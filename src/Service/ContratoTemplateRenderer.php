<?php

namespace App\Service;

use App\Entity\Causa;
use App\Entity\Contrato;
use App\Entity\ContratoTemplate;

/**
 * Reemplaza las variables de una ContratoTemplate (p.ej. {{cliente_nombre}})
 * por los datos de un Contrato concreto.
 *
 * El contenido de la plantilla lo edita el propio tenant, así que el
 * reemplazo es un strtr() literal sobre texto: nunca se compila ni ejecuta
 * como Twig/PHP (evita que una plantilla pueda inyectar código).
 */
class ContratoTemplateRenderer
{
    /**
     * Catálogo de variables disponibles, para mostrarlo en el editor.
     *
     * @return array<string,string> token => descripción
     */
    public static function catalogo(): array
    {
        return [
            '{{cliente_nombre}}' => 'Nombre del cliente',
            '{{cliente_rut}}' => 'Rut del cliente',
            '{{cliente_email}}' => 'Correo del cliente',
            '{{cliente_telefono}}' => 'Teléfono del cliente',
            '{{cliente_direccion}}' => 'Dirección del cliente',
            '{{tipo_contrato}}' => 'Tipo de cliente (Persona/Empresa/Convenio)',
            '{{folio}}' => 'Folio del contrato',
            '{{cuotas}}' => 'Número de cuotas',
            '{{monto_contrato}}' => 'Monto del contrato',
            '{{fecha_contrato}}' => 'Fecha de creación del contrato',
            '{{causas}}' => 'Tabla con las causas del contrato. Use ⚙ para elegir columnas y cabeceras',
            '{{nacionalidad}}' => 'Nacionalidad del cliente',
            '{{estado_civil}}' => 'Estado civil del cliente',
            '{{vigencia}}' => 'Vigencia del contrato (en meses)',
            '{{detalle_cuotas}}' => 'Tabla con el detalle de cuotas (N°, vencimiento, monto). Ancho opcional: {{detalle_cuotas:50%}} o {{detalle_cuotas:400px}}. Use ⚙ para elegir columnas y cabeceras',
            '{{comuna_cliente}}' => 'Comuna del cliente',
            '{{ciudad_cliente}}' => 'Ciudad del cliente',
            '{{situacion_laboral}}' => 'Situación laboral del cliente',
            '{{reunion}}' => 'Reunión (modalidad) del contrato',
        ];
    }

    public function render(ContratoTemplate $template, Contrato $contrato): string
    {
        $contenido = $this->tablasConOpciones(
            $template->getContenido(),
            fn (?array $cols, string $ancho): string => $this->causasHtml($contrato, $cols, $ancho),
            fn (?array $cols, string $ancho): string => $this->tablaCuotas($this->cuotasDe($contrato), $ancho, $cols)
        );

        return strtr($contenido, $this->variables($contrato));
    }

    /**
     * Renderiza un contenido (aún sin guardar) con datos inventados, para la
     * previsualización del editor.
     */
    public function renderEjemplo(string $contenido): string
    {
        $contenido = $this->tablasConOpciones(
            $contenido,
            fn (?array $cols, string $ancho): string => $this->causasEjemplo($cols, $ancho),
            fn (?array $cols, string $ancho): string => $this->tablaCuotas($this->cuotasEjemplo(), $ancho, $cols)
        );

        return strtr($contenido, $this->variablesEjemplo());
    }

    /**
     * @return array<string,string> token => valor de ejemplo
     */
    public function variablesEjemplo(): array
    {
        return [
            '{{cliente_nombre}}' => 'Juan Andrés Pérez Soto',
            '{{cliente_rut}}' => '12.345.678-5',
            '{{cliente_email}}' => 'juan.perez@ejemplo.cl',
            '{{cliente_telefono}}' => '+56 9 1234 5678',
            '{{cliente_direccion}}' => 'Av. Providencia 1234, Of. 56, Providencia',
            '{{tipo_contrato}}' => 'Persona',
            '{{folio}}' => '1024',
            '{{cuotas}}' => '6',
            '{{monto_contrato}}' => '900.000',
            '{{fecha_contrato}}' => date('d-m-Y'),
            '{{causas}}' => $this->causasEjemplo(null),
            '{{nacionalidad}}' => 'Chilena',
            '{{estado_civil}}' => 'Casado',
            '{{vigencia}}' => '12',
            '{{detalle_cuotas}}' => $this->tablaCuotas($this->cuotasEjemplo()),
            '{{comuna_cliente}}' => 'Providencia',
            '{{ciudad_cliente}}' => 'Santiago',
            '{{situacion_laboral}}' => 'Dependiente',
            '{{reunion}}' => 'Presencial',
        ];
    }

