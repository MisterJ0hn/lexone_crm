<?php

namespace App\Controller;

use App\Entity\Causa;
use Doctrine\ORM\EntityManagerInterface;
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

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }
     #[Route(path: '/{id}', name: 'contrato_archivos_index', methods: ['GET'])]
    public function index(Causa $causa, ContratoArchivosRepository $contratoArchivosRepository): Response
    {
        $this->denyAccessUnlessGranted('view','contrato_archivos');
        $contrato= $causa->getAgenda()->getContrato();
        $archivos=$contratoArchivosRepository->findBy(['causa'=>$causa]);
        return $this->render('contrato_archivos/index.html.twig', [
            'contrato_archivos' => $archivos,
            'contrato'=>$contrato,
            'causa'=>$causa
        ]);
    }

    #[Route(path: '/{id}/new', name: 'contrato_archivos_new', methods: ['GET', 'POST'])]
    public function new(Causa $causa, Request $request): Response
    {
        $this->denyAccessUnlessGranted('create','contrato_archivos');
        $user=$this->getUser();
        $contrato= $causa->getAgenda()->getContrato();
        $contratoArchivo = new ContratoArchivos();
        $contratoArchivo->setFechaSubida(new DateTime(date('Y-m-d h:i:s')));
        $contratoArchivo->setContrato($contrato);
        $contratoArchivo->setCausa($causa);
        $contratoArchivo->setUsuarioRegistro($user);
        $form = $this->createForm(ContratoArchivosType::class, $contratoArchivo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;

            //subida de archivo

            $brochureFile =$form->get('url')->getData();
            
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
               
               
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // Nombre seguro: sin rutas, puntos ni caracteres especiales (evita traversal y dobles extensiones).
                $safeFilename = trim(preg_replace('/[^A-Za-z0-9_-]+/', '_', (function_exists('transliterator_transliterate') ? transliterator_transliterate('Any-Latin; Latin-ASCII', $originalFilename) : false) ?: $originalFilename), '_');
                $safeFilename = substr($safeFilename !== '' ? $safeFilename : 'archivo', 0, 80);
                // La extensión ya fue validada contra la lista permitida en ContratoArchivosType.
                $extension = strtolower($brochureFile->getClientOriginalExtension());
                $newFilename = $safeFilename.'-'.uniqid().'.'.$extension;
               
                
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

            return $this->redirectToRoute('contrato_archivos_index',['id'=>$causa->getId()]);
        }

        return $this->render('contrato_archivos/new.html.twig', [
            'contrato_archivo' => $contratoArchivo,
            'contrato'=>$contrato,
            'form' => $form->createView(),
        ]);
    }

   
    #[Route(path: '/{id}/edit', name: 'contrato_archivos_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ContratoArchivos $contratoArchivo): Response
    {
        $form = $this->createForm(ContratoArchivosType::class, $contratoArchivo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('contrato_archivos_index');
        }

        return $this->render('contrato_archivos/edit.html.twig', [
            'contrato_archivo' => $contratoArchivo,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'contrato_archivos_delete', methods: ['DELETE'])]
    public function delete(Request $request, ContratoArchivos $contratoArchivo): Response
    {
        $this->denyAccessUnlessGranted('full','contrato_archivos');
        $causa =$contratoArchivo->getCausa();
        if ($this->isCsrfTokenValid('delete'.$contratoArchivo->getId(), $request->request->get('_token'))) {
            $entityManager = $this->entityManager;

            $entityManager->remove($contratoArchivo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('contrato_archivos_index',['id'=>$causa->getId()]);
    }
}
