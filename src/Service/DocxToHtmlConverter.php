<?php

namespace App\Service;

/**
 * Convierte un .docx en HTML con estilos en línea (pt) apto para el editor de
 * plantillas de contrato y para Dompdf.
 *
 * No depende de librerías externas: lee word/document.xml, styles.xml y las
 * relaciones con ZipArchive + DOM, resuelve la cadena de estilos de Word y
 * emite sólo lo que Dompdf sabe pintar (tamaño, fuente, color, negrita,
 * alineación, sangrías, interlineado, tablas con bordes e imágenes).
 *
 * El .docx lo sube un usuario: se limitan tamaños, no se resuelven entidades
 * externas y todo texto se escapa antes de emitirse.
 */
class DocxToHtmlConverter
{
    private const MAX_ARCHIVO = 8 * 1024 * 1024;
    private const MAX_XML = 20 * 1024 * 1024;
    private const MAX_IMAGEN = 2 * 1024 * 1024;

    private const NS_W = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
    private const NS_R = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    private const NS_WP = 'http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing';
    private const NS_A = 'http://schemas.openxmlformats.org/drawingml/2006/main';

    /** Nombres que usan los Word de los clientes => token del catálogo. */
    public const ALIAS_VARIABLES = [
        'nombre_cliente' => 'cliente_nombre',
        'rut_cliente' => 'cliente_rut',
        'email_cliente' => 'cliente_email',
        'correo_cliente' => 'cliente_email',
        'telefono_cliente' => 'cliente_telefono',
        'direccion_cliente' => 'cliente_direccion',
        'folio_contrato' => 'folio',
        'numero_folio' => 'folio',
        'cuotas_contrato' => 'cuotas',
        'vigencia_contrato' => 'vigencia',
    ];

    /** @var array<string, array{based: ?string, p: array, r: array, default: bool, type: string}> */
    private array $estilos = [];
    private array $rDefecto = [];
    /** @var array<string,string> rId => ruta dentro del zip */
    private array $relaciones = [];
    private \ZipArchive $zip;
    private \DOMXPath $xp;
    private array $avisos = [];
    private float $margenIzqPagina = 0.0;
    private bool $hayImagenAnclada = false;
    /** @var array<string,int> tamaño/fuente => caracteres, para dar a las tablas del sistema el estilo del cuerpo */
    private array $histTam = [];
    private array $histFuente = [];
    /** @var list<string> */
    private array $tokensValidos = [];

    /**
     * @return array{html: string, avisos: list<string>}
     */
    public function convertir(string $ruta): array
    {
        $this->estilos = $this->rDefecto = $this->relaciones = $this->avisos = [];
        $this->hayImagenAnclada = false;
        $this->histTam = $this->histFuente = [];
        $this->tokensValidos = array_map(
            static fn (string $t): string => trim($t, '{}'),
            array_keys(ContratoTemplateRenderer::catalogo())
        );

        if (!is_file($ruta) || filesize($ruta) > self::MAX_ARCHIVO) {
            throw new \InvalidArgumentException('El archivo no existe o supera los 8 MB.');
        }
        $zip = new \ZipArchive();
        if ($zip->open($ruta) !== true) {
            throw new \InvalidArgumentException('El archivo no es un .docx válido.');
        }
        $this->zip = $zip;

        try {
            $docXml = $this->leerXml('word/document.xml');
            if ($docXml === null) {
                throw new \InvalidArgumentException('El archivo no es un .docx válido (falta word/document.xml).');
            }
            $this->cargarEstilos($this->leerXml('word/styles.xml'));
            $this->cargarRelaciones($this->leerXml('word/_rels/document.xml.rels'));

            $this->xp = new \DOMXPath($docXml);
            $this->xp->registerNamespace('w', self::NS_W);
            $this->xp->registerNamespace('r', self::NS_R);
            $this->xp->registerNamespace('wp', self::NS_WP);
            $this->xp->registerNamespace('a', self::NS_A);

            $sect = $this->xp->query('//w:body/w:sectPr/w:pgMar | //w:sectPr/w:pgMar')->item(0);
            if ($sect instanceof \DOMElement) {
                $this->margenIzqPagina = (int) $sect->getAttributeNS(self::NS_W, 'left') / 20;
            }

            $body = $this->xp->query('//w:body')->item(0);
            $html = $body ? $this->bloques($body) : '';
        } finally {
            $zip->close();
        }

        if ($this->hayImagenAnclada) {
            $this->avisos[] = 'Las imágenes flotantes de Word se colocaron en línea con el texto; revise su posición.';
        }
        $this->avisos = array_values(array_unique($this->avisos));

        arsort($this->histTam);
        arsort($this->histFuente);
        $tam = (float) (array_key_first($this->histTam) ?? 11);
        $fuente = (string) (array_key_first($this->histFuente) ?? 'Arial');
        $html = str_replace('%%ESTILO_CUERPO%%', 'font-family:' . $this->familia($fuente) . ';font-size:' . $this->pt($tam) . ';', $html);

        return ['html' => trim($html), 'avisos' => $this->avisos];
    }

