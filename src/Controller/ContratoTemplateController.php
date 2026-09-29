<?php

namespace App\Controller;

use App\Entity\ContratoTemplate;
use App\Form\ContratoTemplateType;
use App\Repository\ContratoTemplateRepository;
use App\Repository\EmpresaRepository;
use App\Repository\ModuloPerRepository;
use App\Service\ContratoTemplateRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Plantillas de contrato editables por tenant (Empresa) y por TipoCliente,
 * usadas por ContratoController::pdf() para generar el contrato.
 */
#[Route("/contrato_template")]
class ContratoTemplateController extends AbstractController
{
    #[Route("/", name: "contrato_template_index", methods: ["GET"])]
    public function index(ContratoTemplateRepository $contratoTemplateRepository, ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view', 'contrato_template');
        $user = $this->getUser();
        $pagina = $moduloPerRepository->findOneByName('contrato_template', 1);

        return $this->render('contrato_template/index.html.twig', [
            'contratoTemplates' => $contratoTemplateRepository->findBy(['empresa' => $user->getEmpresaActual()], ['tipoCliente' => 'ASC', 'nombre' => 'ASC']),
            'pagina' => $pagina ? $pagina->getNombre() : 'Plantillas de Contrato',
        ]);
    }

    #[Route("/new", name: "contrato_template_new", methods: ["GET","POST"])]
    public function new(Request $request, EmpresaRepository $empresaRepository): Response
    {
        $this->denyAccessUnlessGranted('create', 'contrato_template');
        $user = $this->getUser();

        $contratoTemplate = new ContratoTemplate();
        $contratoTemplate->setEmpresa($empresaRepository->find($user->getEmpresaActual()));
        $contratoTemplate->setUsuarioRegistro($user);
        $contratoTemplate->setFechaCreacion(new \DateTime());

        $form = $this->createForm(ContratoTemplateType::class, $contratoTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contratoTemplate);
            $entityManager->flush();

            $this->addFlash('success', 'Plantilla creada correctamente.');

            return $this->redirectToRoute('contrato_template_index');
        }

        return $this->render('contrato_template/new.html.twig', [
            'contratoTemplate' => $contratoTemplate,
            'form' => $form->createView(),
            'variables' => ContratoTemplateRenderer::catalogo(),
        ]);
    }

    #[Route("/{id}/edit", name: "contrato_template_edit", methods: ["GET","POST"])]
    public function edit(Request $request, ContratoTemplate $contratoTemplate): Response
    {
        $this->denyAccessUnlessGranted('edit', 'contrato_template');
        $this->verificarTenant($contratoTemplate);

        $form = $this->createForm(ContratoTemplateType::class, $contratoTemplate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contratoTemplate->setFechaModificacion(new \DateTime());

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Plantilla actualizada correctamente.');

            return $this->redirectToRoute('contrato_template_index');
        }

        return $this->render('contrato_template/edit.html.twig', [
            'contratoTemplate' => $contratoTemplate,
            'form' => $form->createView(),
            'variables' => ContratoTemplateRenderer::catalogo(),
        ]);
    }

    #[Route("/{id}", name: "contrato_template_delete", methods: ["DELETE"])]
    public function delete(Request $request, ContratoTemplate $contratoTemplate): Response
    {
        $this->denyAccessUnlessGranted('full', 'contrato_template');
        $this->verificarTenant($contratoTemplate);

        if ($this->isCsrfTokenValid('delete'.$contratoTemplate->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($contratoTemplate);
            $entityManager->flush();
        }

        return $this->redirectToRoute('contrato_template_index');
    }

    /**
     * Corta camino a un IDOR: sin esto, cualquier usuario con permiso sobre el
     * módulo podría editar/borrar la plantilla de otra empresa por id.
     */
    private function verificarTenant(ContratoTemplate $contratoTemplate): void
    {
        $user = $this->getUser();
        if (!$contratoTemplate->getEmpresa() || $contratoTemplate->getEmpresa()->getId() !== $user->getEmpresaActual()) {
            throw $this->createNotFoundException('Plantilla no encontrada.');
        }
    }

}
