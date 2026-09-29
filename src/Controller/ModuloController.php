<?php

namespace App\Controller;

use App\Entity\Modulo;
use App\Entity\ModuloPer;
use App\Form\ModuloPerType;
use App\Repository\ModuloRepository;
use App\Repository\EmpresaRepository;
use App\Repository\ModuloPerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Knp\Component\Pager\PaginatorInterface;

/**
 * @Route("/modulo")
 */
class ModuloController extends AbstractController
{
    /**
     * @Route("/", name="modulo_index", methods={"GET"})
     */
    public function index(ModuloPerRepository $moduloPerRepository,PaginatorInterface $paginator,Request $request): Response
    {
        $this->denyAccessUnlessGranted('view','modulo');
        
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('modulo',1);
        $query=$moduloPerRepository->findBy(['empresa'=>1]);
        $modulosPer=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/,
            array('defaultSortFieldName' => 'nombre', 'defaultSortDirection' => 'asc'));
        return $this->render('modulo/index.html.twig', [
            'modulos' =>$modulosPer ,
            'pagina'=>$pagina->getNombre(),
        ]);
    }

    /**
     * @Route("/new", name="modulo_new", methods={"GET","POST"})
     */
    public function new(Request $request,ModuloRepository $moduloRepository, ModuloPerRepository $moduloPerRepository, EmpresaRepository $empresaRepository): Response
    {
        $this->denyAccessUnlessGranted('create','modulo');
        $user=$this->getUser();

        $modulos=$moduloRepository->findAll();

        foreach($modulos as $modulo){
            $moduloPer=$moduloPerRepository->findOneBy(['empresa'=>1,'modulo'=>$modulo->getId()]);
            if(!$moduloPer){
                $moduloNew = new ModuloPer();
                $empresa=$empresaRepository->find(1);
                $moduloNew->setEmpresa($empresa);
                $moduloNew->setModulo($modulo);
                $moduloNew->setNombre($modulo->getNombreAlt());
                $moduloNew->setDescripcion($modulo->getDescripcion());

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($moduloNew);
                $entityManager->flush();

            }
        }

        return $this->redirectToRoute('modulo_index');
       /* $modulo = new Modulo();
        
        
        
        
        $form = $this->createForm(ModuloType::class, $modulo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($modulo);
            $entityManager->flush();

            return $this->redirectToRoute('modulo_index');
        }*/

       /* return $this->render('modulo/new.html.twig', [
            'modulo' => $modulo,
            'form' => $form->createView(),
        ]);*/
    }

    
    /**
     * @Route("/{id}/edit", name="modulo_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ModuloPer $modulo): Response
    {
        $this->denyAccessUnlessGranted('edit','modulo');
        
        $form = $this->createForm(ModuloPerType::class, $modulo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('modulo_index');
        }

        return $this->render('modulo/edit.html.twig', [
            'modulo' => $modulo,
            'form' => $form->createView(),
        ]);
    }

    
}