    // ------------------------------------------------------------------ zip/xml

    private function leerXml(string $nombre): ?\DOMDocument
    {
        $stat = $this->zip->statName($nombre);
        if ($stat === false || $stat['size'] > self::MAX_XML) {
            return null;
        }
        $contenido = $this->zip->getFromName($nombre);
        if ($contenido === false || $contenido === '') {
            return null;
        }
        $dom = new \DOMDocument();
        $previo = libxml_use_internal_errors(true);
        $ok = $dom->loadXML($contenido, LIBXML_NONET | LIBXML_COMPACT);
        libxml_clear_errors();
        libxml_use_internal_errors($previo);

        return $ok ? $dom : null;
    }

    private function cargarRelaciones(?\DOMDocument $dom): void
    {
        if (!$dom) {
            return;
        }
        foreach ($dom->getElementsByTagName('Relationship') as $rel) {
            $destino = $rel->getAttribute('Target');
            if ($rel->getAttribute('TargetMode') === 'External') {
                continue;
            }
            $this->relaciones[$rel->getAttribute('Id')] = ltrim(str_starts_with($destino, '/') ? $destino : 'word/' . $destino, '/');
        }
    }

    private function cargarEstilos(?\DOMDocument $dom): void
    {
        if (!$dom) {
            return;
        }
        $xp = new \DOMXPath($dom);
        $xp->registerNamespace('w', self::NS_W);

        $rpr = $xp->query('//w:docDefaults/w:rPrDefault/w:rPr')->item(0);
        if ($rpr instanceof \DOMElement) {
            $this->rDefecto = $this->leerRpr($rpr);
        }

        foreach ($xp->query('//w:style') as $st) {
            /** @var \DOMElement $st */
            $id = $st->getAttributeNS(self::NS_W, 'styleId');
            $based = $xp->query('w:basedOn', $st)->item(0);
            $ppr = $xp->query('w:pPr', $st)->item(0);
            $rpr = $xp->query('w:rPr', $st)->item(0);
            $this->estilos[$id] = [
                'based' => $based instanceof \DOMElement ? $based->getAttributeNS(self::NS_W, 'val') : null,
                'p' => $ppr instanceof \DOMElement ? $this->leerPpr($ppr) : [],
                'r' => $rpr instanceof \DOMElement ? $this->leerRpr($rpr) : [],
                'default' => $st->getAttributeNS(self::NS_W, 'default') === '1',
                'type' => $st->getAttributeNS(self::NS_W, 'type'),
            ];
        }
    }

    // --------------------------------------------------------------- propiedades

    private function w(\DOMElement $el, string $atributo): string
    {
        return $el->getAttributeNS(self::NS_W, $atributo);
    }

    private function hijo(\DOMElement $el, string $nombre): ?\DOMElement
    {
        foreach ($el->childNodes as $n) {
            if ($n instanceof \DOMElement && $n->namespaceURI === self::NS_W && $n->localName === $nombre) {
                return $n;
            }
        }

        return null;
    }

    private function activo(?\DOMElement $el): ?bool
    {
        if (!$el) {
            return null;
        }
        $v = strtolower($this->w($el, 'val'));

        return !in_array($v, ['0', 'false', 'off', 'none'], true);
    }

    /** @return array<string,mixed> propiedades de run */
    private function leerRpr(\DOMElement $rpr): array
    {
        $r = [];
        $fuente = $this->hijo($rpr, 'rFonts');
        if ($fuente && ($f = $this->w($fuente, 'ascii') ?: $this->w($fuente, 'hAnsi'))) {
            $r['fuente'] = trim(preg_replace('/\s+MT$/i', '', $f) ?? $f);
        }
        if (($sz = $this->hijo($rpr, 'sz')) && $this->w($sz, 'val') !== '') {
            $r['tam'] = (int) $this->w($sz, 'val') / 2;
        }
        if (($b = $this->activo($this->hijo($rpr, 'b'))) !== null) {
            $r['negrita'] = $b;
        }
        if (($i = $this->activo($this->hijo($rpr, 'i'))) !== null) {
            $r['cursiva'] = $i;
        }
        if ($u = $this->hijo($rpr, 'u')) {
            $r['subrayado'] = $this->w($u, 'val') !== 'none';
        }
        if (($s = $this->activo($this->hijo($rpr, 'strike'))) !== null) {
            $r['tachado'] = $s;
        }
        if ($c = $this->hijo($rpr, 'color')) {
            $v = $this->w($c, 'val');
            if (preg_match('/^[0-9A-Fa-f]{6}$/', $v)) {
                $r['color'] = '#' . strtolower($v);
            } elseif ($v === 'auto') {
                $r['color'] = null;
            }
        }
        if ($h = $this->hijo($rpr, 'shd')) {
            $v = $this->w($h, 'fill');
            if (preg_match('/^[0-9A-Fa-f]{6}$/', $v)) {
                $r['fondo'] = '#' . strtolower($v);
            }
        }
        if ($h = $this->hijo($rpr, 'highlight')) {
            $mapa = ['yellow' => '#ffff00', 'green' => '#00ff00', 'cyan' => '#00ffff', 'lightGray' => '#d3d3d3'];
            if (isset($mapa[$this->w($h, 'val')])) {
                $r['fondo'] = $mapa[$this->w($h, 'val')];
            }
        }
        if (($v = $this->hijo($rpr, 'vertAlign')) && in_array($this->w($v, 'val'), ['superscript', 'subscript'], true)) {
            $r['vert'] = $this->w($v, 'val') === 'superscript' ? 'sup' : 'sub';
        }
        if (($c = $this->activo($this->hijo($rpr, 'caps'))) !== null) {
            $r['mayus'] = $c;
        }

        return $r;
    }

