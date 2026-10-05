<?php

namespace App\Service;

use App\Entity\Empresa;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Cliente de la API api-pjud.codifica.cl (detalle procesal de causas en vivo desde el PJUD).
 *
 * Autentica con x-client-key + login (email/password de la Empresa) -> token Bearer, que se cachea.
 * El scrape del proveedor es asíncrono: consultar_<materia> devuelve la causa a medias o 404 mientras
 * sincroniza, y sincronizar_<materia> (que usa el RUT/clave del OJV de la persona) encola el trabajo.
 *
 * detalle() devuelve siempre un arreglo con `estado` = listo | sincronizando | error | sin_credenciales.
 */
class PjudApiClient
{
    public const MATERIAS = ['civil', 'familia', 'laboral', 'cobranza', 'penal'];

    private const CATALOGO_TTL = 6 * 3600;
    private const ESTADOS_SINCRONIZANDO = ['sincronizando', 'pendiente', 'en proceso', 'encolada'];
    private const ESTADOS_ERROR = ['error', 'fallido', 'fallida', 'fallo', 'rechazada'];
    private const TIPOS_PENAL = ['Ordinaria', 'Exhorto', 'Administrativa', 'Extradición', 'Militar'];

    private const MSG_SINCRONIZANDO = 'La primera consulta puede tardar varios minutos.';
    private const MSG_ERROR_SYNC = 'La sincronización de esta causa con el Poder Judicial falló. Puedes reintentarla.';
    private const MSG_SIN_CREDENCIALES = 'Para consultar esta causa por primera vez hay que iniciar sesión en el Poder Judicial con tu clave (Mis Datos → Clave del Poder Judicial) o con la ClaveÚnica del cliente de la causa.';

    /**
     * Por materia: si usa catálogo de tribunales, si expone cuadernos, clave de cuaderno en el request
     * de movimientos y las secciones que devuelve consultar_movimientos_<materia>.
     */
    private const CONFIG = [
        'civil' => ['catalogo' => true, 'cuadernos' => true, 'clave_cuaderno' => 'cuadeno',
            'secciones' => ['historia', 'litigantes', 'notificaciones', 'escritos_resolver', 'exhortos', 'piezas_exhorto']],
        'familia' => ['catalogo' => false, 'cuadernos' => false, 'clave_cuaderno' => 'cuadeno',
            'secciones' => ['movimientos', 'litigantes', 'notificaciones', 'materias', 'plazos', 'diligencias']],
        'laboral' => ['catalogo' => true, 'cuadernos' => false, 'clave_cuaderno' => 'cuadeno',
            'secciones' => ['movimiento', 'litigantes', 'notificaciones', 'diligencias', 'liquidacion', 'materias', 'escritos_pendientes']],
        'cobranza' => ['catalogo' => true, 'cuadernos' => true, 'clave_cuaderno' => 'cuadeno',
            'secciones' => ['historia', 'litigantes', 'notificaciones', 'diligencias', 'liquidacion']],
        'penal' => ['catalogo' => true, 'cuadernos' => true, 'clave_cuaderno' => 'cuaderno',
            'secciones' => ['historia', 'litigantes', 'notificaciones', 'relaciones']],
    ];

    private Client $http;
    private string $base;
    private string $clientKey = '';
    private string $email = '';
    private string $password = '';
    private int $empresaId = 0;

    public function __construct(string $baseUrl, private readonly CacheInterface $cache, bool $verifySsl = true)
    {
        $this->base = rtrim($baseUrl, '/');
        $this->http = new Client(['timeout' => 25, 'http_errors' => false, 'verify' => $verifySsl]);
    }

    public function paraEmpresa(?Empresa $empresa): self
    {
        $this->empresaId = (int) $empresa?->getId();
        $this->clientKey = trim((string) $empresa?->getPjudClientKey());
        $this->email = trim((string) $empresa?->getPjudEmail());
        $this->password = (string) $empresa?->getPjudPassword();
        return $this;
    }

