<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Form\ChangePasswordFormType;
use App\Service\PasswordService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/password-caducado")
 */
class PasswordCaducadoController extends AbstractController
{
    /**
     * @Route("", name="password_caducado", methods={"GET","POST"})
     */
    public function index(Request $request, PasswordService $passwordService): Response
    {
        /** @var Usuario|null $usuario */
        $usuario = $this->getUser();

        if (!$usuario instanceof Usuario) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            if ($passwordService->yaFueUsada($usuario, $plainPassword)) {
                $this->addFlash('error', 'No puedes reutilizar una contraseña anterior. Elige una diferente.');
            } else {
                $passwordService->aplicarNuevoPassword($usuario, $plainPassword);
                $this->addFlash('success', 'Contraseña actualizada correctamente. Ya puedes continuar.');

                return $this->redirectToRoute('dashboard');
            }
        }

        return $this->render('password_caducado/index.html.twig', [
            'form'           => $form->createView(),
            'diasExpiracion' => $passwordService->getDiasExpiracion(),
        ]);
    }
}