    /** @return array<string,mixed> propiedades de párrafo */
    private function leerPpr(\DOMElement $ppr): array
    {
        $p = [];
        if ($jc = $this->hijo($ppr, 'jc')) {
            $mapa = ['both' => 'justify', 'distribute' => 'justify', 'center' => 'center', 'right' => 'right', 'end' => 'right', 'left' => 'left', 'start' => 'left'];
            $p['alinea'] = $mapa[$this->w($jc, 'val')] ?? null;
        }
        if ($ind = $this->hijo($ppr, 'ind')) {
            foreach (['left' => 'izq', 'start' => 'izq', 'right' => 'der', 'end' => 'der', 'firstLine' => 'primera', 'hanging' => 'colgante'] as $attr => $clave) {
                if ($this->w($ind, $attr) !== '') {
                    $p[$clave] = (int) $this->w($ind, $attr) / 20;
                }
            }
        }
        if ($sp = $this->hijo($ppr, 'spacing')) {
            if ($this->w($sp, 'before') !== '') {
                $p['antes'] = (int) $this->w($sp, 'before') / 20;
            }
            if ($this->w($sp, 'after') !== '') {
                $p['despues'] = (int) $this->w($sp, 'after') / 20;
            }
            if ($this->w($sp, 'line') !== '') {
                $p['linea'] = (int) $this->w($sp, 'line');
                $p['reglaLinea'] = $this->w($sp, 'lineRule') ?: 'auto';
            }
        }
        if ($this->hijo($ppr, 'numPr')) {
            $p['lista'] = true;
        }

        return $p;
    }

    /** Resuelve la cadena basedOn de un estilo y devuelve [p, r] fusionados. */
    private function resolverEstilo(?string $id, string $tipo = 'paragraph'): array
    {
        $cadena = [];
        $visto = [];
        while ($id !== null && isset($this->estilos[$id]) && !isset($visto[$id])) {
            $visto[$id] = true;
            array_unshift($cadena, $this->estilos[$id]);
            $id = $this->estilos[$id]['based'];
        }
        $p = [];
        $r = [];
        foreach ($cadena as $st) {
            $p = array_merge($p, $st['p']);
            $r = array_merge($r, $st['r']);
        }

        return [$p, $r];
    }

    private function estiloPorDefecto(string $tipo): ?string
    {
        foreach ($this->estilos as $id => $st) {
            if ($st['default'] && $st['type'] === $tipo) {
                return (string) $id;
            }
        }

        return null;
    }

    // --------------------------------------------------------------- bloques

    private function bloques(\DOMElement $contenedor): string
    {
        $html = '';
        foreach ($contenedor->childNodes as $n) {
            if (!$n instanceof \DOMElement || $n->namespaceURI !== self::NS_W) {
                continue;
            }
            if ($n->localName === 'p') {
                $html .= $this->parrafo($n);
            } elseif ($n->localName === 'tbl') {
                $html .= $this->tabla($n);
            } elseif ($n->localName === 'sdt') {
                $contenido = $this->hijo($n, 'sdtContent');
                if ($contenido) {
                    $html .= $this->bloques($contenido);
                }
            }
        }

        return $html;
    }