    public function configurado(): bool
    {
        return $this->email !== '' && $this->password !== '';
    }

    // ───────────────────────── HTTP ─────────────────────────

    private function token(bool $renovar = false): string
    {
        $clave = 'pjud_token_' . $this->empresaId . '_' . md5($this->email);
        if ($renovar) {
            $this->cache->delete($clave);
        }
        return $this->cache->get($clave, function (ItemInterface $item): string {
            $data = $this->peticion('POST', '/auth/login', ['json' => ['email' => $this->email, 'password' => $this->password]], false);
            $token = $data['token'] ?? null;
            if (!$token) {
                throw new PjudApiException('El login contra el PJUD no devolvió un token.');
            }
            $item->expiresAfter($this->segundosHastaVencer($data['expira_en'] ?? null));
            return $token;
        });
    }

    private function segundosHastaVencer(?string $expiraEn): int
    {
        if ($expiraEn) {
            try {
                $restante = (new \DateTimeImmutable($expiraEn))->getTimestamp() - time() - 30;
                if ($restante > 0) {
                    return $restante;
                }
            } catch (\Exception) {
            }
        }
        return 300;
    }

    private function cabeceras(bool $conToken): array
    {
        $h = ['Accept' => 'application/json'];
        if ($this->clientKey !== '') {
            $h['x-client-key'] = $this->clientKey;
        }
        if ($conToken) {
            $h['Authorization'] = 'Bearer ' . $this->token();
        }
        return $h;
    }

    /** @throws PjudApiException|PjudNoEncontrado|PjudConflicto */
    private function peticion(string $metodo, string $ruta, array $opciones = [], bool $conToken = true, bool $reintento = true): array
    {
        if (!$this->configurado()) {
            throw new PjudApiException('La empresa no tiene configurada la API PJUD (configúrela en Empresa).');
        }
        $opciones['headers'] = $this->cabeceras($conToken);
        try {
            $resp = $this->http->request($metodo, $this->base . $ruta, $opciones);
        } catch (GuzzleException $e) {
            throw new PjudApiException('No se pudo conectar con el servicio de detalle PJUD: ' . mb_substr($e->getMessage(), 0, 220), 0, $e);
        }
        $codigo = $resp->getStatusCode();
        if ($codigo === 401 && $conToken && $reintento) {
            $this->token(true);
            return $this->peticion($metodo, $ruta, $opciones, true, false);
        }
        if ($codigo === 401) {
            throw new PjudApiException('El servicio de detalle PJUD rechazó las credenciales configuradas.');
        }
        if ($codigo === 404) {
            throw new PjudNoEncontrado('El PJUD todavía no tiene registrada esta causa.');
        }
        if ($codigo === 409) {
            throw new PjudConflicto('El PJUD ya está sincronizando esta causa.');
        }
        $cuerpo = json_decode((string) $resp->getBody(), true);
        if ($codigo >= 400) {
            $detalle = is_array($cuerpo) ? ($cuerpo['mensaje'] ?? $cuerpo['detail'] ?? null) : null;
            throw new PjudApiException('api-pjud respondió HTTP ' . $codigo . (is_string($detalle) && $detalle !== '' ? ': ' . $detalle : ''));
        }
        if (!is_array($cuerpo)) {
            throw new PjudApiException('El servicio de detalle PJUD devolvió una respuesta ilegible.');
        }
        return $cuerpo;
    }

    // ───────────────────────── Catálogo ─────────────────────────

