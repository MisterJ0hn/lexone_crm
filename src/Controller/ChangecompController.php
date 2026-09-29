<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\EventListener\TenantSubscriber;
use App\Repository\EmpresaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/changecomp")
 */
class ChangecompController extends AbstractController
{
    /**
     * @Route("/", name="changecomp_index")
     */
    public function index(string $route_name, EmpresaRepository $empresaRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        /** @var Usuario $user */
        $user = $this->getUser();

        // Sólo se listan las empresas a las que el usuario realmente pertenece
        // (antes se listaban todas — cualquiera podía cambiarse a cualquier empresa).
        $empresas = $empresaRepository->findDisponiblesParaUsuario($user);

        return $this->render('changecomp/index.html.twig', [
            'controller_name' => 'ChangecompController',
            'empresas' => $empresas,
            'route' => $route_name,
            'id_empresa' => $user->getEmpresaActual(),
        ]);
    }

    /**
     * @Route("/new", name="changecomp_new", methods={"GET","POST"})
     */
    public function new(EmpresaRepository $empresaRepository, Request $request, UrlGeneratorInterface $urlGenerator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        /** @var Usuario $user */
        $user = $this->getUser();

        $empresaId = (int) $request->request->get('company');
        $route = $request->request->get('route');

        // El cambio de empresa se guarda en sesión, no en BD (ver TenantSubscriber):
        // así no se pisa entre pestañas/sesiones concurrentes del mismo usuario, y
        // sólo se puede elegir una empresa a la que el usuario pertenece.
        if ($empresaRepository->esMiembro($user, $empresaId)) {
            $request->getSession()->set(TenantSubscriber::SESSION_KEY, $empresaId);
        } else {
            $this->addFlash('error', 'No tienes acceso a esa empresa.');
        }

        return new RedirectResponse($urlGenerator->generate($route));
    }
}