    private function parrafo(\DOMElement $p): string
    {
        $ppr = $this->hijo($p, 'pPr');
        $idEstilo = null;
        if ($ppr && ($ps = $this->hijo($ppr, 'pStyle'))) {
            $idEstilo = $this->w($ps, 'val');
        }
        $idEstilo ??= $this->estiloPorDefecto('paragraph');

        [$pEstilo, $rEstilo] = $this->resolverEstilo($idEstilo);
        $pDirecto = $ppr ? $this->leerPpr($ppr) : [];
        $propP = array_merge($pEstilo, $pDirecto);
        $base = array_merge($this->rDefecto, $rEstilo);

        $marca = $ppr && ($m = $this->hijo($ppr, 'rPr')) ? $this->leerRpr($m) : [];

        // Segmentos [estiloCss, texto|null, htmlCrudo|null]
        $segmentos = [];
        $this->recorrerRuns($p, $base, $segmentos);

        $texto = '';
        foreach ($segmentos as $s) {
            $texto .= $s[1] ?? "\u{FFFC}";
        }
        $vacio = trim(str_replace("\u{FFFC}", '', $texto)) === '' && !array_filter($segmentos, static fn ($s) => $s[2] !== null);

        $baseParrafo = $vacio ? array_merge($base, $marca) : $base;
        if (!$vacio) {
            $largo = max(1, mb_strlen(trim($texto)));
            foreach ($segmentos as $s) {
                if ($s[1] === null) {
                    continue;
                }
                $props = $baseParrafo;
                $tamSeg = preg_match('/font-size:([\d.]+)pt/', $s[0], $m) ? $m[1] : (string) ($props['tam'] ?? 11);
                $this->histTam[$tamSeg] = ($this->histTam[$tamSeg] ?? 0) + mb_strlen($s[1]);
            }
            $fu = (string) ($baseParrafo['fuente'] ?? 'Arial');
            $this->histFuente[$fu] = ($this->histFuente[$fu] ?? 0) + $largo;
        }
        $segmentos = $this->variablesEnSegmentos($segmentos);

        $cuerpo = '';
        $css = $this->cssRun($baseParrafo, []);
        foreach ($this->agrupar($segmentos) as $s) {
            if ($s[2] !== null) {
                $cuerpo .= $s[2];
                continue;
            }
            $texto = htmlspecialchars($s[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $texto = str_replace("\u{2028}", '<br>', $texto);
            $estiloSpan = $s[0];
            $cuerpo .= $estiloSpan !== '' ? '<span style="' . $estiloSpan . '">' . $texto . '</span>' : $texto;
        }
        if ($vacio) {
            $cuerpo = '&nbsp;';
        }

        $estiloP = $this->cssParrafo($propP) . $css;
        if (!empty($propP['lista'])) {
            $this->avisos[] = 'Hay listas con numeración automática de Word; se importaron como párrafos sin su número, revise el texto.';
        }

        return '<p' . ($estiloP !== '' ? ' style="' . $estiloP . '"' : '') . '>' . $cuerpo . '</p>';
    }

    /**
     * @param array<string,mixed> $base
     * @param list<array{0:string,1:?string,2:?string}> $segmentos
     */
    private function recorrerRuns(\DOMElement $nodo, array $base, array &$segmentos): void
    {
        foreach ($nodo->childNodes as $n) {
            if (!$n instanceof \DOMElement) {
                continue;
            }
            if ($n->namespaceURI === self::NS_W && in_array($n->localName, ['hyperlink', 'smartTag', 'ins', 'fldSimple'], true)) {
                $this->recorrerRuns($n, $base, $segmentos);
                continue;
            }
            if ($n->namespaceURI !== self::NS_W || $n->localName !== 'r') {
                continue;
            }

            $rpr = $this->hijo($n, 'rPr');
            $estiloRun = [];
            if ($rpr && ($rs = $this->hijo($rpr, 'rStyle'))) {
                [, $estiloRun] = $this->resolverEstilo($this->w($rs, 'val'));
            }
            $props = array_merge($estiloRun, $rpr ? $this->leerRpr($rpr) : []);
            $css = $this->cssRun($props + $base, $base);

            foreach ($n->childNodes as $c) {
                if (!$c instanceof \DOMElement) {
                    continue;
                }
                if ($c->namespaceURI === self::NS_W) {
                    switch ($c->localName) {
                        case 't':
                            $segmentos[] = [$css, $c->textContent, null];
                            break;
                        case 'tab':
                            $segmentos[] = [$css, "\u{00A0}\u{00A0}\u{00A0}\u{00A0}", null];
                            break;
                        case 'br':
                            if ($this->w($c, 'type') !== 'page') {
                                $segmentos[] = [$css, "\u{2028}", null];
                            } else {
                                $segmentos[] = ['', null, '<div style="page-break-after:always"></div>'];
                            }
                            break;
                        case 'drawing':
                            $img = $this->imagen($c);
                            if ($img !== null) {
                                $segmentos[] = ['', null, $img];
                            }
                            break;
                    }
                }
            }
        }
    }

    // ------------------------------------------------------------------- css

    private function pt(float $v): string
    {
        return rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.') . 'pt';
    }

    /**
     * @param array<string,mixed> $props
     * @param array<string,mixed> $base si se indica, sólo se emite lo que difiere
     */
    private function cssRun(array $props, array $base): string
    {
        $css = '';
        $dif = static fn (string $k): bool => !array_key_exists($k, $base) || ($base[$k] ?? null) !== ($props[$k] ?? null);

        if (isset($props['fuente']) && $dif('fuente')) {
            $css .= 'font-family:' . $this->familia($props['fuente']) . ';';
        }
        if (isset($props['tam']) && $dif('tam')) {
            $css .= 'font-size:' . $this->pt($props['tam']) . ';';
        }
        if (array_key_exists('negrita', $props) && $dif('negrita') && ($props['negrita'] || isset($base['negrita']))) {
            $css .= 'font-weight:' . ($props['negrita'] ? 'bold' : 'normal') . ';';
        }
        if (array_key_exists('cursiva', $props) && $dif('cursiva') && ($props['cursiva'] || isset($base['cursiva']))) {
            $css .= 'font-style:' . ($props['cursiva'] ? 'italic' : 'normal') . ';';
        }
        $deco = [];
        if (!empty($props['subrayado'])) {
            $deco[] = 'underline';
        }
        if (!empty($props['tachado'])) {
            $deco[] = 'line-through';
        }
        if ($deco && ($dif('subrayado') || $dif('tachado'))) {
            $css .= 'text-decoration:' . implode(' ', $deco) . ';';
        }
        if (!empty($props['color']) && $dif('color')) {
            $css .= 'color:' . $props['color'] . ';';
        }
        if (!empty($props['fondo']) && $dif('fondo')) {
            $css .= 'background-color:' . $props['fondo'] . ';';
        }
        if (!empty($props['vert']) && $dif('vert')) {
            $css .= 'vertical-align:' . $props['vert'] . ';font-size:' . $this->pt(($props['tam'] ?? 11) * 0.65) . ';';
        }
        if (!empty($props['mayus']) && $dif('mayus')) {
            $css .= 'text-transform:uppercase;';
        }

        return $css;
    }

    private function familia(string $fuente): string
    {
        $fuente = trim(preg_replace('/\s+MT$/i', '', $fuente) ?? $fuente);
        $limpia = preg_replace('/[^A-Za-z0-9 \-]/', '', $fuente) ?? '';
        $serif = (bool) preg_match('/times|cambria|georgia|garamond|book antiqua|palatino|serif/i', $limpia);
        $mono = (bool) preg_match('/courier|consolas|mono/i', $limpia);

        return "'" . $limpia . "', " . ($mono ? 'monospace' : ($serif ? 'serif' : 'sans-serif'));
    }

    /** @param array<string,mixed> $p */
    private function cssParrafo(array $p): string
    {
        $css = '';
        $antes = $p['antes'] ?? 0;
        $despues = $p['despues'] ?? 0;
        $izq = $p['izq'] ?? 0;
        $der = $p['der'] ?? 0;
        $css .= 'margin:' . $this->pt($antes) . ' ' . $this->pt($der) . ' ' . $this->pt($despues) . ' ' . $this->pt($izq) . ';';
        if (!empty($p['alinea'])) {
            $css .= 'text-align:' . $p['alinea'] . ';';
        }
        if (isset($p['primera']) && $p['primera'] > 0) {
            $css .= 'text-indent:' . $this->pt($p['primera']) . ';';
        } elseif (isset($p['colgante']) && $p['colgante'] > 0) {
            $css .= 'text-indent:-' . $this->pt($p['colgante']) . ';';
        }
        if (isset($p['linea'])) {
            if (($p['reglaLinea'] ?? 'auto') === 'auto') {
                $css .= 'line-height:' . rtrim(rtrim(number_format($p['linea'] / 240, 2, '.', ''), '0'), '.') . ';';
            } else {
                $css .= 'line-height:' . $this->pt($p['linea'] / 20) . ';';
            }
        }

        return $css;
    }

    // ------------------------------------------------------------- variables

    /**
     * Convierte [variable] (incluso partida en varios runs) en {{token}} y
     * deja constancia de lo que no existe en el catálogo.
     *
     * @param list<array{0:string,1:?string,2:?string}> $segmentos
     * @return list<array{0:string,1:?string,2:?string}>
     */
    private function variablesEnSegmentos(array $segmentos): array
    {
        $chars = [];
        foreach ($segmentos as $s) {
            if ($s[1] === null) {
                $chars[] = [$s[0], null, $s[2]];
                continue;
            }
            foreach (mb_str_split($s[1]) as $c) {
                $chars[] = [$s[0], $c, null];
            }
        }
        $plano = '';
        foreach ($chars as $c) {
            $plano .= $c[1] ?? "\u{FFFC}";
        }
        if (!preg_match('/\[[\p{L}_][\p{L}0-9_]*\]/u', $plano)) {
            return $segmentos;
        }

        $salida = [];
        $i = 0;
        $total = count($chars);
        while ($i < $total) {
            $resto = '';
            $encontrado = null;
            if ($chars[$i][1] === '[') {
                for ($j = $i; $j < $total && $j - $i < 60; $j++) {
                    $resto .= $chars[$j][1] ?? "\u{FFFC}";
                    if ($chars[$j][1] === ']') {
                        break;
                    }
                }
                if (preg_match('/^\[([\p{L}_][\p{L}0-9_]*)\]$/u', $resto, $mm)) {
                    $encontrado = [$mm[1], mb_strlen($resto)];
                }
            }
            if ($encontrado) {
                $nombre = $encontrado[0];
                $nombre = strtr($nombre, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ñ' => 'n']);
                $token = self::ALIAS_VARIABLES[$nombre] ?? $nombre;
                if (in_array($token, $this->tokensValidos, true)) {
                    foreach (mb_str_split('{{' . $token . '}}') as $c) {
                        $salida[] = [$chars[$i][0], $c, null];
                    }
                } else {
                    $this->avisos[] = 'La variable [' . $nombre . '] no existe en el catálogo y quedó como texto.';
                    for ($k = 0; $k < $encontrado[1]; $k++) {
                        $salida[] = $chars[$i + $k];
                    }
                }
                $i += $encontrado[1];
                continue;
            }
            $salida[] = $chars[$i];
            $i++;
        }

        $resultado = [];
        foreach ($salida as $c) {
            $resultado[] = [$c[0], $c[1], $c[2]];
        }

        return $resultado;
    }

    /**
     * Junta caracteres/segmentos consecutivos con el mismo estilo.
     *
     * @param list<array{0:string,1:?string,2:?string}> $segmentos
     * @return list<array{0:string,1:?string,2:?string}>
     */
    private function agrupar(array $segmentos): array
    {
        $res = [];
        foreach ($segmentos as $s) {
            $ult = count($res) - 1;
            if ($s[2] === null && $ult >= 0 && $res[$ult][2] === null && $res[$ult][0] === $s[0]) {
                $res[$ult][1] .= $s[1];
                continue;
            }
            $res[] = $s;
        }

        return $res;
    }

    // ---------------------------------------------------------------- tablas

    /**
     * Arma "|col=Cabecera;col=Cabecera" para {{causas}}/{{detalle_cuotas}} a partir de una tabla de Word:
     * la columna sale del marcador [variable] de la fila de datos (o del texto de la cabecera) y la
     * cabecera, del texto de la primera fila.
     *
     * @param array<string,string> $mapa marcador => columna del sistema
     */
    private function opcionesDeTabla(\DOMElement $tbl, array $mapa, bool $conCabeceras = true): string
    {
        $filas = [];
        foreach ($tbl->childNodes as $tr) {
            if ($tr instanceof \DOMElement && $tr->localName === 'tr') {
                $celdas = [];
                foreach ($tr->childNodes as $tc) {
                    if ($tc instanceof \DOMElement && $tc->localName === 'tc') {
                        $celdas[] = trim((string) preg_replace('/\s+/u', ' ', $tc->textContent));
                    }
                }
                $filas[] = $celdas;
            }
        }
        if (!$filas) {
            return '';
        }

        $partes = [];
        foreach ($filas[0] as $i => $cabecera) {
            $marcador = trim($filas[1][$i] ?? $cabecera, '[] ');
            $columna = $mapa[$marcador] ?? $mapa[strtolower(trim($cabecera, '[] '))] ?? null;
            if ($columna === null || isset($partes[$columna])) {
                continue;
            }
            $etiqueta = $conCabeceras ? trim(str_replace([';', '=', '|', '{', '}', '[', ']'], '', $cabecera)) : '';
            $partes[$columna] = $columna . ($etiqueta !== '' ? '=' . $etiqueta : '');
        }

        return $partes ? '|' . implode(';', $partes) : '';
    }

    private function tabla(\DOMElement $tbl): string
    {
        $texto = $tbl->textContent;
        if (preg_match('/rol_rut|caratulado_servicio|servicio_contratado/', $texto) && !$this->xp->query('.//w:tbl', $tbl)->length) {
            $opciones = $this->opcionesDeTabla($tbl, [
                'servicio_contratado' => 'materia', 'rol_rut' => 'rol', 'caratulado_servicio' => 'caratulado',
                'juzgado' => 'juzgado', 'nombre_cliente' => 'cliente',
            ]);
            $this->avisos[] = 'La tabla de causas se reemplazó por {{causas}} con las mismas columnas y cabeceras (el sistema agrega una fila por causa); ajuste su estilo si lo necesita.';

            return '<div style="%%ESTILO_CUERPO%%margin:0 0 0 16pt">{{causas' . $opciones . '}}</div>';
        }
        if (preg_match('/fecha_vencimiento/', $texto) && preg_match('/valor_cuota/', $texto)) {
            $opciones = $this->opcionesDeTabla($tbl, ['cuota' => 'numero', 'fecha_vencimiento' => 'vencimiento', 'valor_cuota' => 'monto'], false);
            $this->avisos[] = 'La tabla de cuotas se reemplazó por {{detalle_cuotas}} (el sistema genera una fila por cuota).';

            return '<div style="%%ESTILO_CUERPO%%margin:0 0 0 16pt">{{detalle_cuotas' . $opciones . '}}</div>';
        }

        $tblPr = $this->hijo($tbl, 'tblPr');
        $bordes = $tblPr && ($b = $this->hijo($tblPr, 'tblBorders')) ? $this->leerBordes($b) : [];
        $sangria = 0.0;
        if ($tblPr && ($ti = $this->hijo($tblPr, 'tblInd'))) {
            $sangria = (int) $this->w($ti, 'w') / 20;
        }
        $mar = ['t' => 0.0, 'r' => 0.0, 'b' => 0.0, 'l' => 0.0];
        if ($tblPr && ($cm = $this->hijo($tblPr, 'tblCellMar'))) {
            foreach (['top' => 't', 'right' => 'r', 'bottom' => 'b', 'left' => 'l', 'start' => 'l', 'end' => 'r'] as $lado => $k) {
                if ($e = $this->hijo($cm, $lado)) {
                    $mar[$k] = (int) $this->w($e, 'w') / 20;
                }
            }
        }

        $cols = [];
        if ($grid = $this->hijo($tbl, 'tblGrid')) {
            foreach ($grid->childNodes as $g) {
                if ($g instanceof \DOMElement && $g->localName === 'gridCol') {
                    $cols[] = (int) $this->w($g, 'w') / 20;
                }
            }
        }
        $anchoTotal = array_sum($cols);

        // rowspan por vMerge
        $filas = [];
        foreach ($tbl->childNodes as $tr) {
            if ($tr instanceof \DOMElement && $tr->namespaceURI === self::NS_W && $tr->localName === 'tr') {
                $filas[] = $tr;
            }
        }
        $span = [];
        $abierto = [];
        foreach ($filas as $ri => $tr) {
            $ci = 0;
            foreach ($tr->childNodes as $tc) {
                if (!$tc instanceof \DOMElement || $tc->localName !== 'tc') {
                    continue;
                }
                $pr = $this->hijo($tc, 'tcPr');
                $gs = $pr && ($g = $this->hijo($pr, 'gridSpan')) ? max(1, (int) $this->w($g, 'val')) : 1;
                $vm = $pr ? $this->hijo($pr, 'vMerge') : null;
                if ($vm) {
                    if ($this->w($vm, 'val') === 'restart') {
                        $span[$ri . ':' . $ci] = 1;
                        $origen = $ri . ':' . $ci;
                        $abierto[$ci] = $origen;
                    } elseif (isset($abierto[$ci])) {
                        $span[$abierto[$ci]]++;
                    }
                } else {
                    unset($abierto[$ci]);
                }
                $ci += $gs;
            }
        }

        $estilo = 'border-collapse:collapse;table-layout:fixed;';
        if ($anchoTotal > 0) {
            $estilo .= 'width:' . $this->pt($anchoTotal) . ';';
        }
        if ($sangria > 0) {
            $estilo .= 'margin-left:' . $this->pt($sangria) . ';';
        }
        $html = '<table cellspacing="0" cellpadding="0" style="' . $estilo . '">';
        if ($cols) {
            $html .= '<colgroup>';
            foreach ($cols as $c) {
                $html .= '<col style="width:' . $this->pt($c) . '">';
            }
            $html .= '</colgroup>';
        }

        foreach ($filas as $ri => $tr) {
            $html .= '<tr>';
            $ci = 0;
            foreach ($tr->childNodes as $tc) {
                if (!$tc instanceof \DOMElement || $tc->localName !== 'tc') {
                    continue;
                }
                $pr = $this->hijo($tc, 'tcPr');
                $gs = $pr && ($g = $this->hijo($pr, 'gridSpan')) ? max(1, (int) $this->w($g, 'val')) : 1;
                $vm = $pr ? $this->hijo($pr, 'vMerge') : null;
                $clave = $ri . ':' . $ci;
                $ci += $gs;
                if ($vm && $this->w($vm, 'val') !== 'restart') {
                    continue;
                }

                $e = '';
                $bc = $bordes;
                if ($pr && ($b = $this->hijo($pr, 'tcBorders'))) {
                    $bc = array_merge($bc, $this->leerBordes($b));
                }
                foreach (['top' => 't', 'right' => 'r', 'bottom' => 'b', 'left' => 'l'] as $lado => $k) {
                    $def = $bc[$lado] ?? ($bc[$lado === 'top' || $lado === 'bottom' ? 'insideH' : 'insideV'] ?? null);
                    if ($def !== null) {
                        $e .= 'border-' . $lado . ':' . $def . ';';
                    }
                }
                $e .= 'padding:' . $this->pt($mar['t']) . ' ' . $this->pt($mar['r']) . ' ' . $this->pt($mar['b']) . ' ' . $this->pt($mar['l']) . ';';
                if ($pr && ($w = $this->hijo($pr, 'tcW')) && $this->w($w, 'type') === 'dxa') {
                    $e .= 'width:' . $this->pt((int) $this->w($w, 'w') / 20) . ';';
                }
                if ($pr && ($sh = $this->hijo($pr, 'shd')) && preg_match('/^[0-9A-Fa-f]{6}$/', $this->w($sh, 'fill'))) {
                    $e .= 'background-color:#' . strtolower($this->w($sh, 'fill')) . ';';
                }
                if ($pr && ($va = $this->hijo($pr, 'vAlign'))) {
                    $e .= 'vertical-align:' . (['center' => 'middle', 'bottom' => 'bottom'][$this->w($va, 'val')] ?? 'top') . ';';
                } else {
                    $e .= 'vertical-align:top;';
                }

                $atr = $gs > 1 ? ' colspan="' . $gs . '"' : '';
                if (isset($span[$clave]) && $span[$clave] > 1) {
                    $atr .= ' rowspan="' . $span[$clave] . '"';
                }
                $interior = $this->bloques($tc);
                $html .= '<td' . $atr . ' style="' . $e . '">' . ($interior !== '' ? $interior : '&nbsp;') . '</td>';
            }
            $html .= '</tr>';
        }

        return $html . '</table>';
    }

    /** @return array<string,string> lado => "1pt solid #xxxxxx" */
    private function leerBordes(\DOMElement $b): array
    {
        $res = [];
        $lados = ['top' => 'top', 'left' => 'left', 'bottom' => 'bottom', 'right' => 'right', 'insideH' => 'insideH', 'insideV' => 'insideV', 'start' => 'left', 'end' => 'right'];
        foreach ($lados as $nombre => $destino) {
            if (!($e = $this->hijo($b, $nombre))) {
                continue;
            }
            $tipo = $this->w($e, 'val');
            if (in_array($tipo, ['nil', 'none', ''], true)) {
                $res[$destino] = 'none';
                continue;
            }
            $grosor = max(0.5, (int) $this->w($e, 'sz') / 8);
            $color = $this->w($e, 'color');
            $color = preg_match('/^[0-9A-Fa-f]{6}$/', $color) ? '#' . strtolower($color) : '#000000';
            $estilo = in_array($tipo, ['dashed', 'dotted', 'double'], true) ? $tipo : 'solid';
            $res[$destino] = $this->pt($grosor) . ' ' . $estilo . ' ' . $color;
        }

        return $res;
    }

    // -------------------------------------------------------------- imágenes

    private function imagen(\DOMElement $drawing): ?string
    {
        $blip = $this->xp->query('.//a:blip', $drawing)->item(0);
        if (!$blip instanceof \DOMElement) {
            return null;
        }
        $rid = $blip->getAttributeNS(self::NS_R, 'embed');
        $ruta = $this->relaciones[$rid] ?? null;
        if ($ruta === null || str_contains($ruta, '..')) {
            return null;
        }
        $stat = $this->zip->statName($ruta);
        if ($stat === false || $stat['size'] > self::MAX_IMAGEN) {
            $this->avisos[] = 'Una imagen supera 2 MB o no se pudo leer y se omitió.';

            return null;
        }
        $mime = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif'][strtolower(pathinfo($ruta, PATHINFO_EXTENSION))] ?? null;
        $bin = $this->zip->getFromName($ruta);
        if ($mime === null || $bin === false) {
            $this->avisos[] = 'Una imagen con formato no soportado (sólo PNG/JPG/GIF) se omitió.';

            return null;
        }

        $ext = $this->xp->query('.//wp:extent', $drawing)->item(0);
        $estilo = '';
        if ($ext instanceof \DOMElement) {
            $estilo = 'width:' . $this->pt((int) $ext->getAttribute('cx') / 12700) . ';height:' . $this->pt((int) $ext->getAttribute('cy') / 12700) . ';';
        }
        $img = '<img src="data:' . $mime . ';base64,' . base64_encode($bin) . '" style="' . $estilo . '">';

        $ancla = $this->xp->query('.//wp:anchor', $drawing)->item(0);
        if ($ancla instanceof \DOMElement) {
            $this->hayImagenAnclada = true;
            $izq = 0.0;
            $off = $this->xp->query('wp:positionH/wp:posOffset', $ancla)->item(0);
            $desde = $this->xp->query('wp:positionH', $ancla)->item(0);
            if ($off && $desde instanceof \DOMElement && $desde->getAttribute('relativeFrom') === 'page') {
                $izq = max(0.0, (int) $off->textContent / 12700 - $this->margenIzqPagina);
            } elseif ($off) {
                $izq = max(0.0, (int) $off->textContent / 12700);
            }

            return '<span style="display:block;margin-left:' . $this->pt($izq) . '">' . $img . '</span>';
        }

        return $img;
    }
}
