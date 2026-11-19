<?php

namespace App\EventListener;

use App\Entity\Agenda;
use App\Entity\Cuenta;
use App\Entity\Empresa;
use App\Entity\Ticket;
use App\Entity\Usuario;
use App\Service\TenantContext;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\Proxy;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Defensa contra acceso por ID en la URL (IDOR): revisa cada entidad que Symfony
 * entrega al controlador (p.ej. show(Contrato $contrato)) y responde 404 si pertenece
 * a otra empresa. Complementa a App\Doctrine\TenantFilter, que cubre las consultas
 * hechas a mano con find()/repositorios.
 *
 * La empresa de una entidad se deduce siguiendo su cadena de pertenencia
 * (Pago -> Contrato -> Agenda -> Empresa). Si no se puede determinar (catálogos sin
 * empresa), se deja pasar.
 */
class TenantAccessListener implements EventSubscriberInterface
{
    /** Getters que llevan hacia la empresa, en orden de preferencia. */
    private const CAMINOS = ['getEmpresa', 'getAgenda', 'getContrato', 'getCuenta', 'getPago', 'getTicket', 'getCausa'];

    /**
     * Entidades con "empresa" propia que SÍ son datos de una empresa. Cualquier otra
     * entidad con getEmpresa() (Materia, Juzgado, Pais...) es catálogo posiblemente
     * compartido y no se bloquea. Las entidades hijas (Contrato, Pago, Cuota...) no
     * tienen getEmpresa() y se resuelven por su cadena.
     */
    private const CON_EMPRESA_PROPIA = [Empresa::class, Usuario::class, Agenda::class, Cuenta::class, Ticket::class];

    private const NO_PERTENECE = -1;

    public function __construct(
        private TenantContext $tenantContext,
        private EntityManagerInterface $em
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER_ARGUMENTS => 'onControllerArguments'];
    }

    public function onControllerArguments(ControllerArgumentsEvent $event): void
    {
        $empresaId = $this->tenantContext->getEmpresaId();
        if ($empresaId === null || $this->tenantContext->isSuperAdmin()) {
            return;
        }

        foreach ($event->getArguments() as $argumento) {
            if (!is_object($argumento) || $this->em->getMetadataFactory()->isTransient($this->claseReal($argumento))) {
                continue;
            }

            $duena = $this->empresaDe($argumento, 0);
            if ($duena !== null && $duena !== $empresaId) {
                throw new NotFoundHttpException('Recurso no encontrado.');
            }
        }
    }

    private function empresaDe(object $entidad, int $profundidad): ?int
    {
        if ($entidad instanceof Empresa) {
            return $entidad->getId();
        }
        if ($profundidad > 5) {
            return null;
        }
        if ($profundidad === 0
            && method_exists($entidad, 'getEmpresa')
            && !in_array($this->claseReal($entidad), self::CON_EMPRESA_PROPIA, true)) {
            return null;
        }

        try {
            foreach (self::CAMINOS as $getter) {
                if (!method_exists($entidad, $getter)) {
                    continue;
                }
                $padre = $entidad->$getter();
                if (!is_object($padre)) {
                    continue;
                }
                if ($padre instanceof Proxy) {
                    $padre->__load();
                }
                $resultado = $this->empresaDe($padre, $profundidad + 1);
                if ($resultado !== null) {
                    return $resultado;
                }
            }
        } catch (EntityNotFoundException) {
            // El TenantFilter ocultó el padre: pertenece a otra empresa.
            return self::NO_PERTENECE;
        }

        return null;
    }

    private function claseReal(object $objeto): string
    {
        return \Doctrine\Common\Util\ClassUtils::getClass($objeto);
    }
}
