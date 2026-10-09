<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\ContratoTemplate;
use App\Form\ContratoTemplateType;
use App\Repository\ContratoTemplateRepository;
use App\Repository\EmpresaRepository;
use App\Repository\ModuloPerRepository;
use App\Service\ContratoTemplateRenderer;
use App\Service\DocxToHtmlConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }
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
            $entityManager = $this->entityManager;
            $entityManager->persist($contratoTemplate);
            $entityManager->flush();

            $this->addFlash('success', 'Plantilla creada correctamente.');

            return $this->redirectToRoute('contrato_template_index');
        }

        return $this->render('contrato_template/new.html.twig', [
            'contratoTemplate' => $contratoTemplate,
            'form' => $form->createView(),
            'variables' => ContratoTemplateRenderer::catalogo(),
            'columnas' => ContratoTemplateRenderer::columnasTabla(),
        ]);
    }

    /**
     * Previsualiza en PDF el contenido que está en el editor (aunque no esté
     * guardado) con datos inventados. El contenido se trata como texto: solo
     * se hace strtr() de variables, igual que en el contrato real.
     */
    #[Route("/preview", name: "contrato_template_preview", methods: ["POST"])]
    public function preview(Request $request, ContratoTemplateRenderer $renderer): Response
    {
        if (!$this->isGranted('create', 'contrato_template') && !$this->isGranted('edit', 'contrato_template')) {
            throw $this->createAccessDeniedException();
        }
        if (!$this->isCsrfTokenValid('contrato_template_preview', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token inválido.');
        }

        $html = $renderer->renderEjemplo((string) $request->request->get('contenido', ''));

        $options = new Options();
        $options->set('defaultFont', 'helvetica');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="previsualizacion-contrato.pdf"',
        ]);
    }

    /**
     * Convierte un .docx subido en HTML para cargarlo en el editor. No guarda
     * nada: el usuario revisa el resultado y recién ahí guarda la plantilla.
     */
    #[Route("/importar-docx", name: "contrato_template_importar_docx", methods: ["POST"])]
    public function importarDocx(Request $request, DocxToHtmlConverter $conversor): JsonResponse
    {
        if (!$this->isGranted('create', 'contrato_template') && !$this->isGranted('edit', 'contrato_template')) {
            throw $this->createAccessDeniedException();
        }
        if (!$this->isCsrfTokenValid('contrato_template_importar', $request->request->get('_token'))) {
            return new JsonResponse(['detail' => 'Token inválido, recargue la página.'], 403);
        }

        $archivo = $request->files->get('docx');
        if (!$archivo instanceof UploadedFile || !$archivo->isValid()) {
            return new JsonResponse(['detail' => 'Seleccione un archivo .docx.'], 422);
        }
        if (strtolower((string) $archivo->getClientOriginalExtension()) !== 'docx') {
            return new JsonResponse(['detail' => 'El archivo debe ser un .docx (Word 2007 o superior).'], 422);
        }

        try {
            $resultado = $conversor->convertir($archivo->getPathname());
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['detail' => $e->getMessage()], 422);
        }
        if ($resultado['html'] === '') {
            return new JsonResponse(['detail' => 'El documento no tiene contenido.'], 422);
        }

        return new JsonResponse($resultado);
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

            $this->entityManager->flush();

            $this->addFlash('success', 'Plantilla actualizada correctamente.');

            return $this->redirectToRoute('contrato_template_index');
        }

        return $this->render('contrato_template/edit.html.twig', [
            'contratoTemplate' => $contratoTemplate,
            'form' => $form->createView(),
            'variables' => ContratoTemplateRenderer::catalogo(),
            'columnas' => ContratoTemplateRenderer::columnasTabla(),
        ]);
    }

    #[Route("/{id}", name: "contrato_template_delete", methods: ["DELETE"])]
    public function delete(Request $request, ContratoTemplate $contratoTemplate): Response
    {
        $this->denyAccessUnlessGranted('full', 'contrato_template');
        $this->verificarTenant($contratoTemplate);

        if ($this->isCsrfTokenValid('delete'.$contratoTemplate->getId(), $request->request->get('_token'))) {
            $entityManager = $this->entityManager;
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
