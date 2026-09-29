<?php

namespace App\Controller;

use App\Entity\MenuCabezera;
use App\Form\MenuCabezeraType;
use App\Repository\EmpresaRepository;
use App\Repository\MenuCabezeraRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/menu_cabezera")
 */
class MenuCabezeraController extends AbstractController
{
    /**
     * @Route("/", name="menu_cabezera_index", methods={"GET"})
     */
    public function index(MenuCabezeraRepository $menuCabezeraRepository): Response
    {
        $this->denyAccessUnlessGranted('view','menu_cabezera');
        $user=$this->getUser();

        return $this->render('menu_cabezera/index.html.twig', [
            'menu_cabezeras' => $menuCabezeraRepository->findAll(),
            'pagina'=>'Men&uacute;',
        ]);
    }

    /**
     * @Route("/new", name="menu_cabezera_new", methods={"GET","POST"})
     */
    public function new(Request $request,EmpresaRepository $empresaRepository): Response
    {
        $this->denyAccessUnlessGranted('create','menu_cabezera');
        $user=$this->getUser();
        $empresa=$empresaRepository->find($user->getEmpresaActual());
        $menuCabezera = new MenuCabezera();
        $form = $this->createForm(MenuCabezeraType::class, $menuCabezera);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($menuCabezera);
            $entityManager->flush();

            return $this->redirectToRoute('menu_cabezera_index');
        }

        return $this->render('menu_cabezera/new.html.twig', [
            'menu_cabezera' => $menuCabezera,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="menu_cabezera_show", methods={"GET"})
     */
    public function show(MenuCabezera $menuCabezera): Response
    {
        $this->denyAccessUnlessGranted('view','menu_cabezera');
        return $this->render('menu_cabezera/show.html.twig', [
            'menu_cabezera' => $menuCabezera,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="menu_cabezera_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, MenuCabezera $menuCabezera): Response
    {
        $this->denyAccessUnlessGranted('edit','menu_cabezera');
        $form = $this->createForm(MenuCabezeraType::class, $menuCabezera);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('menu_cabezera_index');
        }

        return $this->render('menu_cabezera/edit.html.twig', [
            'menu_cabezera' => $menuCabezera,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="menu_cabezera_delete", methods={"DELETE"})
     */
    public function delete(Request $request, MenuCabezera $menuCabezera): Response
    {
        $this->denyAccessUnlessGranted('full','menu_cabezera');
        if ($this->isCsrfTokenValid('delete'.$menuCabezera->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($menuCabezera->getMenus() as $menu) {
            
                if($menu->getDependeDe()){
                    $entityManager->remove($menu);
                    $entityManager->flush();
                }
            }
            foreach ($menuCabezera->getMenus() as $menu) {
            
                $entityManager->remove($menu);
                $entityManager->flush();
            }
            
            foreach($menuCabezera->getUsuarioTipos() as $usuarioTipo){
                $usuarioTipo->setMenuCabezera(null);
                $entityManager->persist($usuarioTipo);
                $entityManager->flush();
            }
            $entityManager->remove($menuCabezera);
            $entityManager->flush();
        }

        return $this->redirectToRoute('menu_cabezera_index');
    }
}
