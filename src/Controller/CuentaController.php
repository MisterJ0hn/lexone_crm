<?php

namespace App\Controller;

use App\Entity\Cuenta;
use App\Entity\Empresa;
use App\Form\CuentaType;
use App\Repository\CuentaRepository;
use App\Repository\ModuloPerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

#[Route("/cuenta")]
class CuentaController extends AbstractController
{
    #[Route("/", name: "cuenta_index", methods: ["GET"])]
    public function index(CuentaRepository $cuentaRepository,ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','cuenta');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('cuenta',1);
        return $this->render('cuenta/index.html.twig', [
            'cuentas' => $cuentaRepository->findBy(['empresa'=>1]),
            'pagina'=> $pagina->getNombre(),
        ]);
    }

    #[Route("/new", name: "cuenta_new", methods: ["GET","POST"])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('create','cuenta');
        $user=$this->getUser();
        $empresa=$this->getDoctrine()->getRepository(Empresa::class)->find($user->getEmpresaActual());
        $cuentum = new Cuenta();
        $cuentum->setFechaCreacion(new \DateTime(date("Y-m-d h:i:s")));
        $cuentum->setFechaUltimamodificacion(new \DateTime(date("Y-m-d h:i:s")));
        $cuentum->setEmpresa($empresa);
        $form = $this->createForm(CuentaType::class, $cuentum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($cuentum);
            $entityManager->flush();

            return $this->redirectToRoute('cuenta_index');
        }

        return $this->render('cuenta/new.html.twig', [
            'cuentum' => $cuentum,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name: "cuenta_show", methods: ["GET"])]
    public function show(Cuenta $cuentum): Response
    {
        $this->denyAccessUnlessGranted('view','cuenta');
        return $this->render('cuenta/show.html.twig', [
            'cuentum' => $cuentum,
        ]);
    }

    #[Route("/{id}/edit", name: "cuenta_edit", methods: ["GET","POST"])]
    public function edit(Request $request, Cuenta $cuentum): Response
    {
        $this->denyAccessUnlessGranted('edit','cuenta');
        $form = $this->createForm(CuentaType::class, $cuentum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('cuenta_index');
        }

        return $this->render('cuenta/edit.html.twig', [
            'cuentum' => $cuentum,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name: "cuenta_delete", methods: ["DELETE"])]
    public function delete(Request $request, Cuenta $cuentum): Response
    {
        $this->denyAccessUnlessGranted('full','empresa');
        if ($this->isCsrfTokenValid('delete'.$cuentum->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            foreach($cuentum->getSucursals() as $sucursal){
                foreach ($sucursal->getContratos() as $contrato) {
                    $contrato->setSucursal(null);
                    $entityManager->persist($contrato);
                    $entityManager->flush();
                }
                foreach ($sucursal->getAgendas() as $agenda) {
                    $agenda->setSucursal(null);
                    $agenda->setCuenta(null);
                    $entityManager->persist($agenda);
                    $entityManager->flush();
                }
                $entityManager->remove($sucursal);
                $entityManager->flush();

            }
            foreach ($cuentum->getUsuarioCuentas() as $usuarioCuenta) {
                $entityManager->remove($usuarioCuenta);
                $entityManager->flush();
            }
            foreach ($cuentum->getJuzgadoCuentas() as $jusgadoCuenta) {
                $entityManager->remove($jusgadoCuenta);
                $entityManager->flush();
            }
            
            
            $entityManager->remove($cuentum);
            $entityManager->flush();
        }

        return $this->redirectToRoute('cuenta_index');
    }
}
