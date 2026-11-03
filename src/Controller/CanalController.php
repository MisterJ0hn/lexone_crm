<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Canal;
use App\Entity\Empresa;
use App\Form\CanalType;
use App\Repository\CanalRepository;
use App\Repository\EmpresaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/canal")]
class CanalController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }
    /**
     * Alta rápida de un canal desde el panel agendador: lo crea en la empresa del usuario (o reutiliza uno activo
     * con el mismo nombre) y devuelve {id, nombre} para dejarlo seleccionado en el combo.
     */
    #[Route("/rapido", name: "canal_rapido", methods: ["POST"])]
    public function rapido(Request $request, CanalRepository $canalRepository, EmpresaRepository $empresaRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('create', 'panel_agendador');
        if (!$this->isCsrfTokenValid('canal_rapido', (string) $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['detail' => 'Token inválido, recargue la página.'], 400);
        }
        $nombre = trim((string) $request->request->get('nombre'));
        if ($nombre === '' || mb_strlen($nombre) > 100) {
            return new JsonResponse(['detail' => 'Ingrese el nombre del canal (máximo 100 caracteres).'], 422);
        }

        $user = $this->getUser();
        $empresa = $empresaRepository->find($user->getEmpresaActual());
        foreach ($canalRepository->findBy(['empresa' => $empresa->getId(), 'estado' => true]) as $existente) {
            if (mb_strtolower($existente->getNombre()) === mb_strtolower($nombre)) {
                return new JsonResponse(['id' => $existente->getId(), 'nombre' => $existente->getNombre()]);
            }
        }

        $canal = new Canal();
        $canal->setEmpresa($empresa);
        $canal->setNombre($nombre);
        $canal->setEstado(true);
        $canal->setUsuarioRegistro($user);
        $this->entityManager->persist($canal);
        $this->entityManager->flush();

        return new JsonResponse(['id' => $canal->getId(), 'nombre' => $canal->getNombre()]);
    }

    #[Route("/", name: "canal_index", methods: ["GET","POST"])]
    public function index(Request $request,CanalRepository $canalRepository,EmpresaRepository $empresaRepository): Response
    {
        $user=$this->getUser();

        $canal = new Canal();
        $canal->setEmpresa($empresaRepository->find($user->getEmpresaActual()));
        $canal->setEstado(1);
        $canal->setUsuarioRegistro($user);
        $form = $this->createForm(CanalType::class, $canal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;
            $entityManager->persist($canal);
            $entityManager->flush();

            //return $this->redirectToRoute('canal_index');
        }

        return $this->render('canal/index.html.twig', [
            'canals' => $canalRepository->findBy(['empresa'=>$user->getEmpresaActual(),'estado'=>1]),
            'form' => $form->createView(),
            'pagina'=>'Canal'
        ]);
    }

    #[Route("/new", name: "canal_new", methods: ["GET","POST"])]
    public function new(Request $request): Response
    {
        $canal = new Canal();
        $form = $this->createForm(CanalType::class, $canal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;
            $entityManager->persist($canal);
            $entityManager->flush();

            return $this->redirectToRoute('canal_index');
        }

        return $this->render('canal/new.html.twig', [
            'canal' => $canal,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name: "canal_show", methods: ["GET"])]
    public function show(Canal $canal): Response
    {
        return $this->render('canal/show.html.twig', [
            'canal' => $canal,
        ]);
    }

    #[Route("/{id}/edit", name: "canal_edit", methods: ["GET","POST"])]
    public function edit(Request $request, Canal $canal): Response
    {
        $form = $this->createForm(CanalType::class, $canal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('canal_index');
        }

        return $this->render('canal/edit.html.twig', [
            'canal' => $canal,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name: "canal_delete", methods: ["DELETE"])]
    public function delete(Request $request, Canal $canal): Response
    {
        if ($this->isCsrfTokenValid('delete'.$canal->getId(), $request->request->get('_token'))) {
            $entityManager = $this->entityManager;
            $entityManager->remove($canal);
            $entityManager->flush();
        }

        return $this->redirectToRoute('canal_index');
    }
}
