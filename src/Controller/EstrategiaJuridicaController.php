<?php

namespace App\Controller;

use App\Entity\EstrategiaJuridica;
use App\Entity\Materia;
use App\Form\EstrategiaJuridicaType;
use App\Repository\EstrategiaJuridicaRepository;
use App\Repository\MateriaEstrategiaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/estrategia_juridica")
 */
class EstrategiaJuridicaController extends AbstractController
{
    /**
     * @Route("/", name="estrategia_juridica_index", methods={"GET"})
     */
    public function index(EstrategiaJuridicaRepository $estrategiaJuridicaRepository): Response
    {
        return $this->render('estrategia_juridica/index.html.twig', [
            'estrategia_juridicas' => $estrategiaJuridicaRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="estrategia_juridica_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $estrategiaJuridica = new EstrategiaJuridica();
        $form = $this->createForm(EstrategiaJuridicaType::class, $estrategiaJuridica);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($estrategiaJuridica);
            $entityManager->flush();

            return $this->redirectToRoute('estrategia_juridica_index');
        }

        return $this->render('estrategia_juridica/new.html.twig', [
            'estrategia_juridica' => $estrategiaJuridica,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="estrategia_juridica_show", methods={"GET"})
     */
    public function show(EstrategiaJuridica $estrategiaJuridica): Response
    {
        return $this->render('estrategia_juridica/show.html.twig', [
            'estrategia_juridica' => $estrategiaJuridica,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="estrategia_juridica_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, EstrategiaJuridica $estrategiaJuridica): Response
    {
        $form = $this->createForm(EstrategiaJuridicaType::class, $estrategiaJuridica);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('estrategia_juridica_index');
        }

        return $this->render('estrategia_juridica/edit.html.twig', [
            'estrategia_juridica' => $estrategiaJuridica,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/combo", name="estrategia_juridica_combo", methods={"GET","POST"})
     */
    public function combo(Materia $materia,MateriaEstrategiaRepository $materiaEstrategiaRepository): Response
    {

        
        return $this->render('estrategia_juridica/combo.html.twig', [
            'materia_estrategias' => $materiaEstrategiaRepository->findBy(['materia'=>$materia->getId(),'estado'=>1]) 
            
        ]);
    }

    /**
     * @Route("/{id}", name="estrategia_juridica_delete", methods={"DELETE"})
     */
    public function delete(Request $request, EstrategiaJuridica $estrategiaJuridica): Response
    {
        if ($this->isCsrfTokenValid('delete'.$estrategiaJuridica->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($estrategiaJuridica);
            $entityManager->flush();
        }

        return $this->redirectToRoute('estrategia_juridica_index');
    }
}
