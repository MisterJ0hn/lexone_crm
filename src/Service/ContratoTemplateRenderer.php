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
            '{{causas}}' => 'Tabla con las causas del contrato',
            '{{nacionalidad}}' => 'Nacionalidad del cliente',
            '{{estado_civil}}' => 'Estado civil del cliente',
            '{{vigencia}}' => 'Vigencia del contrato (en meses)',
            '{{detalle_cuotas}}' => 'Tabla con el detalle de cuotas (N°, vencimiento, monto). Ancho opcional: {{detalle_cuotas:50%}} o {{detalle_cuotas:400px}}',
            '{{comuna_cliente}}' => 'Comuna del cliente',
            '{{ciudad_cliente}}' => 'Ciudad del cliente',
            '{{situacion_laboral}}' => 'Situación laboral del cliente',
            '{{reunion}}' => 'Reunión (modalidad) del contrato',
        ];
    }

    public function render(ContratoTemplate $template, Contrato $contrato): string
    {
        $contenido = $this->detalleCuotasConAncho($template->getContenido(), $this->cuotasDe($contrato));

        return strtr($contenido, $this->variables($contrato));
    }

    /**
     * Renderiza un contenido (aún sin guardar) con datos inventados, para la
     * previsualización del editor.
     */
    public function renderEjemplo(string $contenido): string
    {
        $contenido = $this->detalleCuotasConAncho($contenido, $this->cuotasEjemplo());

        return strtr($contenido, $this->variablesEjemplo());
    }

    /**
     * @return array<string,string> token => valor de ejemplo
     */
    public function variablesEjemplo(): array
    {
        $causas = '<table border="1" cellspacing="0" cellpadding="4" style="border-collapse:collapse;width:100%">'
            . '<thead><tr><th>Materia</th><th>Causa/Rol</th><th>Caratulado</th><th>Juzgado</th></tr></thead>'
            . '<tbody><tr><td>Cobranza</td><td>C-1234-2026</td><td>Banco Ejemplo / Pérez</td><td>1° Juzgado Civil de Santiago</td></tr>'
            . '<tr><td>Laboral</td><td>O-567-2026</td><td>González / Empresa Demo SpA</td><td>2° Juzgado de Letras del Trabajo</td></tr></tbody></table>';

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
            '{{causas}}' => $causas,
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
     * Admite {{detalle_cuotas:50%}} o {{detalle_cuotas:400px}} (un número sin
     * unidad se toma como %). Solo se aceptan números + % / px, así que el
     * ancho nunca puede inyectar CSS ni HTML arbitrario.
     *
     * @param list<array{0:int|null,1:\DateTimeInterface|null,2:int|float|string|null}> $cuotas
     */
    private function detalleCuotasConAncho(string $contenido, array $cuotas): string
    {
        return preg_replace_callback(
            '/\{\{detalle_cuotas:(\d{1,4})(%|px)?\}\}/',
            function (array $m) use ($cuotas): string {
                $unidad = $m[2] ?? '%';
                $valor = (int) $m[1];
                if ($unidad === '%') {
                    $valor = max(1, min(100, $valor));
                }

                return $this->tablaCuotas($cuotas, $valor . $unidad);
            },
            $contenido
        ) ?? $contenido;
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
     */
    private function tablaCuotas(array $cuotas, string $ancho = '100%'): string
    {
        if ($cuotas === []) {
            return '';
        }

        $filas = '';
        foreach ($cuotas as [$numero, $fecha, $monto]) {
            $filas .= '<tr><td>' . (int) $numero . '</td>'
                . '<td>' . ($fecha ? $fecha->format('d-m-Y') : '') . '</td>'
                . '<td style="text-align:right">$' . number_format((float) $monto, 0, ',', '.') . '</td></tr>';
        }

        return '<table border="1" cellspacing="0" cellpadding="4" style="border-collapse:collapse;width:' . $ancho . '">'
            . '<thead><tr><th>N° cuota</th><th>Vencimiento</th><th>Monto</th></tr></thead>'
            . '<tbody>' . $filas . '</tbody></table>';
    }

    private function causasHtml(Contrato $contrato): string
    {
        $agenda = $contrato->getAgenda();
        if (!$agenda) {
            return '';
        }

        $esPersona = $agenda->getTipoCliente() === null || $agenda->getTipoCliente()->getNombre() === 'Persona';

        $filas = '';
        foreach ($agenda->getCausas() as $causa) {
            if (!$causa->getEstado()) {
                continue;
            }
            $filas .= $this->filaCausa($causa, $esPersona);
        }

        if ($filas === '') {
            return '';
        }

        $columnaCliente = $esPersona ? '' : '<th>Cliente</th>';

        return '<table border="1" cellspacing="0" cellpadding="4" style="border-collapse:collapse;width:100%">'
            . '<thead><tr>' . $columnaCliente . '<th>Materia</th><th>Causa/Rol</th><th>Caratulado</th><th>Juzgado</th></tr></thead>'
            . '<tbody>' . $filas . '</tbody></table>';
    }

    private function filaCausa(Causa $causa, bool $esPersona): string
    {
        $rol = trim(sprintf('%s-%s-%s', $causa->getLetra(), $causa->getRol(), $causa->getAnio()), '-');

        $materia = $causa->getServicio()
            ? (string) $causa->getServicio()
            : ($causa->getMateria() ? (string) $causa->getMateria()->getNombre() : '');

        $juzgado = $causa->getJuzgado()
            ? (string) $causa->getJuzgado()->getNombre()
            : ($causa->getJuzgadoCuenta() && $causa->getJuzgadoCuenta()->getJuzgado() ? (string) $causa->getJuzgadoCuenta()->getJuzgado()->getNombre() : '');

        $columnaCliente = $esPersona ? '' : '<td>' . htmlspecialchars((string) ($causa->getCliente() ? $causa->getCliente()->getNombre() : '')) . '</td>';

        return '<tr>' . $columnaCliente
            . '<td>' . htmlspecialchars($materia) . '</td>'
            . '<td>' . htmlspecialchars($rol) . '</td>'
            . '<td>' . htmlspecialchars((string) $causa->getCausaNombre()) . '</td>'
            . '<td>' . htmlspecialchars($juzgado) . '</td>'
            . '</tr>';
    }
}
