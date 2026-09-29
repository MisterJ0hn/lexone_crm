<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\PrivilegioTipousuario;
use App\Entity\Privilegio;
use App\Entity\UsuarioTipo;
use App\Repository\UsuarioTipoRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\AccionRepository;
use App\Form\PrivilegioTipousuarioType;
use App\Repository\PrivilegioRepository;
use App\Repository\PrivilegioTipousuarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/privilegio_tipousuario")]
class PrivilegioTipousuarioController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }
    
    #[Route("/{id}", name: "privilegio_tipousuario_index", methods: ["GET"])]
    public function index(UsuarioTipo $usuarioTipo, 
                        PrivilegioTipousuarioRepository $privilegioTipousuarioRepository,
                        ModuloPerRepository $moduloRepository,
                        AccionRepository $accionRepository): Response
    {
        $this->denyAccessUnlessGranted('view','privilegio_tipousuario');
        $user=$this->getUser();
        $modulos= $moduloRepository->findBy(['empresa'=>1]);
        $acciones=$accionRepository->findBy(['empresa'=>1]);
        return $this->render('privilegio_tipousuario/index.html.twig', [
            'privilegio_tipousuarios' => $privilegioTipousuarioRepository->findByEmpresa(1,$usuarioTipo->getId()),
            'modulos'=>$modulos,
            'usuarioTipo'=>$usuarioTipo,
            'acciones'=>$acciones,
        ]);
    }

    
    #[Route("/{id}/new", name: "privilegio_tipousuario_new", methods: ["GET","POST"])]
    public function new(Request $request,
                        UsuarioTipo $usuarioTipo,
                        ModuloPerRepository $moduloPerRepository,
                        AccionRepository $accionRepository,
                        PrivilegioTipousuarioRepository $privilegioTipousuarioRepository,
                        PrivilegioRepository $privilegioRepository): Response
    {
        $this->denyAccessUnlessGranted('create','privilegio_tipousuario');

        $user=$this->getUser();

        $privilegioTipousuario = new PrivilegioTipousuario();
        $selModulo=$request->request->get('selModulo');
        $modulo=$moduloPerRepository->find($selModulo);
        $accion=$accionRepository->findOneBy(['empresa'=>1,'accion'=>"view"]);
        $privilegioTipousuario->setModuloPer($modulo);
        $privilegioTipousuario->setTipousuario($usuarioTipo);
        $privilegioTipousuario->setAccion($accion);

        $entityManager = $this->entityManager;
        $entityManager->persist($privilegioTipousuario);
        $entityManager->flush();

        foreach($usuarioTipo->getUsuarios() as $usuario){
            $privilegioTipousuarios=$privilegioTipousuarioRepository->findBy(['tipousuario'=>$usuario->getUsuarioTipo()->getId()]);

            foreach($privilegioTipousuarios as $privilegioTipousuario){
                $privilegio=$privilegioRepository->findBy(["moduloPer"=>$privilegioTipousuario->getModuloPer()->getId(),"usuario"=>$usuario->getId()]);
                if(!$privilegio){
                    $privilegioNew=new Privilegio();
                    $privilegioNew->setUsuario($usuario);
                    $privilegioNew->setModuloPer($privilegioTipousuario->getModuloPer());
                    $privilegioNew->setAccion($privilegioTipousuario->getAccion());

                    $entityManager = $this->entityManager;
                    $entityManager->persist($privilegioNew);
                    $entityManager->flush();

                }
            }
        }
       return $this->redirectToRoute('privilegio_tipousuario_index',array(
                'id'=>$usuarioTipo->getId(),
       ));
        /*$form = $this->createForm(PrivilegioTipousuarioType::class, $privilegioTipousuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;
            $entityManager->persist($privilegioTipousuario);
            $entityManager->flush();

            
        }

        return $this->render('privilegio_tipousuario/new.html.twig', [
            'privilegio_tipousuario' => $privilegioTipousuario,
            'form' => $form->createView(),
        ]);*/
    }

    


    #[Route("/{id}/edit", name: "privilegio_tipousuario_edit", methods: ["GET","POST"])]
    public function edit(Request $request,PrivilegioTipousuario $privilegioTipousuario,AccionRepository $accionRepository,PrivilegioTipousuarioRepository $privilegioTipousuarioRepository,PrivilegioRepository $privilegioRepository): Response
    {
        $this->denyAccessUnlessGranted('edit','privilegio_tipousuario');
        $accion=$accionRepository->find($request->request->get('accion'));

        $privilegioTipousuario->setAccion($accion);

        
        $entityManager = $this->entityManager;
        $entityManager->persist($privilegioTipousuario);
        $entityManager->flush();
        $tipousuario=$privilegioTipousuario->getTipousuario();
        foreach($tipousuario->getUsuarios() as $usuario){
            //echo $usuario->getId();
            $privilegioTipousuarios=$privilegioTipousuarioRepository->findBy(['tipousuario'=>$usuario->getUsuarioTipo()->getId()]);

            foreach($privilegioTipousuarios as $privilegioTipousuario){
                $privilegio=$privilegioRepository->findOneBy(["moduloPer"=>$privilegioTipousuario->getModuloPer()->getId(),"usuario"=>$usuario->getId()]);
                
                if($privilegio){
                    echo  $privilegio->getId();
                   // $privilegio->setUsuario($usuario);
                    //$privilegio->setModuloPer($privilegioTipousuario->getModuloPer());
                    $privilegio->setAccion($privilegioTipousuario->getAccion());

                    //$entityManager = $this->entityManager;
                    $entityManager->persist($privilegio);
                    $entityManager->flush();

                }
            }
        }

        return $this->render('privilegio_tipousuario/ok.html.twig');
    }

    #[Route("/{id}", name: "privilegio_tipousuario_delete", methods: ["DELETE"])]
    public function delete(Request $request, PrivilegioTipousuario $privilegioTipousuario): Response
    {
        $this->denyAccessUnlessGranted('full','privilegio_tipousuario');
        if ($this->isCsrfTokenValid('delete'.$privilegioTipousuario->getId(), $request->request->get('_token'))) {
            $entityManager = $this->entityManager;
            $entityManager->remove($privilegioTipousuario);
            $entityManager->flush();
        }

        return $this->redirectToRoute('privilegio_tipousuario_index');
    }
}
