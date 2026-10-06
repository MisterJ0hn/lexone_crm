<?php

namespace App\Controller;

use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Guarda en el usuario el tema de interfaz (claro/oscuro) que eligió con el
 * botón de la barra superior; base.html.twig lo aplica al cargar cada página.
 */
class TemaController extends AbstractController
{
    #[Route("/tema", name: "tema_guardar", methods: ["POST"])]
    public function guardar(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        if (!$this->isCsrfTokenValid('tema', $request->request->get('_token'))) {
            return new JsonResponse(['ok' => false], 403);
        }

        /** @var Usuario $user */
        $user = $this->getUser();
        $user->setTema($request->request->get('tema') === 'oscuro' ? 'oscuro' : 'claro');
        $entityManager->flush();

        return new JsonResponse(['ok' => true, 'tema' => $user->getTema()]);
    }
}
