<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Contrato;
use App\Entity\ContratoRol;
use App\Entity\Usuario;
use App\Entity\Cuota;
use App\Form\ContratoType;
use App\Entity\AgendaObservacion;
use App\Entity\ContratoAnexo;
use App\Form\ContratoRolType;
use App\Repository\AgendaObservacionRepository;
use App\Repository\AgendaRepository;
use App\Repository\ContratoRepository;
use App\Repository\ContratoRolRepository;
use App\Repository\JuzgadoRepository;
use App\Repository\SucursalRepository;
use App\Repository\CuentaRepository;
use App\Repository\DiasPagoRepository;
use App\Repository\UsuarioRepository;
use App\Repository\UsuarioTipoRepository;
use App\Repository\AgendaStatusRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\CuotaRepository;
use App\Repository\ConfiguracionRepository;
use App\Service\Toku;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Component\Pager\PaginatorInterface;
#[Route("/desconoce")]
class DesconoceController  extends AbstractController{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route("/", name: "desconoce_index", methods: ["GET","POST"])]
    public function index(ContratoRepository $contratoRepository,
                        PaginatorInterface $paginator,
                        ModuloPerRepository $moduloPerRepository,
                        Request $request,
                        CuentaRepository $cuentaRepository,
                        AgendaRepository $agendaRepository): Response
    {
        $this->denyAccessUnlessGranted('view','desconoce');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('desconoce',$user->getEmpresaActual());
        $filtro=null;
        $error='';
        $error_toast="";
        $folio=null;
        $otros="";
        $status=null;
        $str_folio='';
        $str_status='a.status in (12,13,15)';
        $fecha='';
        $statuesgroup="13,12,15";
        if(null !== $request->query->get('error_toast')){
            $error_toast=$request->query->get('error_toast');
        }
        $compania=null;

        if(null !== $request->query->get('bFolio') && $request->query->get('bFolio')!=''){
            $folio=$request->query->get('bFolio');
            $str_folio=" and c.folio= $folio";

            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30*24);//2 años atrás
            $dateFin=date('Y-m-d');
            

        }else{
            if(null !== $request->query->get('bCompania') && $request->query->get('bCompania')!=0){
                $compania=$request->query->get('bCompania');
            }

            if(null !== $request->query->get('bFiltro') && $request->query->get('bFiltro')!=''){
                $filtro=$request->query->get('bFiltro');
            }
            if(null !== $request->query->get('bStatus') && $request->query->get('bStatus')!=''){
                $status=$request->query->get('bStatus');
                $statuesgroup=$status;

                $str_status="a.status= $status";
            }
            
            if(null !== $request->query->get('bFecha')){
                $aux_fecha=explode(" - ",$request->query->get('bFecha'));
                $dateInicio=$aux_fecha[0];
                $dateFin=$aux_fecha[1];
                $fecha=" and c.fechaDesiste between '$dateInicio' and '$dateFin 23:59:59' " ;
                $fecha_resumen=" c.fechaDesiste between '$dateInicio' and '$dateFin 23:59:59' " ;
            }else{
                $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30*24);//2 años atrás
                //$dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*7);
                $dateFin=date('Y-m-d');

                 $fecha=" and c.fechaDesiste between '$dateInicio' and '$dateFin 23:59:59' " ;
                 $fecha_resumen=" c.fechaDesiste between '$dateInicio' and '$dateFin 23:59:59' " ;
            }
        }
        //$fecha.=$otros;
        $str_query=$str_status.$fecha.$str_folio;
        $str_resumen=$fecha_resumen.$str_folio;

    
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 4:
            case 1:
            case 11:
            case 13:
                $query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$str_query);
                $queryresumen=$contratoRepository->findByPersGroup(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,0,$str_resumen);
                $companias=$cuentaRepository->findByPers($user->getId(),1);
            break;
            case 7:
            case 12:

                $query=$contratoRepository->findByPers(null,null,$compania,$filtro,null,$str_query);
                $queryresumen=$contratoRepository->findByPersGroup(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,0,$str_resumen);
                $companias=$cuentaRepository->findByPers($user->getId());
                break;
            default:
                $query=$contratoRepository->findByPers($user->getId(),null,$compania,$filtro,null,$str_query);
                $queryresumen=$contratoRepository->findByPersGroup($user->getId(),$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,0,$str_resumen);
                $companias=$cuentaRepository->findByPers($user->getId());
                
            break;
        }
        //$companias=$cuentaRepository->findByPers($user->getId());
        //$query=$contratoRepository->findAll();
 
        $contratos=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            20 /*limit per page*/,
            array('defaultSortFieldName' => 'fechaDesiste', 'defaultSortDirection' => 'desc'));
        return $this->render('desconoce/index.html.twig', [
            'contratos' => $contratos,
            'bFiltro'=>$filtro,
            'bFolio'=>$folio,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'status'=>$status,
            'resumenes'=>$queryresumen,
            'pagina'=>$pagina->getNombre(),
            'error'=>$error,
            'statuesGroup'=>$statuesgroup,
            'error_toast'=>$error_toast,
        ]);
    }
    #[Route("/{id}/edit", name: "desconoce_edit", methods: ["GET","POST"])]
    public function edit(Contrato $contrato,
                        DiasPagoRepository $diasPagoRepository,
                        ModuloPerRepository $moduloPerRepository,
                        AgendaStatusRepository $agendaStatusRepository,
                        UsuarioRepository $usuarioRepository,
                        CuotaRepository $cuotaRepository,
                        Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit','desconoce');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('desconoce',$user->getEmpresaActual());

        $multas=$cuotaRepository->findBy(['isMulta'=>true,'contrato'=>$contrato]);
        if(null !== $request->query->get('status')){
            $status= $request->query->get('status');
            $observacion_texto= $request->request->get('txtObservacion');
            $entityManager = $this->entityManager;
            $agenda=$contrato->getAgenda();
            $agenda->setStatus($agendaStatusRepository->find($status));

            $entityManager->persist($agenda);
            $entityManager->flush();
            if($status==15){
                $detalleCuotas=$contrato->getDetalleCuotas();
               // $toku=new Toku();

                foreach($detalleCuotas as $detalleCuota){
                // $contrato->removeDetalleCuota($detalleCuota);
                    if($detalleCuota->getPagado()<$detalleCuota->getMonto()){
                        $detalleCuota->setAnular(true);

                        $detalleCuota->setFechaAnulacion(new \DateTime(date('Y-m-d')));
                        
                        $entityManager->persist($detalleCuota);
                        $entityManager->flush();
                       /* if($detalleCuota->getInvoiceId()!=null){  
                            $toku->anularInvoice($detalleCuota->getInvoiceId());
                        }*/
                    }
                }
                $contrato->setIsFinalizado(true);
                $contrato->setFechaTermino(new \DateTime(date('Y-m-d h:i')));
                $entityManager->persist($contrato);
                $entityManager->flush();
            }
            if($status==7){
                foreach($multas as $multa){
                    $multa->setAnular(true);
                    $multa->setFechaAnulacion(new \DateTime(date('Y-m-d')));
                    $entityManager->persist($multa);
                    $entityManager->flush();
                    /*$toku=new Toku();
                    if($multa->getInvoiceId()!=null){
                        $toku->anularInvoice($multa->getInvoiceId());
                    }*/


                }
                $usuario=$contrato->getCliente();
                $detalleCuotas=$contrato->getDetalleCuotas();
                //$toku=new Toku();

                foreach($detalleCuotas as $cuota){
                    if($cuota->getIsMulta()==null){
                        /*if($usuario->getTokuId()!= null ){
                           // $cuotaResultToku=$toku->crearInvoice($usuario->getTokuId(),strval($contrato->getFolio()),$cuota->getMonto(),$cuota->getFechaPago()->format('Y-m-d'));
                            error_log("<br><br> Usuario : ".print_r($cuotaResultToku,true),3,"/home/micrm.cl/test/TokuWebhook_log");
                        }*/
                        $cuota->setAnular(0);
                        $entityManager->persist($cuota);
                        $entityManager->flush();
                    }
                }
            }
            $observacion=new AgendaObservacion();
            $observacion->setAgenda($agenda);
            $observacion->setUsuarioRegistro($usuarioRepository->find($user->getId()));
            $observacion->setStatus($agendaStatusRepository->find($status));
            $observacion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
            $observacion->setObservacion($observacion_texto);
            $entityManager->persist($observacion);
            $entityManager->flush();
            if($status==15){
                $error_toast="Toast.fire({
                icon: 'success',
                title: 'Cliente confirma termino de contrato'
              })";
              return $this->redirectToRoute('desconoce_pdf',['id'=>$contrato->getId(),'error_toast'=>$error_toast]);
            }else{
                $error_toast="Toast.fire({
                    icon: 'success',
                    title: 'Cliente reconsidera contrato'
                  })";
                  return $this->redirectToRoute('desconoce_index',['error_toast'=>$error_toast]);

            }
           

         
        }
            
        return $this->render('desconoce/show.html.twig', [
            'contrato' => $contrato,
            'agenda'=>$contrato->getAgenda(),
            'pagina'=>$pagina->getNombre(),
            'diasPagos'=>$diasPagoRepository->findAll(),
            'multas'=>$multas,
            'metodo'=>'R',
        ]);
    }
    #[Route("/{id}", name: "desconoce_show", methods: ["GET"])]
    public function show(Contrato $contrato,DiasPagoRepository $diasPagoRepository,ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','desconoce');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('desconoce',$user->getEmpresaActual());
        return $this->render('desconoce/show.html.twig', [
            'contrato' => $contrato,
            'agenda'=>$contrato->getAgenda(),
            'pagina'=>$pagina->getNombre(),
            'diasPagos'=>$diasPagoRepository->findAll(),
            
        ]);
    }

    #[Route("/{id}/pdf", name: "desconoce_pdf", methods: ["GET","POST"])]
    public function pdf(Contrato $contrato,
                        CuotaRepository $cuotaRepository , 
                        AgendaObservacionRepository $agendaObservacionRepository): Response
    {
        $this->denyAccessUnlessGranted('view','terminos');
        $entityManager = $this->entityManager;
        
        $anexos=$contrato->getContratoAnexos();
        $crear_anexo=true;
        foreach($anexos as $anexo){
            if($anexo->getIsDesiste()){
                $crear_anexo=false;
                $anexo_desiste=$anexo;
            }
        }
        if($crear_anexo){
            $filename = sprintf('desestimiento-'.$contrato->getId().'-%s.pdf',rand(0,9000));
            
            $anexo=new ContratoAnexo();
            $anexo->setContrato($contrato);
            $anexo->setFechaCreacion(new \DateTime(date('Y-m-d H:i')));
            $anexo->setIsDesiste(true);
            

            $anexo->setPdf($filename);
            $entityManager->persist($anexo);
            $entityManager->flush();

            $contrato->setFechaPdfAnexo(new \DateTime(date('Y-m-d H:i')));
            $entityManager->persist($contrato);
            $entityManager->flush();
            $cuotas=$cuotaRepository->findBy(['contrato'=>$contrato,'isMulta'=>true]);
            foreach($cuotas as $cuota){
                if($cuota->getIsMulta() && !$cuota->getAnular()){
                    $cuota->setAnexo($anexo);
                    $entityManager->persist($cuota);
                    $entityManager->flush();
                }
            }

            $observacion=$agendaObservacionRepository->findOneBy(['agenda'=>$contrato->getAgenda(),'status'=>[12,13]],['id'=>'desc']);
         
            $html = $this->renderView('terminos/print.html.twig', array(
                'anexo' => $anexo,
                'Titulo'=>"Contrato",
                'contrato'=>$contrato,
                'status'=>$observacion->getStatus(),
                'cuotas'=>$cuotaRepository->findBy(['anexo'=>$anexo]),
            ));
            /*$snappy->generateFromHtml(
            $html,
            $this->getParameter('url_root'). $this->getParameter('pdf_contratos').$filename
            );
            return new PdfResponse(
                $snappy->getOutputFromHtml($html, array(
                    'page-size' => 'letter')),
                $filename
            );*/

            // Configure Dompdf según sus necesidades
            $pdfOptions = new Options();
            $pdfOptions->set('defaultFont', 'helvetica');
        
            //$pdfOptions->set('fontHeightRatio',0.1);
            
            // Crea una instancia de Dompdf con nuestras opciones
            $dompdf = new Dompdf($pdfOptions);

            $dompdf->getOptions()->setChroot(array($this->getParameter('url_raiz')));
            
            // Recupere el HTML generado en nuestro archivo twig
        /* $html = $this->renderView('default/mypdf.html.twig', [
                'title' => "Welcome to our PDF Test"
            ]);*/
            
            // Cargar HTML en Dompdf
            $dompdf->loadHtml($html);
            
            // (Opcional) Configure el tamaño del papel y la orientación 'vertical' o 'vertical'
            $dompdf->setPaper('letter', 'portrait');

            // Renderiza el HTML como PDF
            $dompdf->render();

            $file=$dompdf->output();
            file_put_contents($this->getParameter('url_root'). $this->getParameter('pdf_contratos').$filename,$file);
            // Envíe el PDF generado al navegador (descarga forzada)
        /* $dompdf->stream($filename, [
                "Attachment" => true
            ]);*/ 
           
        }
        return $this->redirectToRoute('desconoce_index');
    }

    
}
