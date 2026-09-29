<?php

namespace App\EventListener;

use App\Entity\Usuario;
use App\Repository\EmpresaRepository;
use App\Service\TenantContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Resuelve la empresa (tenant) "actual" del usuario autenticado en cada request,
 * a partir de la sesión, y la deja disponible en dos lugares:
 *  - Usuario::getEmpresaActual() (vía el override en memoria, no persistido)
 *  - TenantContext::getEmpresaId()
 *
 * Reemplaza el mecanismo anterior (columna usuario.empresa_actual escrita en cada
 * cambio de compañía, sin validar membresía) por uno que:
 *  1. Sólo permite elegir una empresa a la que el usuario realmente pertenece
 *     (EmpresaRepository::findDisponiblesParaUsuario, derivado de UsuarioCuenta).
 *  2. Vive en sesión, no en BD: pestañas/sesiones concurrentes ya no se pisan
 *     entre sí a través del mismo registro de usuario.
 *  3. Bloquea el acceso si la empresa resuelta ya no está vigente.
 */
class TenantSubscriber implements EventSubscriberInterface
{
    public const SESSION_KEY = '_tenant_empresa';

    /**
     * Rutas que no deben verse afectadas por el bloqueo de vigencia (prefijos de
     * path). Mismo criterio que PasswordExpirationListener.
     */
    private const RUTAS_EXCLUIDAS = [
        '/login',
        '/logout',
        '/reset-password',
        '/changecomp',
        '/_wdt',
        '/_profiler',
        '/build/',
        '/api/',
    ];

    private Security $security;
    private EmpresaRepository $empresaRepository;
    private TenantContext $tenantContext;

    public function __construct(
        Security $security,
        EmpresaRepository $empresaRepository,
        TenantContext $tenantContext
    ) {
        $this->security = $security;
        $this->empresaRepository = $empresaRepository;
        $this->tenantContext = $tenantContext;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 6],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $ruta = $request->getPathInfo();
        foreach (self::RUTAS_EXCLUIDAS as $excluida) {
            if (str_starts_with($ruta, $excluida)) {
                return;
            }
        }

        $usuario = $this->security->getUser();
        if (!$usuario instanceof Usuario) {
            return;
        }
        if (!$request->hasSession()) {
            return;
        }

        $esSuperAdmin = $usuario->getUsuarioTipo() !== null && $usuario->getUsuarioTipo()->getId() === 8;

        if ($esSuperAdmin && $request->hasSession()) {
            // El super-admin puede operar cualquier empresa; su elección vive en
            // sesión. Si no hay elección válida, cae a su empresa de membresía.
            $session = $request->getSession();
            $elegido = $session->get(self::SESSION_KEY);
            if ($elegido === null || $this->empresaRepository->find($elegido) === null) {
                $elegido = $usuario->getEmpresa() !== null ? $usuario->getEmpresa()->getId() : null;
                $session->set(self::SESSION_KEY, $elegido);
            }
            $usuario->setEmpresaActualOverride($elegido);
        } else {
            // Usuario normal: fijo a su empresa de membresía (sin override).
            $usuario->setEmpresaActualOverride(null);
        }

        $empresaId = $usuario->getEmpresaActual();
        $this->tenantContext->setEmpresaId($empresaId);

        if ($empresaId !== null) {
            $empresa = $this->empresaRepository->find($empresaId);
            if ($empresa !== null && $empresa->getFechaVigencia() < new \DateTime()) {
                throw new AccessDeniedHttpException('La vigencia de la empresa ha expirado.');
            }
        }
    }
}