    /**
     * @return array<string,string> token => valor, listo para strtr()
     */
    public function variables(Contrato $contrato): array
    {
        $cliente = $contrato->getCliente();
        $agenda = $contrato->getAgenda();

        return [
            '{{cliente_nombre}}' => $cliente ? (string) $cliente->getNombre() : '',
            '{{cliente_rut}}' => $cliente ? (string) $cliente->getRut() : '',
            '{{cliente_email}}' => $cliente ? (string) $cliente->getCorreo() : '',
            '{{cliente_telefono}}' => $cliente ? (string) $cliente->getTelefono() : '',
            '{{cliente_direccion}}' => $cliente ? (string) $cliente->getDireccion() : '',
            '{{tipo_contrato}}' => ($agenda && $agenda->getTipoCliente()) ? (string) $agenda->getTipoCliente()->getNombre() : 'Persona',
            '{{folio}}' => (string) $contrato->getFolio(),
            '{{cuotas}}' => (string) $contrato->getCuotas(),
            '{{monto_contrato}}' => $contrato->getMontoContrato() !== null ? number_format((float) $contrato->getMontoContrato(), 0, ',', '.') : '',
            '{{fecha_contrato}}' => $contrato->getFechaCreacion() ? $contrato->getFechaCreacion()->format('d-m-Y') : '',
            '{{causas}}' => $this->causasHtml($contrato),
            '{{nacionalidad}}' => $this->nacionalidad($contrato),
            '{{estado_civil}}' => $this->estadoCivil($contrato),
            '{{vigencia}}' => $contrato->getVigencia() !== null ? (string) $contrato->getVigencia() : '',
            '{{detalle_cuotas}}' => $this->detalleCuotasHtml($contrato),
            '{{comuna_cliente}}' => $contrato->getComuna() ?: ($cliente && $cliente->getComuna() ? (string) $cliente->getComuna()->getNombre() : ''),
            '{{ciudad_cliente}}' => $contrato->getCiudad() ?: ($cliente && $cliente->getCiudad() ? (string) $cliente->getCiudad()->getNombre() : ''),
            '{{situacion_laboral}}' => $contrato->getSituacionLaboral() ? (string) $contrato->getSituacionLaboral()->getNombre() : '',
            '{{reunion}}' => ($reunion = $contrato->getReunion() ?? ($cliente ? $cliente->getReunion() : null)) ? (string) $reunion->getNombre() : '',
        ];
    }

    /**
     * Para Persona la nacionalidad viene del país del contrato; para
     * Convenio/Empresa, del campo propio del cliente (ver Cliente::$nacionalidad).
     */
    private function nacionalidad(Contrato $contrato): string
    {
        $cliente = $contrato->getCliente();
        if ($cliente && $cliente->getNacionalidad()) {
            return (string) $cliente->getNacionalidad();
        }

        return $contrato->getPais() ? (string) $contrato->getPais()->getNombre() : '';
    }

    private function estadoCivil(Contrato $contrato): string
    {
        $estadoCivil = $contrato->getEstadoCivil() ?? ($contrato->getCliente() ? $contrato->getCliente()->getEstadoCivil() : null);

        return $estadoCivil ? (string) $estadoCivil->getNombre() : '';
    }

    /**
     * Columnas que se pueden elegir en {{causas}} y {{detalle_cuotas}}, con su cabecera por defecto.
     * Sirve tanto para renderizar como para armar el selector del editor.
     *
     * @return array<string,array<string,string>> tabla => [columna => cabecera]
     */
    public static function columnasTabla(): array
    {
        return [
            'causas' => [
                'cliente' => 'Cliente',
                'materia' => 'Materia',
                'servicio' => 'Servicio',
                'rol' => 'Causa/Rol',
                'caratulado' => 'Caratulado',
                'juzgado' => 'Juzgado',
            ],
            'detalle_cuotas' => [
                'numero' => 'N° cuota',
                'vencimiento' => 'Vencimiento',
                'monto' => 'Monto',
            ],
        ];
    }

