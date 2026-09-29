<?php

namespace App\Controller;

use App\Entity\Importacion;
use App\Entity\Agenda;
use App\Form\ImportacionType;
use App\Repository\ImportacionRepository;
use App\Repository\UsuarioRepository;
use App\Repository\AgendaStatusRepository;
use App\Repository\ModuloPerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/importacion")
 */
class ImportacionController extends AbstractController
{
    /**
     * @Route("/", name="importacion_index", methods={"GET"})
     */
    public function index(ImportacionRepository $importacionRepository,
    ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','importacion');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('importacion',$user->getEmpresaActual());
        return $this->render('importacion/index.html.twig', [
            'importacions' => $importacionRepository->findAll(),
            'pagina'=>$pagina->getNombre(),
        ]);
    }

    /**
     * @Route("/new", name="importacion_new", methods={"GET","POST"})
     */
    public function new(Request $request,
                        UsuarioRepository $usuarioRepository,
                        AgendaStatusRepository $agendaStatusRepository,
                        ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('create','importacion');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('importacion',$user->getEmpresaActual());
        $importacion = new Importacion();
        $importacion->setFechaCarga(new \DateTime(date("Y-m-d H:i:s")));
        $form = $this->createForm(ImportacionType::class, $importacion);
        $form->add('cuenta');
        $form->handleRequest($request);

       if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('url')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                //$safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',$originalFilename);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('csv_importacion'),
                        $newFilename
                    );
                    $importacion->setNombre($originalFilename);
                    $importacion->setUrl($this->getParameter('csv_importacion').$newFilename);
                    $importacion->setUsuarioCarga($usuarioRepository->find($user->getId()));
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($importacion);
                    $entityManager->flush();
                    $fp = fopen($importacion->getUrl(), "r");
                    $i=0;
                    $paso=true;
                    $mensajeError="";
                    while (!feof($fp)){
                        $linea = fgets($fp);
                        $datos=explode(";",$linea);
                        if ($i==0){
                            $i++;
                            continue;
                        }
                        $i++;
                        
                        if($datos[0]=="") break;

                        $campania=$datos[0];
                        $nombreCliente=$datos[1];
                        $telefonoCliente=$datos[2];
                        $emailCliente=$datos[3];
    

                        if(trim($nombreCliente)==""){
                            $paso=false;
                            $mensajeError.="<li>Linea: $i - El Campo Nombre esta vacio</li>";
                        }
                        if(trim($telefonoCliente)==""){
                            
                            $paso=false;
                            $mensajeError.="<li>Linea: $i - El Campo telefono esta vacio</li>";
                        }
                        if(trim($emailCliente)==""){
                            $paso=false;
                            $mensajeError.="<li>Linea: $i - El Campo email esta vacio</li>";
                        }
                       
                        
                    }
                    fclose($fp);

