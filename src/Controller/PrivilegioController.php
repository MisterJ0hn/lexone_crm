<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Privilegio;
use App\Repository\UsuarioRepository;
use App\Repository\AccionRepository;
use App\Entity\PrivilegioTipousuario;
use App\Entity\Usuario;
use App\Form\PrivilegioType;
use App\Repository\PrivilegioRepository;
use App\Repository\PrivilegioTipousuarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/privilegio")]
class PrivilegioController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }
    #[Route("/{id}", name: "privilegio_index", methods: ["GET"])]
    public function index(Usuario $usuario,
                        PrivilegioRepository $privilegioRepository,
                        AccionRepository $accionRepository): Response
    {

        $this->denyAccessUnlessGranted('view','privilegio');
        $user=$this->getUser();
        $acciones=$accionRepository->findBy(['empresa'=>1]);
        return $this->render('privilegio/index.html.twig', [
            'privilegios' => $privilegioRepository->findBy(['usuario'=>$usuario->getId()]),
            'acciones'=>$acciones,
            'usuario'=>$usuario,

        ]);
    }

    #[Route("/{id}/new", name: "privilegio_new", methods: ["GET","POST"])]
    public function new(Request $request,
                        Usuario $usuario,
                        PrivilegioTipousuarioRepository $privilegioTipousuarioRepository,
                        PrivilegioRepository $privilegioRepository): Response
    {
        $this->denyAccessUnlessGranted('create','privilegio');
        $user=$this->getUser();
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

        return $this->redirectToRoute('privilegio_index',['id'=>$usuario->getId()]);
        
    }

    #[Route("/{id}", name: "privilegio_show", methods: ["GET"])]
    public function show(Privilegio $privilegio): Response
    {

        $this->denyAccessUnlessGranted('view','privilegio');

        return $this->render('privilegio/show.html.twig', [
            'privilegio' => $privilegio,
        ]);
    }

    #[Route("/{id}/edit", name: "privilegio_edit", methods: ["GET","POST"])]
    public function edit(Request $request, Privilegio $privilegio,AccionRepository $accionRepository): Response
    {
        $this->denyAccessUnlessGranted('edit','privilegio');

        $accion=$accionRepository->find($request->request->get('accion'));
        $privilegio->setAccion($accion);

        $entityManager = $this->entityManager;
        $entityManager->persist($privilegio);
        $entityManager->flush();

        return $this->render('privilegio/ok.html.twig');
    }
    #[Route("/{id}/regenerar", name: "privilegio_regenerar", methods: ["GET","POST"])]
    public function regenerar(Request $request,
            Usuario $usuario,
            PrivilegioTipousuarioRepository $privilegioTipousuarioRepository,
            PrivilegioRepository $privilegioRepository): Response
    {
        $this->denyAccessUnlessGranted('edit','privilegio');
        $user=$this->getUser();

       
        
        $privilegios=$usuario->getPrivilegios();
        foreach($privilegios as $privilegio){
           
            $entityManager = $this->entityManager;
            $entityManager->remove($privilegio);
            $entityManager->flush();

        }
        $privilegioTipousuarios=$privilegioTipousuarioRepository->findByEmpresa(1,$usuario->getUsuarioTipo()->getId());

      
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
        return $this->redirectToRoute('privilegio_index',['id'=>$usuario->getId()]);
    }

    #[Route("/{id}", name: "privilegio_delete", methods: ["DELETE"])]
    public function delete(Request $request, Privilegio $privilegio): Response
    {
        $this->denyAccessUnlessGranted('full','privilegio');
        if ($this->isCsrfTokenValid('delete'.$privilegio->getId(), $request->request->get('_token'))) {
            $entityManager = $this->entityManager;
            $entityManager->remove($privilegio);
            $entityManager->flush();
        }

        return $this->redirectToRoute('privilegio_index');
    }
}