    private static function normalizar(string $texto): string
    {
        $texto = mb_strtolower(trim($texto));
        $texto = strtr($texto, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', '°' => '', 'º' => '', '.' => '']);
        return implode(' ', preg_split('/\s+/', $texto) ?: []);
    }

    /** @return array{0:int,1:int} [corte_id, tribunal_id] */
    private function resolverTribunal(string $nombre, string $competencia): array
    {
        $cortes = $this->cache->get('pjud_catalogo_' . $competencia . '_' . $this->empresaId, function (ItemInterface $item) use ($competencia) {
            $item->expiresAfter(self::CATALOGO_TTL);
            return $this->peticion('GET', '/catalogo/tribunales', ['query' => ['competencia' => $competencia]])['cortes'] ?? [];
        });
        $objetivo = self::normalizar($nombre);
        foreach ($cortes as $corte) {
            foreach ($corte['tribunales'] ?? [] as $tribunal) {
                if (self::normalizar($tribunal['nombre']) === $objetivo) {
                    return [(int) $corte['id'], (int) $tribunal['id']];
                }
            }
        }
        throw new PjudApiException(sprintf('El tribunal «%s» no está en el catálogo %s del PJUD.', $nombre, $competencia));
    }

    // ───────────────────────── Flujo principal ─────────────────────────

    /**
     * @param array{tipo:string,rol:int,anio:int,tribunal:string,tipo_causa?:?string} $causa
     * @param array{rut?:?string,clave?:?string,metodo_login?:int}|null $credenciales OJV de la persona
     */
    public function detalle(string $materia, array $causa, bool $forzar = false, ?int $cuaderno = null, ?array $credenciales = null): array
    {
        $cfg = self::CONFIG[$materia] ?? throw new PjudApiException('Materia no soportada por el detalle PJUD.');

        $cuerpo = ['corte' => 0, 'tribunal' => 0, 'tipo' => $causa['tipo'], 'rol' => $causa['rol'], 'anio' => $causa['anio']];
        if ($materia === 'penal') {
            $cuerpo['tipo'] = $this->tipoPenal($causa['tipo_causa'] ?? null);
        }
        if ($cfg['catalogo']) {
            try {
                [$cuerpo['corte'], $cuerpo['tribunal']] = $this->resolverTribunal($causa['tribunal'], $materia);
            } catch (PjudApiException $e) {
                return [
                    'estado' => 'error', 'mensaje' => self::MSG_ERROR_SYNC, 'detalle_estado' => null,
                    'ultimo_error' => sprintf('No se pudo resolver el tribunal «%s» contra el catálogo %s del PJUD: %s', $causa['tribunal'], ucfirst($materia), $e->getMessage()),
                ];
            }
        }

        $puedeSincronizar = !empty($credenciales['rut']) && !empty($credenciales['clave']);
        if ($forzar && $puedeSincronizar) {
            $this->sincronizar($materia, $cuerpo, $credenciales);
        }

        $detalleEstado = $ultimoError = null;
        try {
            $data = $this->peticion('POST', '/consultar_' . $materia, ['json' => $cuerpo]);
            $detalle = $data['causa'] ?? $data;
            $detalleEstado = trim((string) ($detalle['detalle_estado'] ?? '')) ?: null;
            $ultimoError = trim((string) ($detalle['ultimo_error'] ?? '')) ?: null;
        } catch (PjudNoEncontrado) {
            $detalle = null;
        }

        $estadoNorm = $detalle ? mb_strtolower(trim((string) ($detalle['estado'] ?? ''))) : '';
        if (in_array($estadoNorm, self::ESTADOS_ERROR, true)) {
            return ['estado' => 'error', 'mensaje' => self::MSG_ERROR_SYNC, 'detalle_estado' => $detalleEstado, 'ultimo_error' => $ultimoError];
        }

        $cuadernos = $detalle['cuadernos'] ?? [];
        $sincronizando = $detalle === null
            || in_array($estadoNorm, self::ESTADOS_SINCRONIZANDO, true)
            || ($cfg['cuadernos'] && !$cuadernos);

        if ($sincronizando && !$puedeSincronizar) {
            return ['estado' => 'sin_credenciales', 'mensaje' => self::MSG_SIN_CREDENCIALES, 'detalle_estado' => $detalleEstado];
        }
        if ($sincronizando && !$forzar) {
            $this->sincronizar($materia, $cuerpo, $credenciales);
        }

        $identificador = $detalle['identificador'] ?? null;
        $resultado = [
            'estado' => $sincronizando ? 'sincronizando' : 'listo',
            'mensaje' => $sincronizando ? self::MSG_SINCRONIZANDO : null,
            'detalle_estado' => $detalleEstado,
            'ultimo_error' => $ultimoError,
            'causa' => $identificador ? $this->normalizarCabecera($materia, $detalle) : null,
            'cuaderno_consultado_id' => null,
            'secciones' => array_fill_keys($cfg['secciones'], []),
        ];

        if ($identificador && (!$cfg['cuadernos'] || $cuadernos)) {
            $elegido = $cfg['cuadernos'] ? $this->elegirCuaderno($cuadernos, $cuaderno) : ['id' => 1];
            try {
                $mov = $this->peticion('POST', '/consultar_movimientos_' . $materia, [
                    'json' => ['identificador' => $identificador, $cfg['clave_cuaderno'] => $elegido['id']],
                ]);
            } catch (PjudApiException $e) {
                // Mientras sincroniza, los movimientos pueden no estar listos: se devuelve la cabecera sola.
                if (!$sincronizando) {
                    throw $e;
                }
                return $resultado;
            }
            $resultado['cuaderno_consultado_id'] = $cfg['cuadernos'] ? $elegido['id'] : null;
            $resultado['secciones'] = $this->seccionesDe($materia, $mov, $identificador, (int) $elegido['id']);
        }

        return $resultado;
    }

    private function tipoPenal(?string $tipoCausa): string
    {
        foreach (self::TIPOS_PENAL as $t) {
            if (self::normalizar($t) === self::normalizar((string) $tipoCausa)) {
                return $t;
            }
        }
        return 'Ordinaria';
    }

    private function sincronizar(string $materia, array $cuerpo, ?array $cred): void
    {
        if (empty($cred['rut']) || empty($cred['clave'])) {
            return;
        }
        $cuerpo += ['rut' => $cred['rut'], 'clave' => $cred['clave'], 'metodo_login' => $cred['metodo_login'] ?? 1];
        try {
            $this->peticion('POST', '/sincronizar_' . $materia, ['json' => $cuerpo]);
        } catch (PjudConflicto) {
            // Ya en curso, o muy pronto desde el último intento: significa "espera".
        } catch (PjudApiException) {
            // Best-effort: una falla acá no corta la pantalla; el estado se ve en la próxima consulta.
        }
    }

    private function elegirCuaderno(array $cuadernos, ?int $id): array
    {
        foreach ($cuadernos as $c) {
            if ($id !== null && ($c['id'] ?? null) === $id) {
                return $c;
            }
        }
        return $cuadernos[0];
    }

    // ───────────────────────── Normalización ─────────────────────────

    private function normalizarCabecera(string $materia, array $d): array
    {
        if (isset($d['Ruc']) && !isset($d['ruc'])) {
            $d['ruc'] = $d['Ruc'];
        }
        if ($materia === 'cobranza' && isset($d['doc_demanda']) && is_array($d['doc_demanda']) && isset($d['doc_demanda']['ruta'])) {
            $d['doc_demanda']['url'] = $d['doc_demanda']['ruta'];
        }
        if ($materia === 'penal') {
            if (empty($d['rol']) && !empty($d['rit'])) {
                $d['rol'] = $d['rit'];
            }
            if (empty($d['estado_adm']) && !empty($d['est_adm'])) {
                $d['estado_adm'] = $d['est_adm'];
            }
            foreach (['acumulada', 'certificado_envio'] as $k) {
                $v = $d[$k] ?? null;
                $d[$k] = is_array($v) ? ($v['url'] ?? $v['ruta'] ?? null) : ($v ?: null);
            }
        }
        // Anexos de la causa: la URL puede venir relativa al identificador.
        $ident = (string) ($d['identificador'] ?? '');
        foreach (['anexos_causa', 'documentos_laboral'] as $k) {
            foreach ($d[$k] ?? [] as $i => $a) {
                $d[$k][$i]['doc'] = $this->urlDocumento($a['doc'] ?? null, $ident, 1);
            }
        }
        if (isset($d['texto_demanda']) && is_array($d['texto_demanda']) && array_is_list($d['texto_demanda'])) {
            foreach ($d['texto_demanda'] as $i => $t) {
                $d['texto_demanda'][$i]['doc'] = $this->urlDocumento($t['doc'] ?? null, $ident, 1);
            }
        }
        foreach ($d['audio_laboral'] ?? [] as $i => $a) {
            $d['audio_laboral'][$i]['audio'] = $this->urlDocumento($a['audio'] ?? null, $ident, 1);
        }
        return $d;
    }

    private function seccionesDe(string $materia, array $mov, string $ident, int $cuaderno): array
    {
        $cfg = self::CONFIG[$materia];
        $s = [];
        foreach ($cfg['secciones'] as $k) {
            $s[$k] = $mov[$k] ?? ($k === 'relaciones' ? ($mov['Relaciones'] ?? []) : []);
            $s[$k] = is_array($s[$k]) ? $s[$k] : [];
        }

        // Trámites: `doc` pasa a `documentos` con url/tipo/color, y los anexos resuelven su URL.
        $clave = ['civil' => 'historia', 'cobranza' => 'historia', 'familia' => 'movimientos', 'laboral' => 'movimiento', 'penal' => 'historia'][$materia];
        foreach ($s[$clave] as $i => $item) {
            $docs = $item['doc'] ?? null;
            unset($item['doc']);
            $item['documentos'] = $materia === 'penal'
                ? $this->documentosPenal($docs, $ident)
                : $this->documentosTramite($docs, $ident, $cuaderno);
            foreach ($item['anexo'] ?? [] as $j => $a) {
                $item['anexo'][$j]['doc'] = $this->urlDocumento($a['doc'] ?? null, $ident, $cuaderno);
            }
            if ($materia === 'cobranza') {
                $desc = $item['descripcion_tramite'] ?? null;
                $item['descripcion_tramite_doc'] = null;
                if (is_array($desc)) {
                    $item['descripcion_tramite_doc'] = $this->urlDocumento($desc['doc']['ruta'] ?? null, $ident, $cuaderno);
                    $item['descripcion_tramite'] = $desc['descripcion'] ?? null;
                }
                $georref = $item['georref'] ?? null;
                unset($item['georref']);
                $item['georeferencia'] = is_array($georref) && $georref ? $georref : null;
            }
            $s[$clave][$i] = $item;
        }

        if ($materia === 'civil') {
            foreach ($s['escritos_resolver'] as $i => $e) {
                $s['escritos_resolver'][$i]['doc'] = $this->urlDocumento($e['doc'] ?? null, $ident, $cuaderno);
                foreach ($e['anexo'] ?? [] as $j => $a) {
                    $s['escritos_resolver'][$i]['anexo'][$j]['doc'] = $this->urlDocumento($a['doc'] ?? null, $ident, $cuaderno);
                }
            }
            foreach ($s['piezas_exhorto'] as $i => $p) {
                $s['piezas_exhorto'][$i]['doc'] = $this->urlDocumento($p['doc'] ?? null, $ident, $cuaderno);
            }
            foreach ($s['exhortos'] as $i => $x) {
                foreach ($x['rol_destino'] ?? [] as $j => $rd) {
                    foreach ($rd['roles'] ?? [] as $k => $r) {
                        $s['exhortos'][$i]['rol_destino'][$j]['roles'][$k]['doc'] = $this->urlDocumento($r['doc'] ?? null, $ident, $cuaderno);
                    }
                }
            }
        }
        if ($materia === 'cobranza') {
            foreach ($s['liquidacion'] as $i => $l) {
                $s['liquidacion'][$i]['liquidacion'] = $this->urlDocumento($l['liquidacion'] ?? null, $ident, $cuaderno);
            }
        }
        return $s;
    }

    /** `doc` llega como string, lista de strings o lista de {doc|doc2, color}. @return list<array{url:string,tipo:string,color:?string}> */
    private function documentosTramite(mixed $doc, string $ident, int $cuaderno): array
    {
        if (!$doc) {
            return [];
        }
        $crudos = [];
        if (is_string($doc)) {
            $crudos[] = [$doc, 'principal', null];
        } else {
            foreach ($doc as $i => $e) {
                if (is_string($e) && $e !== '') {
                    $crudos[] = [$e, $i === 0 ? 'principal' : 'certificado', null];
                } elseif (is_array($e)) {
                    $color = $e['color'] ?? null;
                    if (!empty($e['doc'])) {
                        $crudos[] = [$e['doc'], 'principal', $color];
                    }
                    if (!empty($e['doc2'])) {
                        $crudos[] = [$e['doc2'], 'certificado', $color];
                    }
                }
            }
        }
        $out = [];
        foreach ($crudos as [$crudo, $tipo, $color]) {
            if ($url = $this->urlDocumento($crudo, $ident, $cuaderno)) {
                $out[] = ['url' => $url, 'tipo' => $tipo, 'color' => $color];
            }
        }
        return $out;
    }

    private function documentosPenal(mixed $doc, string $ident): array
    {
        if (is_string($doc)) {
            $doc = [['doc' => $doc]];
        }
        $out = [];
        foreach ($doc ?: [] as $e) {
            if (is_array($e) && ($url = $this->urlDocumento($e['doc'] ?? null, $ident, 1))) {
                $out[] = ['url' => $url, 'tipo' => 'principal', 'color' => $e['color'] ?? null];
            }
        }
        return $out;
    }

    private function urlDocumento(?string $doc, string $ident, int $cuaderno): ?string
    {
        if (!$doc) {
            return null;
        }
        if (preg_match('#^https?://#i', $doc)) {
            return $doc;
        }
        return sprintf('%s/public/%s/%d/%s', $this->base, rawurlencode($ident), $cuaderno, rawurlencode($doc));
    }

    // ───────────────────────── Documentos ─────────────────────────

    /**
     * Abre en streaming un PDF de /public/... del proveedor. Solo acepta URLs del mismo host que la
     * API y de la ruta /public/ (evita que sirva de proxy abierto), y rehace la petición contra la base.
     */
    public function abrirDocumento(string $url): ResponseInterface
    {
        $base = parse_url($this->base);
        $pedido = parse_url(trim($url));
        $ruta = $pedido['path'] ?? '';
        if (
            !in_array($pedido['scheme'] ?? '', ['http', 'https'], true)
            || ($pedido['host'] ?? null) !== ($base['host'] ?? '')
            || !str_starts_with($ruta, '/public/')
            || str_contains($ruta, '..')
        ) {
            throw new PjudApiException('La URL no corresponde a un documento del PJUD.');
        }
        $destino = $this->base . $ruta . (isset($pedido['query']) ? '?' . $pedido['query'] : '');
        try {
            $resp = $this->http->get($destino, ['headers' => $this->clientKey !== '' ? ['x-client-key' => $this->clientKey] : [], 'stream' => true]);
        } catch (GuzzleException $e) {
            throw new PjudApiException('No se pudo conectar con el PJUD para traer el documento.', 0, $e);
        }
        if ($resp->getStatusCode() === 404) {
            throw new PjudNoEncontrado('El PJUD no tiene este documento.');
        }
        if ($resp->getStatusCode() >= 400) {
            throw new PjudApiException('El PJUD respondió HTTP ' . $resp->getStatusCode() . ' al pedir el documento.');
        }
        return $resp;
    }
}
