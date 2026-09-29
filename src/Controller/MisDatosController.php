<?php

namespace App\Controller;

use App\Repository\UsuarioRepository;
use App\Repository\ModuloPerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/mis_datos")
 */

class MisDatosController extends AbstractController
{
    /**
     * @Route("/", name="mis_datos_index", methods={"GET"})
     */
    public function index(
    ModuloPerRepository $moduloPerRepository)
    {
        $this->denyAccessUnlessGranted('view','mis_datos');
        $u=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('mis_datos',$u->getEmpresaActual());
        return $this->render('mis_datos/index.html.twig', [
            'misdatos' => $u,
            'pagina'=>$pagina->getNombre(),
            ]);
    }
    /**
     * @Route("/modificar", name="mis_datos_modificar", methods={"GET","POST"})
     */
    public function modificar(UsuarioRepository $usuarioRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit','mis_datos');

        $u=$this->getUser();
        $usuario=$usuarioRepository->find($u->getId());

        $usuario->setNombre($request->request->get('nombre'));
        $usuario->setCorreo($request->request->get('correo'));

        
        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($usuario);
        $entityManager->flush();

        //return $this->redirectToRoute('mis_datos_index');
        $error="<div class='alert alert-success alert-dismissible fade show' role='alert'>
        <strong>Exito</strong> Datos modificados!!!
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
        <span aria-hidden='true'>&times;</span></button></div>";
        return $this->render('mis_datos/index.html.twig', ['misdatos' => $usuario,'mensaje'=>$error]);



    }
     /**
     * @Route("/password", name="mis_datos_password", methods={"GET","POST"})
     */
    public function password(UsuarioRepository $usuarioRepository, Request $request,UserPasswordEncoderInterface $encoder): Response
    {
        $this->denyAccessUnlessGranted('edit','mis_datos');
        $u=$this->getUser();
        $usuario=$usuarioRepository->find($u->getId());

        
        $password=$request->request->get('password');
        $encoded=$encoder->encodePassword($usuario,$password);
        $usuario->setPassword($encoded);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($usuario);
        $entityManager->flush();
        $error="<div class='alert alert-success alert-dismissible fade show' role='alert'>
        <strong>Exito</strong> Password modificada!!!
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
        <span aria-hidden='true'>&times;</span></button></div>";
        return $this->render('mis_datos/index.html.twig', ['misdatos' => $usuario,'error'=>$error]);
    }
}
