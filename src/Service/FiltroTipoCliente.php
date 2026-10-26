<?php

namespace App\Service;

/**
 * Condición DQL para el filtro "Cliente" (bTipoCliente) de filtro.html.twig.
 * Persona incluye las agendas sin tipo de cliente (se tratan como Persona).
 */
final class FiltroTipoCliente
{
    public const TIPOS = ['Persona', 'Empresa', 'Convenio'];

    /**
     * @param string $agendaExpr expresión DQL que apunta a la agenda ('a' o 'c.agenda')
     */
    public static function condicion(?string $tipo, string $agendaExpr = 'a'): string
    {
        if (!in_array($tipo, self::TIPOS, true)) {
            return '';
        }

        return sprintf(
            "%s IN (SELECT agf.id FROM App\\Entity\\Agenda agf LEFT JOIN agf.tipoCliente tcf WHERE tcf.nombre = '%s'%s)",
            $agendaExpr,
            $tipo,
            $tipo === 'Persona' ? ' OR tcf.id IS NULL' : ''
        );
    }

    /** Lee bTipoCliente del request y lo valida contra la lista permitida. */
    public static function desdeRequest(\Symfony\Component\HttpFoundation\Request $request): ?string
    {
        $tipo = $request->query->get('bTipoCliente');

        return in_array($tipo, self::TIPOS, true) ? $tipo : null;
    }
}
