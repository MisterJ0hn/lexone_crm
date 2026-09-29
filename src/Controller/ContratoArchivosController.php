<?php

namespace App\Controller;

use App\Entity\Contrato;
use App\Entity\ContratoArchivos;
use App\Form\ContratoArchivosType;
use App\Repository\ContratoArchivosRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/contrato_archivos")]
class ContratoArchivosController extends AbstractController
{
    #[Route("/{id}", name: "contrato_archivos_index", methods: ["GET"])]
    public function index(Contrato $contrato): Response
    {
        $this->denyAccessUnlessGranted('view','contrato_archivos');

        $archivos=$contrato->getContratoArchivos();
        return $this->render('contrato_archivos/index.html.twig', [
            'contrato_archivos' => $archivos,
            'contrato'=>$contrato
        ]);
    }

    #[Route("/{id}/new", name: "contrato_archivos_new", methods: ["GET","POST"])]
    public function new(Contrato $contrato, Request $request): Response
    {
        $this->denyAccessUnlessGranted('create','contrato_archivos');
        $user=$this->getUser();
        $contratoArchivo = new ContratoArchivos();
        $contratoArchivo->setFechaSubida(new DateTime(date('Y-m-d h:i:s')));
        $contratoArchivo->setContrato($contrato);
        $contratoArchivo->setUsuarioRegistro($user);
        $form = $this->createForm(ContratoArchivosType::class, $contratoArchivo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            
            //subida de archivo

            $brochureFile =$form->get('url')->getData();
            
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
               
               
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $originalFilename;
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();
               
                
                // Move the file to the directory where brochures are stored
                //echo $this->getParameter('url_root').
                //$this->getParameter('archivos_contratos');
                
                $brochureFile->move($this->getParameter('url_root').
                    $this->getParameter('archivos_contratos'),
                    $newFilename
                );

                $contratoArchivo->setUrl($newFilename);
                $entityManager->persist($contratoArchivo);
                $entityManager->flush();
                
            }

            return $this->redirectToRoute('contrato_archivos_index',['id'=>$contrato->getId()]);
        }

        return $this->render('contrato_archivos/new.html.twig', [
            'contrato_archivo' => $contratoArchivo,
            'contrato'=>$contrato,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name: "contrato_archivos_show", methods: ["GET"])]
    /*
    public function show(ContratoArchivos $contratoArchivo): Response
    {
        return $this->render('contrato_archivos/show.html.twig', [
            'contrato_archivo' => $contratoArchivo,
        ]);
    }
*/
    #[Route("/{id}/edit", name: "contrato_archivos_edit", methods: ["GET","POST"])]
    public function edit(Request $request, ContratoArchivos $contratoArchivo): Response
    {
        $form = $this->createForm(ContratoArchivosType::class, $contratoArchivo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('contrato_archivos_index');
        }

        return $this->render('contrato_archivos/edit.html.twig', [
            'contrato_archivo' => $contratoArchivo,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name: "contrato_archivos_delete", methods: ["DELETE"])]
    public function delete(Request $request, ContratoArchivos $contratoArchivo): Response
    {
        $this->denyAccessUnlessGranted('full','contrato_archivos');
        $contrato =$contratoArchivo->getContrato();
        if ($this->isCsrfTokenValid('delete'.$contratoArchivo->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            
            $entityManager->remove($contratoArchivo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('contrato_archivos_index',['id'=>$contrato->getId()]);
    }
}