    /**
     * Sintaxis: {{causas}} · {{detalle_cuotas:50%}} · {{causas|materia=Servicio;rol=Id Causa}}
     * · {{detalle_cuotas:60%|numero=N°;monto=Valor}}. Tras la barra van, en orden, las columnas
     * a mostrar con su cabecera (opcional). Sólo se aceptan columnas conocidas, el ancho es
     * número + % / px (un número sin unidad es %) y las cabeceras se escapan: nada de lo que
     * escriba el usuario llega como HTML ni como CSS.
     *
     * @param callable(?array<string,string>,string):string $causas
     * @param callable(?array<string,string>,string):string $cuotas
     */
    private function tablasConOpciones(string $contenido, callable $causas, callable $cuotas): string
    {
        return preg_replace_callback(
            '/\{\{(causas|detalle_cuotas)(?::(\d{1,4})(%|px)?)?(?:\|([^{}]*))?\}\}/u',
            function (array $m) use ($causas, $cuotas): string {
                $ancho = '100%';
                if (($m[2] ?? '') !== '') {
                    $unidad = ($m[3] ?? '') ?: '%';
                    $valor = (int) $m[2];
                    if ($unidad === '%') {
                        $valor = max(1, min(100, $valor));
                    }
                    $ancho = $valor . $unidad;
                }
                $cols = $this->parseColumnas($m[1], $m[4] ?? '');

                return $m[1] === 'causas' ? $causas($cols, $ancho) : $cuotas($cols, $ancho);
            },
            $contenido
        ) ?? $contenido;
    }

