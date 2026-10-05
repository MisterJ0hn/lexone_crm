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
        ];
    }

    public function render(ContratoTemplate $template, Contrato $contrato): string
    {
        return strtr($template->getContenido(), $this->variables($contrato));
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
        ];
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