                    if($paso){
                        $fp = fopen($importacion->getUrl(), "r");
                        $i=0;
                        while (!feof($fp)){
                            $linea = fgets($fp);
                            $datos=explode(";",$linea);
                            if ($i==0){
                                $i++;
                                continue;
                            }
                            
                            if($datos[0]=="") break;

                            $campania=$datos[0];
                            $nombreCliente=$datos[1];
                            $telefonoCliente=$this->format_phone($datos[2]);
                            $emailCliente=$datos[3];
                            //$agendador=$datos[15];
                            $agenda=new Agenda();

                            $agenda->setCuenta($importacion->getCuenta());
                            //$agenda->setAgendador($usuarioRepository->find($agendador));
                            $agenda->setNombreCliente(utf8_encode($nombreCliente));
                            $agenda->setEmailCliente(utf8_encode($emailCliente));
                            $agenda->setTelefonoCliente($telefonoCliente);
                            $agenda->setStatus($agendaStatusRepository->find(1));
                            $agenda->setFechaCarga(new \DateTime(date('Y-m-d H:i:s')));
                            $agenda->setCampania(utf8_encode($campania));

                            $entityManager->persist($agenda);
                            $entityManager->flush();

                            exec("php  /var/www/html/crm/bin/console app:asignar-leads");
                            //exec("php  d:\htdocs\desarrollos_symfony\crm\bin\console app:asignar-leads");

                        }
                        fclose($fp);
                    }else{
                        $error='<div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-ban"></i>Error!</h5>
                        Existe un error en la carga, favor revisar tu fichero.

                        <ul>'.$mensajeError.'</ul>
                      </div>';
                        return $this->render('importacion/error.html.twig', [
                            
                            'error'=>$error,
                        ]);
                    }

                    
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    return $this->render('importacion/new.html.twig', [
                        'importacion' => $importacion,
                        'form' => $form->createView(),
                        'pagina'=>"Importar",
                        'error'=>'<div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-ban"></i>Error!</h5>
                        Existe un error en la carga, favor revisar tu fichero.
                      </div>',
                    ]);
                }

            }

            return $this->redirectToRoute('importacion_index');
        }

        return $this->render('importacion/new.html.twig', [
            'importacion' => $importacion,
            'form' => $form->createView(),
            'pagina'=>$pagina->getNombre(),
        ]);
    }
    /**
     * @Route("/newPer", name="importacion_newPer", methods={"GET","POST"})
     */
    public function newPer(Request $request,UsuarioRepository $usuarioRepository,AgendaStatusRepository $agendaStatusRepository,
    ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('create','importacion');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('importacion',$user->getEmpresaActual());
        $importacion = new Importacion();
        $importacion->setFechaCarga(new \DateTime(date("Y-m-d H:i:s")));
        $form = $this->createForm(ImportacionType::class, $importacion,[
            'action' =>$this->generateUrl('importacion_newPer')]);
       
        $form->add('cuenta');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('url')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',$originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('csv_importacion'),
                        $newFilename
                    );
                    $importacion->setNombre($originalFilename);
                    $importacion->setUrl($this->getParameter('csv_importacion').$newFilename);
                    $importacion->setUsuarioCarga($usuarioRepository->find($user->getId()));
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($importacion);
                    $entityManager->flush();
                    $fp = fopen($importacion->getUrl(), "r");
                    $i=0;
                    $paso=true;
                    $mensajeError="";
                    while (!feof($fp)){
                        $linea = fgets($fp);
                        $datos=explode(";",$linea);
                        if ($i==0){
                            $i++;
                            continue;
                        }
                        $i++;
                        
                        if($datos[0]=="") break;

                        $campania=$datos[0];
                        $nombreCliente=$datos[1];
                        $telefonoCliente=$datos[2];
                        $emailCliente=$datos[3];
                        $usuario=$datos[4];

                        if(trim($nombreCliente)==""){
                            $paso=false;
                            $mensajeError.="<li>Linea: $i - El Campo Nombre esta vacio</li>";
                        }
                        if(trim($telefonoCliente)==""){
                            $paso=false;
                            $mensajeError.="<li>Linea: $i - El Campo telefono esta vacio</li>";
                        }
                        if(trim($emailCliente)==""){
                            $paso=false;
                            $mensajeError.="<li>Linea: $i - El Campo email esta vacio</li>";
                        }
                        if(trim($usuario)==""){
                            $paso=false;
                            $mensajeError.="<li>Linea: $i - El Campo ID esta vacio</li>";
                        }
                        
                    }
                    fclose($fp);
                    if($paso){
                        $i=0;
                        $fp = fopen($importacion->getUrl(), "r");

                        while (!feof($fp)){
                            $linea = fgets($fp);
                            $datos=explode(";",$linea);
                            if ($i==0){
                                $i++;
                                continue;
                            }
                            
                            if($datos[0]=="") break;

                            $campania=$datos[0];
                            $nombreCliente=$datos[1];
                            $telefonoCliente=$this->format_phone($datos[2]);
                            $emailCliente=$datos[3];
                            $usuario=$datos[4];
                            //$agendador=$datos[15];
                            $agenda=new Agenda();

                            $agenda->setCuenta($importacion->getCuenta());
                            //$agenda->setAgendador($usuarioRepository->find($agendador));
                            $agenda->setNombreCliente(utf8_encode($nombreCliente));
                            $agenda->setEmailCliente(utf8_encode($emailCliente));
                            $agenda->setTelefonoCliente($telefonoCliente);
                            $agenda->setStatus($agendaStatusRepository->find(1));
                            $agenda->setFechaCarga(new \DateTime(date('Y-m-d H:i:s')));
                            $agenda->setCampania(utf8_encode($campania));
                            $agenda->setAgendador($usuarioRepository->find($usuario));

                            $entityManager->persist($agenda);
                            $entityManager->flush();

                        
                        }
                        fclose($fp);
                    }else{
                        $error='<div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-ban"></i>Error!</h5>
                        Existe un error en la carga, favor revisar tu fichero.

                        <ul>'.$mensajeError.'</ul>
                      </div>';
                        return $this->render('importacion/error.html.twig', [
                            
                            'error'=>$error,
                        ]);
                    }
                   
                    
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    return $this->render('importacion/new.html.twig', [
                        'importacion' => $importacion,
                        'form' => $form->createView(),
                        'pagina'=>"Importar",
                        'error'=>'<div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-ban"></i>Error!</h5>
                        Existe un error en la carga, favor revisar tu fichero.
                      </div>',
                    ]);
                }

            }

            return $this->redirectToRoute('importacion_index');
        }

        return $this->render('importacion/newPer.html.twig', [
            'importacion' => $importacion,
            'form' => $form->createView(),
            
            'pagina'=>$pagina->getNombre(),
        ]);
    }

    /**
     * @Route("/{id}", name="importacion_show", methods={"GET"})
     */
    public function show(Importacion $importacion): Response
    {
        return $this->render('importacion/show.html.twig', [
            'importacion' => $importacion,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="importacion_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Importacion $importacion): Response
    {
        $form = $this->createForm(ImportacionType::class, $importacion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('importacion_index');
        }

        return $this->render('importacion/edit.html.twig', [
            'importacion' => $importacion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="importacion_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Importacion $importacion): Response
    {
        if ($this->isCsrfTokenValid('delete'.$importacion->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($importacion);
            $entityManager->flush();
        }

        return $this->redirectToRoute('importacion_index');
    }

    public function format_phone($telefono){
        $telefono=trim($telefono);

        $largo=strlen($telefono);
        if($largo>=9){
            $pos=strpos($telefono,"+56");
            if($pos!==false)
            {
                return $telefono;
            }else{
                return "+56".$telefono;
            }
        }else{
            $pos=strpos($telefono,"+56");
            if($pos!==false)
            {
                return $telefono;
            }else{
                return "+569".$telefono;
            }
        }

    }
}
