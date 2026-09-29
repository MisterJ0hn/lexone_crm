<?php

namespace App\Controller;

use App\Entity\Vencimiento;
use App\Form\VencimientoType;
use App\Repository\EmpresaRepository;
use App\Repository\VencimientoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/vencimiento")
 */
class VencimientoController extends AbstractController
{
    /**
     * @Route("/", name="vencimiento_index", methods={"GET"})
     */
    public function index(VencimientoRepository $vencimientoRepository): Response
    {
        $user=$this->getUser();
        return $this->render('vencimiento/index.html.twig', [
            'vencimientos' => $vencimientoRepository->findBy(['empresa'=>$user->getEmpresaActual()]),
        ]);
    }

    /**
     * @Route("/new", name="vencimiento_new", methods={"GET","POST"})
     */
    public function new(Request $request,EmpresaRepository $empresaRepository): Response
    {
        $user=$this->getUser();
        $vencimiento = new Vencimiento();
        $vencimiento->setEmpresa($empresaRepository->find($user->getEmpresaActual()));
        $form = $this->createForm(VencimientoType::class, $vencimiento);
         $form->add('color', ChoiceType::class, [
            'choices' => [
                'Warning' => 'Warning',
                'Success' => 'Success',
                'danger' => 'danger',
            ],
            'attr' => ['class' => 'form-control'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($vencimiento);
            $entityManager->flush();

            return $this->redirectToRoute('vencimiento_index');
        }

        return $this->render('vencimiento/new.html.twig', [
            'vencimiento' => $vencimiento,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="vencimiento_show", methods={"GET"})
     */
    public function show(Vencimiento $vencimiento): Response
    {
        return $this->render('vencimiento/show.html.twig', [
            'vencimiento' => $vencimiento,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="vencimiento_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Vencimiento $vencimiento): Response
    {
        $user=$this->getUser();
        if($vencimiento->getEmpresa()->getId() !== $user->getEmpresaActual()){
            throw $this->createAccessDeniedException("No tienes permiso para editar este vencimiento");
        }
        $form = $this->createForm(VencimientoType::class, $vencimiento);
        $form->add('color', ChoiceType::class, [
            'choices' => [
                'Amarillo' => 'warning',
                'Verde' => 'success',
                'Rojo' => 'danger',
            ],
            'attr' => ['class' => 'form-control'],
        ]);
        $form->add('nombre');
        $form->add('montoMax');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('vencimiento_index');
        }

        return $this->render('vencimiento/edit.html.twig', [
            'vencimiento' => $vencimiento,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="vencimiento_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Vencimiento $vencimiento): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vencimiento->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($vencimiento);
            $entityManager->flush();
        }

        return $this->redirectToRoute('vencimiento_index');
    }
}
