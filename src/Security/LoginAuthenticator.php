<?php

namespace App\Security;

use App\Entity\Usuario;
use App\Service\RecaptchaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RecaptchaService $recaptcha,
    ) {
    }

    /** Clave de sesión con el último RUT de empresa digitado, para repoblar el formulario tras un error. */
    public const LAST_RUT_EMPRESA = '_security.last_rut_empresa';

    /** Deja solo dígitos y K en mayúscula: "76.123.456-7" y "761234567" comparan igual. */
    public static function normalizarRut(string $rut): string
    {
        return strtoupper(preg_replace('/[^0-9kK]/', '', $rut) ?? '');
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('username', '');
        $recaptchaResponse = $request->request->get('g-recaptcha-response', '');
        $clientIp = $request->getClientIp() ?? '';

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $username);

        if (!$this->recaptcha->verificar($recaptchaResponse, $clientIp)) {
            throw new CustomUserMessageAuthenticationException('Por favor completa la verificación reCAPTCHA.');
        }

        $rutEmpresa = self::normalizarRut((string) $request->request->get('rut_empresa', ''));
        $request->getSession()->set(self::LAST_RUT_EMPRESA, (string) $request->request->get('rut_empresa', ''));

        return new Passport(
            new UserBadge($username, function (string $userIdentifier) use ($rutEmpresa): Usuario {
                // El username se repite entre empresas: se elige el que pertenece a la del RUT digitado.
                $user = null;
                if ($rutEmpresa !== '') {
                    $candidatos = $this->entityManager->getRepository(Usuario::class)->findBy([
                        'username' => $userIdentifier,
                        'estado' => 1,
                    ]);
                    foreach ($candidatos as $candidato) {
                        if (self::normalizarRut((string) $candidato->getEmpresa()?->getRut()) === $rutEmpresa) {
                            $user = $candidato;
                            break;
                        }
                    }
                }

                // Mismo mensaje que usuario inexistente para no revelar qué dato falló.
                if (!$user) {
                    throw new CustomUserMessageAuthenticationException('Username could not be found.');
                }

                return $user;
            }),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('dashboard'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
