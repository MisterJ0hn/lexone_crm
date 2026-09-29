<?php

namespace App\Controller;

use App\Entity\Materia;
use App\Entity\MateriaEstrategia;
use App\Form\MateriaEstrategiaType;
use App\Repository\MateriaEstrategiaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/materia_estrategia")
 */
class MateriaEstrategiaController extends AbstractController
{
    /**
     * @Route("/", name="materia_estrategia_index", methods={"GET"})
     */
    public function index(MateriaEstrategiaRepository $materiaEstrategiaRepository): Response
    {
        return $this->render('materia_estrategia/index.html.twig', [
            'materia_estrategias' => $materiaEstrategiaRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}/new", name="materia_estrategia_new", methods={"GET","POST"})
     */
    public function new(Request $request,Materia $materia,MateriaEstrategiaRepository $materiaEstrategiaRepository): Response
    {
        $materiaEstrategium = new MateriaEstrategia();
        $materiaEstrategium->setMateria($materia);
        $materiaEstrategium->setEstado(1);
        $form = $this->createForm(MateriaEstrategiaType::class, $materiaEstrategium);
        $form->handleRequest($request);
        $error_toast="";
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($materiaEstrategium);
            $entityManager->flush();

            $error_toast="Toast.fire({
                icon: 'success',
                title: 'Registro grabado con exito'
            })";
            //return $this->redirectToRoute('materia_estrategia_index');
        }

        return $this->render('materia_estrategia/new.html.twig', [
            'materia_estrategium' => $materiaEstrategium,
            'form' => $form->createView(),
            'error_toast'=>$error_toast,
            'materia_estrategias' => $materiaEstrategiaRepository->findBy(['materia'=>$materia->getId(),'estado'=>1]),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="materia_estrategia_delete", methods={"GET","POST"})
     */
    public function delete(Request $request, MateriaEstrategia $materiaEstrategium): Response
    {
        
            $entityManager = $this->getDoctrine()->getManager();
            $materiaEstrategium->setEstado(0);
            $entityManager->persist($materiaEstrategium);
            $entityManager->flush();

        return $this->redirectToRoute('materia_estrategia_new',['id'=>$materiaEstrategium->getMateria()->getId()]);
    }

    /**
     * @Route("/{id}", name="materia_estrategia_show", methods={"GET"})
     */
    public function show(MateriaEstrategia $materiaEstrategium): Response
    {
        return $this->render('materia_estrategia/show.html.twig', [
            'materia_estrategium' => $materiaEstrategium,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="materia_estrategia_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, MateriaEstrategia $materiaEstrategium): Response
    {
        $form = $this->createForm(MateriaEstrategiaType::class, $materiaEstrategium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('materia_estrategia_index');
        }

        return $this->render('materia_estrategia/edit.html.twig', [
            'materia_estrategium' => $materiaEstrategium,
            'form' => $form->createView(),
        ]);
    }

    
}