    /**
     * @return array<string,string>|null columna => cabecera (en el orden pedido); null = columnas por defecto
     */
    private function parseColumnas(string $tabla, string $crudo): ?array
    {
        $validas = self::columnasTabla()[$tabla];
        $crudo = html_entity_decode(strip_tags($crudo), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $crudo = str_replace("\u{00A0}", ' ', $crudo);

        $cols = [];
        foreach (explode(';', $crudo) as $parte) {
            [$clave, $etiqueta] = array_pad(explode('=', $parte, 2), 2, '');
            $clave = strtolower(trim($clave));
            if (!isset($validas[$clave]) || isset($cols[$clave])) {
                continue;
            }
            $etiqueta = trim((string) preg_replace('/\s+/u', ' ', $etiqueta));
            $cols[$clave] = $etiqueta !== '' ? mb_substr($etiqueta, 0, 60) : $validas[$clave];
        }

        return $cols !== [] ? $cols : null;
    }

    /**
     * @param array<string,string> $cols columna => cabecera
     * @param list<array<string,string>> $filas texto (sin escapar) de cada columna, por fila
     * @param list<string> $derecha columnas alineadas a la derecha
     */
    private function armarTabla(array $cols, array $filas, string $ancho, array $derecha = []): string
    {
        $cabecera = '';
        foreach ($cols as $etiqueta) {
            $cabecera .= '<th>' . htmlspecialchars($etiqueta) . '</th>';
        }
        $cuerpo = '';
        foreach ($filas as $fila) {
            $cuerpo .= '<tr>';
            foreach (array_keys($cols) as $clave) {
                $estilo = in_array($clave, $derecha, true) ? ' style="text-align:right"' : '';
                $cuerpo .= '<td' . $estilo . '>' . htmlspecialchars($fila[$clave] ?? '') . '</td>';
            }
            $cuerpo .= '</tr>';
        }

        return '<table border="1" cellspacing="0" cellpadding="4" style="border-collapse:collapse;width:' . $ancho . '">'
            . '<thead><tr>' . $cabecera . '</tr></thead><tbody>' . $cuerpo . '</tbody></table>';
    }

    /**
     * @return list<array{0:int|null,1:\DateTimeInterface|null,2:int|float|string|null}>
     */
    private function cuotasEjemplo(): array
    {
        $cuotas = [];
        for ($i = 1; $i <= 6; $i++) {
            $cuotas[] = [$i, (new \DateTimeImmutable(sprintf('first day of +%d month', $i)))->modify('+4 days'), 150000];
        }

        return $cuotas;
    }

    private function detalleCuotasHtml(Contrato $contrato): string
    {
        return $this->tablaCuotas($this->cuotasDe($contrato));
    }

    /**
     * @return list<array{0:int|null,1:\DateTimeInterface|null,2:int|float|string|null}>
     */
    private function cuotasDe(Contrato $contrato): array
    {
        $cuotas = [];
        foreach ($contrato->getDetalleCuotas() as $cuota) {
            if ($cuota->getAnular()) {
                continue;
            }
            $cuotas[] = [$cuota->getNumero(), $cuota->getFechaPago(), $cuota->getMonto()];
        }
        usort($cuotas, static fn (array $a, array $b) => $a[0] <=> $b[0]);

        return $cuotas;
    }

    /**
     * @param list<array{0:int|null,1:\DateTimeInterface|null,2:int|float|string|null}> $cuotas
     * @param array<string,string>|null $cols columna => cabecera; null = todas con su cabecera por defecto
     */
    private function tablaCuotas(array $cuotas, string $ancho = '100%', ?array $cols = null): string
    {
        if ($cuotas === []) {
            return '';
        }
        $cols ??= self::columnasTabla()['detalle_cuotas'];

        $filas = [];
        foreach ($cuotas as [$numero, $fecha, $monto]) {
            $filas[] = [
                'numero' => (string) (int) $numero,
                'vencimiento' => $fecha ? $fecha->format('d-m-Y') : '',
                'monto' => '$' . number_format((float) $monto, 0, ',', '.'),
            ];
        }

        return $this->armarTabla($cols, $filas, $ancho, ['monto']);
    }

    /**
     * @param array<string,string>|null $cols columna => cabecera; null = las de siempre (Cliente sólo si no es Persona)
     */
    private function causasHtml(Contrato $contrato, ?array $cols = null, string $ancho = '100%'): string
    {
        $agenda = $contrato->getAgenda();
        if (!$agenda) {
            return '';
        }

        $esPersona = $agenda->getTipoCliente() === null || $agenda->getTipoCliente()->getNombre() === 'Persona';
        $cols ??= $this->columnasCausasPorDefecto($esPersona);

        $filas = [];
        foreach ($agenda->getCausas() as $causa) {
            if (!$causa->getEstado()) {
                continue;
            }
            $filas[] = $this->datosCausa($causa);
        }
        if ($filas === []) {
            return '';
        }

        return $this->armarTabla($cols, $filas, $ancho);
    }

    /**
     * @return array<string,string>
     */
    private function columnasCausasPorDefecto(bool $esPersona): array
    {
        $cols = self::columnasTabla()['causas'];
        if ($esPersona) {
            unset($cols['cliente']);
        }

        return $cols;
    }

    /**
     * @param array<string,string>|null $cols
     */
    private function causasEjemplo(?array $cols, string $ancho = '100%'): string
    {
        $cols ??= $this->columnasCausasPorDefecto(true);
        $filas = [
            ['cliente' => 'Juan Andrés Pérez Soto', 'materia' => 'Cobranza', 'servicio' => 'Demanda ejecutiva', 'rol' => 'C-1234-2026', 'caratulado' => 'Banco Ejemplo / Pérez', 'juzgado' => '1° Juzgado Civil de Santiago'],
            ['cliente' => 'Juan Andrés Pérez Soto', 'materia' => 'Laboral', 'servicio' => 'Despido injustificado', 'rol' => 'O-567-2026', 'caratulado' => 'González / Empresa Demo SpA', 'juzgado' => '2° Juzgado de Letras del Trabajo'],
        ];

        return $this->armarTabla($cols, $filas, $ancho);
    }

    /**
     * @return array<string,string>
     */
    private function datosCausa(Causa $causa): array
    {
        $rol = trim(sprintf('%s-%s-%s', $causa->getLetra(), $causa->getRol(), $causa->getAnio()), '-');

        $materia = $causa->getServicio()
            ? (string) $causa->getServicio()
            : ($causa->getMateria() ? (string) $causa->getMateria()->getNombre() : '');

        $juzgado = $causa->getJuzgado()
            ? (string) $causa->getJuzgado()->getNombre()  : ''; //No tocar, este es un fix poir una limpieza de datos que se hizo en la base de datos, ya que algunos juzgados no tenian nombre y eso generaba error al mostrar la tabla

        return [
            'cliente' => (string) ($causa->getCliente() ? $causa->getCliente()->getNombre() : ''),
            'materia' => $materia,
            'servicio' => $causa->getServicio() ? (string) $causa->getServicio() : '',
            'rol' => $rol,
            'caratulado' => (string) $causa->getCausaNombre(),
            'juzgado' => $juzgado,
        ];
    }
}
