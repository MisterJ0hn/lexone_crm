<?php

namespace App\Controller;

use App\Entity\UsuarioTipo;
use App\Entity\ModuloPer;
use App\Form\UsuarioTipoType;
use App\Repository\UsuarioTipoRepository;
use App\Repository\AgendaStatusRepository;
use App\Repository\EmpresaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/usuario_tipo")
 */
class UsuarioTipoController extends AbstractController
{
    /**
     * @Route("/", name="usuario_tipo_index", methods={"GET"})
     */
    public function index(UsuarioTipoRepository $usuarioTipoRepository): Response
    {
        $this->denyAccessUnlessGranted('view','usuario_tipo');
        $user=$this->getUser();
        $pagina=$this->getDoctrine()->getRepository(ModuloPer::class)->findOneByName('usuario_tipo',$user->getEmpresaActual());
        
        if($user->getUsuarioTipo()->getId()==8){
            $listado="";
        }else{
            $listado="8";
        }
        return $this->render('usuario_tipo/index.html.twig', [
            'usuario_tipos' => $usuarioTipoRepository->findByEmpresa($user->getEmpresaActual(),$listado),
            'pagina'=>$pagina->getNombre(),
        ]);
    }

    /**
     * @Route("/new", name="usuario_tipo_new", methods={"GET","POST"})
     */
    public function new(Request $request,EmpresaRepository $empresaRepository,AgendaStatusRepository $agendaStatusRepository): Response
    {
        $user=$this->getUser();
        $this->denyAccessUnlessGranted('create','usuario_tipo');
        $pagina=$this->getDoctrine()->getRepository(ModuloPer::class)->findOneByName('usuario_tipo',$user->getEmpresaActual());
        
        $usuarioTipo = new UsuarioTipo();
        $empresa=$empresaRepository->find($user->getEmpresaActual());
        $usuarioTipo->setEmpresa($empresa);
        $form = $this->createForm(UsuarioTipoType::class, $usuarioTipo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $usuarioTipo->setNombreInterno($usuarioTipo->getNombre());
            $entityManager->persist($usuarioTipo);
            $entityManager->flush();

            return $this->redirectToRoute('usuario_tipo_index');
        }

        return $this->render('usuario_tipo/new.html.twig', [
            'usuario_tipo' => $usuarioTipo,
            'form' => $form->createView(),
            'pagina'=>$pagina->getNombre()." / Agregar",
        ]);
    }

    /**
     * @Route("/{id}", name="usuario_tipo_show", methods={"GET"})
     */
    public function show(UsuarioTipo $usuarioTipo): Response
    {
        $this->denyAccessUnlessGranted('view','usuario_tipo');
        return $this->render('usuario_tipo/show.html.twig', [
            'usuario_tipo' => $usuarioTipo,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="usuario_tipo_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, UsuarioTipo $usuarioTipo,AgendaStatusRepository $agendaStatusRepository): Response
    {
        $this->denyAccessUnlessGranted('edit','usuario_tipo');
        $user=$this->getUser();
        $pagina=$this->getDoctrine()->getRepository(ModuloPer::class)->findOneByName('usuario_tipo',$user->getEmpresaActual());
        
        $form = $this->createForm(UsuarioTipoType::class, $usuarioTipo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $statues=$request->request->get('status');
            $usuarioTipo->setStatues($statues);
            $this->getDoctrine()->getManager()->flush();
            
           // var_dump($statues);
            return $this->redirectToRoute('usuario_tipo_index');
        }

        return $this->render('usuario_tipo/edit.html.twig', [
            'usuario_tipo' => $usuarioTipo,
            'form' => $form->createView(),
            'pagina'=>$pagina->getNombre()." / Editar",
            'statues'=>$agendaStatusRepository->findAll(),
        ]);
    }

    
    /**
     * @Route("/{id}", name="usuario_tipo_delete", methods={"DELETE"})
     */
    public function delete(Request $request, UsuarioTipo $usuarioTipo): Response
    {
        $this->denyAccessUnlessGranted('full','usuario_tipo');
        if ($this->isCsrfTokenValid('delete'.$usuarioTipo->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($usuarioTipo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('usuario_tipo_index');
    }
}
