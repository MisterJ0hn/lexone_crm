<?php

namespace App\Controller;

use App\Entity\Canal;
use App\Entity\Empresa;
use App\Form\CanalType;
use App\Repository\CanalRepository;
use App\Repository\EmpresaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/canal")
 */
class CanalController extends AbstractController
{
    /**
     * @Route("/", name="canal_index", methods={"GET","POST"})
     */
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
            $entityManager = $this->getDoctrine()->getManager();
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

    /**
     * @Route("/new", name="canal_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $canal = new Canal();
        $form = $this->createForm(CanalType::class, $canal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($canal);
            $entityManager->flush();

            return $this->redirectToRoute('canal_index');
        }

        return $this->render('canal/new.html.twig', [
            'canal' => $canal,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="canal_show", methods={"GET"})
     */
    public function show(Canal $canal): Response
    {
        return $this->render('canal/show.html.twig', [
            'canal' => $canal,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="canal_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Canal $canal): Response
    {
        $form = $this->createForm(CanalType::class, $canal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('canal_index');
        }

        return $this->render('canal/edit.html.twig', [
            'canal' => $canal,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="canal_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Canal $canal): Response
    {
        if ($this->isCsrfTokenValid('delete'.$canal->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($canal);
            $entityManager->flush();
        }

        return $this->redirectToRoute('canal_index');
    }
}
