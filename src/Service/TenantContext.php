<?php

namespace App\Service;

/**
 * Empresa (tenant) resuelta para el request en curso. Poblada por
 * App\EventListener\TenantSubscriber al inicio de cada request.
 *
 * Es el punto de acceso al tenant actual para código que no tiene (o no debería
 * depender de) el Usuario autenticado — p.ej. un futuro SQLFilter o un listener
 * prePersist (Fase B del rediseño de tenant). Para el código existente, basta con
 * seguir llamando a Usuario::getEmpresaActual(), que ya refleja este mismo valor.
 */
class TenantContext
{
    private ?int $empresaId = null;

    public function getEmpresaId(): ?int
    {
        return $this->empresaId;
    }

    public function setEmpresaId(?int $empresaId): void
    {
        $this->empresaId = $empresaId;
    }
}
