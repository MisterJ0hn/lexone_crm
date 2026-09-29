<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Configuracion;
use App\Form\ConfiguracionType;
use App\Repository\ConfiguracionRepository;
use App\Repository\ModuloPerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/configuracion")]
class ConfiguracionController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }
    #[Route("/", name: "configuracion_index", methods: ["GET","POST"])]
    public function index(Request $request,ConfiguracionRepository $configuracionRepository,ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('edit','configuracion');
        $user=$this->getUser();
        
        $pagina=$moduloPerRepository->findOneByName('configuracion',$user->getEmpresaActual());
        $configuracion=$configuracionRepository->find(1);
        $form = $this->createForm(ConfiguracionType::class, $configuracion);
       
        $form->add('valorMulta');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('configuracion_index');
        }

        return $this->render('configuracion/edit.html.twig', [
            'configuracion' => $configuracion,
            'pagina'=>$pagina->getNombre(),
            'form' => $form->createView(),
        ]);
    }

    #[Route("/new", name: "configuracion_new", methods: ["GET","POST"])]
    public function new(Request $request): Response
    {
        $configuracion = new Configuracion();
        $form = $this->createForm(ConfiguracionType::class, $configuracion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;
            $entityManager->persist($configuracion);
            $entityManager->flush();

            return $this->redirectToRoute('configuracion_index');
        }

        return $this->render('configuracion/new.html.twig', [
            'configuracion' => $configuracion,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name: "configuracion_show", methods: ["GET"])]
    public function show(Configuracion $configuracion): Response
    {
        return $this->render('configuracion/show.html.twig', [
            'configuracion' => $configuracion,
        ]);
    }

    #[Route("/{id}/edit", name: "configuracion_edit", methods: ["GET","POST"])]
    public function edit(Request $request, Configuracion $configuracion,ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('edit','configuracion');
        $user=$this->getUser();
        
        $pagina=$moduloPerRepository->findOneByName('configuracion',$user->getEmpresaActual());
        $form = $this->createForm(ConfiguracionType::class, $configuracion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('configuracion_index');
        }

        return $this->render('configuracion/edit.html.twig', [
            'configuracion' => $configuracion,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name: "configuracion_delete", methods: ["DELETE"])]
    public function delete(Request $request, Configuracion $configuracion): Response
    {
        if ($this->isCsrfTokenValid('delete'.$configuracion->getId(), $request->request->get('_token'))) {
            $entityManager = $this->entityManager;
            $entityManager->remove($configuracion);
            $entityManager->flush();
        }

        return $this->redirectToRoute('configuracion_index');
    }
}
