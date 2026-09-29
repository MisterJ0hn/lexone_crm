<?php

namespace App\EventListener;

use App\Entity\Usuario;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PasswordExpirationListener implements EventSubscriberInterface
{
    private TokenStorageInterface  $tokenStorage;
    private UrlGeneratorInterface  $urlGenerator;

    /**
     * Rutas que NO deben disparar la redirección (prefijos o nombres exactos).
     */
    private const RUTAS_EXCLUIDAS = [
        '/login',
        '/logout',
        '/reset-password',
        '/password-caducado',
        '/_wdt',
        '/_profiler',
        '/build/',
        '/api/',
    ];

    public function __construct(
        TokenStorageInterface $tokenStorage,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 5],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $ruta = $event->getRequest()->getPathInfo();

        foreach (self::RUTAS_EXCLUIDAS as $excluida) {
            if (str_starts_with($ruta, $excluida)) {
                return;
            }
        }

        try {
            $token   = $this->tokenStorage->getToken();
            $usuario = $token ? $token->getUser() : null;
        } catch (\Exception $e) {
            return;
        }

        if (!$usuario instanceof Usuario) {
            return;
        }

        if ($usuario->isPasswordExpirado()) {
            $url = $this->urlGenerator->generate('password_caducado');
            $event->setResponse(new RedirectResponse($url));
        }
    }
}
