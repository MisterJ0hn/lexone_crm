<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;


use App\Entity\Cobranza;
use App\Entity\Pago;
use App\Entity\Lotes;
use App\Entity\Usuario;
use App\Entity\Cuota;
use App\Entity\Contrato;
use App\Entity\Importacion;
use App\Entity\CobranzaRespuesta;
use App\Form\CobranzaType;
use App\Form\ImportacionType;
use App\Repository\ImportacionRepository;
use App\Repository\ContratoRepository;
use App\Repository\ContratoRolRepository;
use App\Repository\CobranzaRepository;
use App\Repository\PagoRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\CuotaRepository;
use App\Repository\CuentaRepository;
use App\Repository\CuentaCorrienteRepository;
use App\Repository\VencimientoRepository;
use App\Repository\UsuarioRepository;
use App\Repository\ConfiguracionRepository;
use App\Repository\DiasPagoRepository;
use App\Repository\LotesRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\DateType;

use App\Service\ContratoFunciones;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Validator\Constraints\IsNull;

ini_set('memory_limit', '-1');
#[Route("/cobranzaold")]
class CobranzaControllerold extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }
    #[Route("/", name: "cobranza_index", methods: ["GET"])]
    public function index(ContratoRepository $contratoRepository, 
                        CuotaRepository $cuotaRepository,
                        PagoRepository $pagoRepository,
                        PaginatorInterface $paginator,
                        ModuloPerRepository $moduloPerRepository,
                        Request $request,
                        CuentaRepository $cuentaRepository,
                        VencimientoRepository $vencimientoRepository): Response
    {
        $this->denyAccessUnlessGranted('view','cobranza');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('cobranza',$user->getEmpresaActual());
        $vencimiento=$vencimientoRepository->findOneMaxNotNull($user->getEmpresaActual(),'v.valMax','ASC');
        $filtro=null;
        $folio=null;
        $compania=null;
        $queryTotales="";
        $segmento="";
       
        //   "maximo ".$vencimiento->getValMax();
        //$vencimiento=$vencimientoArray[0];
        $vencimientos=$vencimientoRepository->findBy(['empresa'=>$user->getEmpresaActual(),'soloPorAdmin'=>false],["valMin"=>'ASC']);
        $otros=' DATEDIFF(now(),co.proximoVencimiento) >= '.$vencimiento->getValMin();
        if($user->getUsuarioTipo()->getId() == 1 || $user->getUsuarioTipo()->getId()==3){
            $otros=" 1=1 ";
        }
        $primerVencimiento=$vencimientoRepository->findOneBy(['empresa'=>$user->getEmpresaActual(),'soloPorAdmin'=>false],['valMin'=>'ASC']);

        $fecha=null;
        $error='';
        $status=$primerVencimiento->getId();
        $error_toast="";
        
       
        if(null !== $request->query->get('error_toast')){
            $error_toast=$request->query->get('error_toast');
        }
        if(null !== $request->query->get('bFolio') && $request->query->get('bFolio')!=''){
            $folio=$request->query->get('bFolio');
            $otros.=" and co.folio= $folio";
            
            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');
           // $fecha=$otros;

        }else{
           
            if(null == $status){

            }else{
            
                if(null != $request->query->get('bStatus')){
                    $status=$request->query->get('bStatus');
                }
                $vencimiento=$vencimientoRepository->find($status);
                if($vencimiento->getValMax() !== null && $vencimiento->getValMin() > 0){
                    $otros=' DATEDIFF(now(),co.proximoVencimiento) >= '.$vencimiento->getValMin().' and DATEDIFF(now(),co.proximoVencimiento) <= '.$vencimiento->getValMax().' and c.monto>='.$vencimiento->getMontoMax();
                }else if($vencimiento->getValMin()==0){
                    $otros=' DATEDIFF(now(),co.proximoVencimiento) <= '.$vencimiento->getValMax().' and c.monto>='.$vencimiento->getMontoMax();
                }else{
                    $otros=' DATEDIFF(now(),co.proximoVencimiento) >= '.$vencimiento->getValMin().' and c.monto>='.$vencimiento->getMontoMax();
                        
                }
             
                
            }
            if(null !== $request->query->get('bFiltro') && $request->query->get('bFiltro')!=''){
                $filtro=$request->query->get('bFiltro');
            }
            if(null !== $request->query->get('bCompania') && $request->query->get('bCompania')!=0){
                $compania=$request->query->get('bCompania');
            }
            if(null !== $request->query->get('bFecha')){
                $aux_fecha=explode(" - ",$request->query->get('bFecha'));
                $dateInicio=$aux_fecha[0];
                $dateFin=$aux_fecha[1];
            }else{
                $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
                $dateFin=date('Y-m-d');

            }
            #$fecha="c.fechaPago between '$dateInicio' and '$dateFin 23:59:59' ";
            
        }
        $fecha.=$otros." and a.status != 13";
        
        switch($user->getUsuarioTipo()->getId()){
            
            
            case 4:
            case 8:
            case 13:
            
                $query=$cuotaRepository->findVencimiento(null,$user->getEmpresaActual(),$compania,$filtro,null,true,$fecha, true,true);
                $companias=$cuentaRepository->findByPers(null);
                break;
            case 7://tramitador
                $query=$cuotaRepository->findVencimiento($user->getId(),$user->getEmpresaActual(),$compania,$filtro,7,true,$fecha,true,true);
                $companias=$cuentaRepository->findByPers(null);
                break;
            case 6: //abogado
                $query=$cuotaRepository->findVencimiento($user->getId(),$user->getEmpresaActual(),$compania,$filtro,6,true,$fecha, true,true);
                $companias=$cuentaRepository->findByPers(null);
                break;
            case 11://Administrativo

                //$query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha,true);
                $query=$cuotaRepository->findVencimiento(null,$user->getEmpresaActual(),$compania,$filtro,null,true,$fecha, true,true);
                $companias=$cuentaRepository->findByPers(null);
            break;
            case 12://Cobradores
                $lotes=array();
                foreach($user->getUsuarioLotes() as $usuarioLote){
                    $lotes[]=$usuarioLote->getLote()->getId();
                }
               
                    $and=" and ";
                
                if(count($lotes)>0){

                    $fecha.="$and co.idLote in (".implode(",",$lotes).") ";
                }else{
                    $fecha.="$and co.idLote is null ";
                }
                
                $query=$cuotaRepository->findVencimiento(null,$user->getEmpresaActual(),null,$filtro,null,true,$fecha,true,true);
                $companias=$cuentaRepository->findByPers(null);
                break;
            
            case 1://administrador y jefes    
            case 3:
                $vencimientos=$vencimientoRepository->findBy(['empresa'=>$user->getEmpresaActual()],["valMin"=>'ASC']);
                $query=$cuotaRepository->findVencimiento(null,$user->getEmpresaActual(),$compania,$filtro,null,true,$fecha, true,true);
                $queryTotales=$cuotaRepository->findVencimientoGroup(null,null,$compania,$filtro,null,true,$fecha, true,true);
                $companias=$cuentaRepository->findByPers(null);

                break;
            default:
                //$query=$contratoRepository->findByPers(null,null,$compania,$filtro,null,$fecha,true);
                $query=$cuotaRepository->findVencimiento(null,$user->getEmpresaActual(),null,$filtro,null,true,$fecha,true,true);
                $companias=$cuentaRepository->findByPers(null);
                
            break;
        }
        //$companias=$cuentaRepository->findByPers($user->getId());
        //$query=$contratoRepository->findAll();

      
        $cuotas=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            100 /*limit per page*/,
            array());
        
        return $this->render('cobranza/index.html.twig', [
            'cuotas' => $cuotas,
            'bFiltro'=>$filtro,
            'bFolio'=>$folio,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'pagina'=>$pagina->getNombre(),
            'finalizado'=>false,
            'error'=>$error,
            'error_toast'=>$error_toast,
            'vencimientos'=>$vencimientos,
            'vencimientoSeleccionado'=>$vencimiento,
            'queryTotales'=>$queryTotales
        ]);
    }

    
    #[Route("/finalizado", name: "cobranza_finalizado", methods: ["GET"])]
    public function finalizado(ContratoRepository $contratoRepository, CuotaRepository $cuotaRepository,PagoRepository $pagoRepository,PaginatorInterface $paginator,ModuloPerRepository $moduloPerRepository,Request $request,CuentaRepository $cuentaRepository): Response
    {
        $this->denyAccessUnlessGranted('view','cobranza');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('cobranza_finalizado',$user->getEmpresaActual());
        $filtro=null;
        $folio=null;
        $compania=null;
        $otros='';
        $otros='';
        $fecha=null;
        if(null !== $request->query->get('bFolio') && $request->query->get('bFolio')!=''){
            $folio=$request->query->get('bFolio');
            $otros=" co.id= $folio";

            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');
            $fecha=$otros;
        }else{
            if(null !== $request->query->get('bFiltro') && $request->query->get('bFiltro')!=''){
                $filtro=$request->query->get('bFiltro');
            }
            if(null !== $request->query->get('bCompania') && $request->query->get('bCompania')!=0){
                $compania=$request->query->get('bCompania');
            }
            if(null !== $request->query->get('bFecha')){
                $aux_fecha=explode(" - ",$request->query->get('bFecha'));
                $dateInicio=$aux_fecha[0];
                $dateFin=$aux_fecha[1];
            }else{
                $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
                $dateFin=date('Y-m-d');

            }
            //$fecha="c.fechaPago between '$dateInicio' and '$dateFin 23:59:59' ";
        }
      
        switch($user->getUsuarioTipo()->getId()){
            case 1://tramitador
            case 3:
            case 4:
            case 8:
            case 12:
                $query=$cuotaRepository->findVencimiento(null,$user->getEmpresaActual(),null,$filtro,7,false,$fecha);
                $companias=$cuentaRepository->findByPers(null               );
                break;
            case 7://tramitador
                $query=$cuotaRepository->findVencimiento($user->getId(),$user->getEmpresaActual(),null,$filtro,7,false,$fecha);
                $companias=$cuentaRepository->findByPers(null);
                break;
            case 6: //abogado
                $query=$cuotaRepository->findVencimiento($user->getId(),$user->getEmpresaActual(),null,$filtro,6,false,$fecha);
                $companias=$cuentaRepository->findByPers(null);
                break;
            case 11://Administrativo
                //$query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha,true);
                $query=$cuotaRepository->findVencimiento(null,$user->getEmpresaActual(),null,$filtro,null,false,$fecha);
                $companias=$cuentaRepository->findByPers(null);

                break;
            
            default:
                //$query=$contratoRepository->findByPers(null,null,$compania,$filtro,null,$fecha,true);
                $query=$cuotaRepository->findVencimiento($user->getId(),$user->getEmpresaActual(),null,$filtro,null,false,$fecha);
                $companias=$cuentaRepository->findByPers(null);
                
            break;
        }
        //$companias=$cuentaRepository->findByPers($user->getId());
        //$query=$contratoRepository->findAll();
        $cuotas=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            20 /*limit per page*/,
            array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));
        
        return $this->render('pago/index.html.twig', [
            'cuotas' => $cuotas,
            'bFiltro'=>$filtro,
            'bFolio'=>$folio,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'pagina'=>$pagina->getNombre(),
            'finalizado'=>true,
        ]);
    }

    #[Route("/resumen", name: "cobranza_resumen", methods: ["GET"])]
    public function resumen(CuentaRepository $cuentaRepository,CobranzaRepository $cobranzaRepository,PaginatorInterface $paginator,ModuloPerRepository $moduloPerRepository,Request $request): Response
    {

        $this->denyAccessUnlessGranted('view','cobranza_resumen');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('cobranza_resumen',$user->getEmpresaActual());
        $filtro=null;
        $folio=null;
        $compania=null;
        $otros='';
        
        
        if(null !== $request->query->get('bCompania') && $request->query->get('bCompania')!=0){
            $compania=$request->query->get('bCompania');
        }
        if(null !== $request->query->get('bFecha')){
            $aux_fecha=explode(" - ",$request->query->get('bFecha'));
            $dateInicio=$aux_fecha[0];
            $dateFin=$aux_fecha[1];
        }else{
            $dateInicio=date('Y-m-d');
            $dateFin=date('Y-m-d');
        }
        $fecha="c.fechaHora between '$dateInicio' and '$dateFin 23:59:59'";
      
        switch($user->getUsuarioTipo()->getId()){
            case 1://tramitador
            case 3:
            case 4:
            case 8:
           
                $query=$cobranzaRepository->findByContratoGroup(null,null,null,$filtro,$fecha);
                $total=$cobranzaRepository->findByContratoGroupCount(null,null,null,$filtro,$fecha);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());

                break;
            case 12:
            case 11://Administrativo
                //$query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha,true);
                $query=$cobranzaRepository->findByContratoGroup($user->getId(),null,null,$filtro,$fecha);
                $total=$cobranzaRepository->findByContratoGroupCount($user->getId(),null,null,$filtro,$fecha);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
             break;
            
            default:
                //$query=$contratoRepository->findByPers(null,null,$compania,$filtro,null,$fecha,true);
                $query=$cobranzaRepository->findByContratoGroup($user->getId(),null,null,$filtro,$fecha);
                $total=$cobranzaRepository->findByContratoGroupCount($user->getId(),null,null,$filtro,$fecha);
                $companias=$cuentaRepository->findByPers(null);
                
            break;
        }
       
        //$companias=$cuentaRepository->findByPers($user->getId());
        //$query=$contratoRepository->findAll();
        $cobranzas=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            20 /*limit per page*/,
            array());
        
        return $this->render('cobranza/resumen.html.twig', [
            'cobranzas' => $cobranzas,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'total'=>$total[1],
            'pagina'=>"Gestión ".$pagina->getNombre(),
        ]);
    }
   
    #[Route("/excel", name: "cobranza_excel", methods: ["GET"])]
    public function cobranzaExcel(ContratoRepository $contratoRepository, 
                        CuotaRepository $cuotaRepository,
                        PagoRepository $pagoRepository,
                        PaginatorInterface $paginator,
                        ModuloPerRepository $moduloPerRepository,
                        Request $request,
                        CuentaRepository $cuentaRepository,
                        VencimientoRepository $vencimientoRepository): Response
    {
        $this->denyAccessUnlessGranted('view','cobranza_excel');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('cobranza',$user->getEmpresaActual());
        $vencimientoArray=$vencimientoRepository->findBy(['empresa'=>$user->getEmpresaActual()],['id'=>'ASC'],1);
        $filtro=null;
        $folio=null;
        $compania=null;
        $vencimiento=$vencimientoArray[0];
        $otros=' DATEDIFF(now(),c.fechaPago)>'.$vencimiento->getValMax();
        $fecha=null;
        $error='';
        $error_toast="";
        if(null !== $request->query->get('error_toast')){
            $error_toast=$request->query->get('error_toast');
        }
        
        if(null !== $request->query->get('bFecha')){
            $dateInicio=$request->query->get('bFecha');
        
            
        }else{
            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateFin=date('Y-m-d');

        }
        $fecha="c.fechaPago between '$dateInicio' and '$dateInicio 23:59:59' and a.status != 13";
    
        //$fecha.=$otros;
        switch($user->getUsuarioTipo()->getId()){
            case 1://tramitador
            case 3:
            case 4:
            case 8:
            case 13:
            
                $query=$cuotaRepository->findVencimiento(null,null,$compania,$filtro,null,true,$fecha,true,true);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;
            case 7://tramitador
                $query=$cuotaRepository->findVencimiento($user->getId(),null,$compania,$filtro,7,true,$fecha,true,true);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;
            case 6: //abogado
                $query=$cuotaRepository->findVencimiento($user->getId(),null,$compania,$filtro,6,true,$fecha,true,true);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;
            case 11://Administrativo

                //$query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha,true);
                $query=$cuotaRepository->findVencimiento(null,null,$compania,$filtro,null,true,$fecha,true,true);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
            break;
            case 12://Cobradores
                $lotes=array();
                foreach($user->getUsuarioLotes() as $usuarioLote){
                    $lotes[]=$usuarioLote->getLote()->getId();
                }
                if(count($lotes)>0){
                    $fecha.=" and co.idLote in (".implode(",",$lotes).") ";
                }else{
                    $fecha.=" and co.idLote is null ";
                }
                
                $query=$cuotaRepository->findVencimiento(null,null,null,$filtro,null,true,$fecha,true,true);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;

            default:
                //$query=$contratoRepository->findByPers(null,null,$compania,$filtro,null,$fecha,true);
                $query=$cuotaRepository->findVencimiento(null,null,null,$filtro,null,true,$fecha,true,true);
                $companias=$cuentaRepository->findByPers(null);
                
            break;
        }
        //$companias=$cuentaRepository->findByPers($user->getId());
        //$query=$contratoRepository->findAll();

        $spreadSheet=new Spreadsheet();

        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadSheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Folio');
        $sheet->setCellValue('A1', 'Lote');
        $sheet->setCellValue('B1', 'nombre');
        $sheet->setCellValue('C1', 'apellido');
        $sheet->setCellValue('D1', 'rut');
        $sheet->setCellValue('E1', 'email');
        $sheet->setCellValue('F1', 'fonocobra');
        $sheet->setCellValue('G1', 'telefono1');
        $sheet->setCellValue('H1', 'telefono2');
        $sheet->setCellValue('I1', 'tramitador');
        $sheet->setCellValue('J1', 'linea1');
        $sheet->setCellValue('K1', 'linea2');
        $sheet->setCellValue('L1', 'linea3');
        $sheet->setCellValue('M1', 'linea4');
        $sheet->setCellValue('N1', 'linea5');


/*
        $sheet->setCellValue('B1', 'Lote');
        
        
        $sheet->setCellValue('E1', 'Cobrador');
        
        
        $sheet->setCellValue('H1', 'Fecha Contrato');
        $sheet->setCellValue('I1', 'Vencimiento');
        $sheet->setCellValue('J1', 'En Mora');
        $sheet->setCellValue('K1', 'Último Pago');
        $sheet->setCellValue('L1', 'Ultima Respuesta');
        $sheet->setCellValue('M1', 'Fecha Compromiso');
        $sheet->setCellValue('N1', 'Q. Ges.');*/

        $sheet = $spreadSheet->getActiveSheet();
        $i=2;
        foreach($query as $cuota){


            $vencimiento=$vencimientoRepository->findAll();
            $endeuda="";
           // $otros=' DATEDIFF(\''. $dateInicio.'\',c.fechaPago)>='.$vencimiento[0]->getValMax();
            $otros=' DATEDIFF(now(),c.fechaPago)>'.$vencimiento[0]->getValMax();
            $cuotadeuda=$cuotaRepository->deudaTotal($cuota->getContrato(),$otros);
        
            if(count($cuotadeuda)>0){
                $endeuda= $cuotadeuda[0][1]-$cuotadeuda[0][2];

                $sheet->setCellValue("A$i",$cuota->getContrato()->getFolio());
                $lote="";
                if($cuota->getContrato()->getIdLote()){
                    $lote=$cuota->getContrato()->getIdLote()->getNombre();
                }

                //$sheet->setCellValue("B$i",$lote);
                $nombre=explode(" ",$cuota->getContrato()->getNombre());
                $sheet->setCellValue("B$i", $nombre[0]);
                $cobrador='';
                $correo_cobrador="";
                if($cuota->getContrato()->getIdLote()){
                    foreach( $cuota->getContrato()->getIdLote()->getUsuarioLotes() as $usuarioLote){
                        $cobrador =$usuarioLote->getUsuario()->getNombre();
                        $correo_cobrador=$usuarioLote->getUsuario()->getTelefono();
                    }
                }
                $sheet->setCellValue("D$i",'');
                $sheet->setCellValue("E$i", $cuota->getContrato()->getEmail());
                
                

                //$sheet->setCellValue("E$i", $cobrador);
                $sheet->setCellValue("F$i", $correo_cobrador);
                
                
                /*
                
                $sheet->setCellValue("H$i", $cuota->getContrato()->getFechaCreacion());
                $sheet->setCellValue("I$i", $cuota->getFechaPago());
    */
                

                $sheet->setCellValue("K$i", "$".number_format($endeuda,0,",","."));
                $pago=$pagoRepository->findUPByContrato($cuota->getContrato());
                $ultimoPago='';
                $ultimoPagoLat="";
                if($pago){
                    $ultimoPago=$pago->getFechaPago()->format('Y-m-d')." ".$pago->getHoraPago()->format('H:i');
                    $ultimoPagoLat=$pago->getFechaPago()->format('d-m-Y');
                }
                
                $sheet->setCellValue("L$i","CONSULTE ALTERNATIVAS DE PAGO");
                $sheet->setCellValue("M$i", $cuota->getContrato()->getAgenda()->getCuenta()->getNombre());
                $sheet->setCellValue("J$i", date("d-m-Y",strtotime( $dateInicio)));
                /*$sheet->setCellValue("K$i", $ultimoPago);
                $sheet->setCellValue("L$i", $cuota->getContrato()->getUltimaFuncion());
                $sheet->setCellValue("N$i", $cuota->getContrato()->getQMov());*/
            
                $i++;
            }else{
                $endeuda= 0;
            
            
            }

        }
        $sheet->setTitle("Clientes Morosos");
 
        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadSheet);
    
        // Create a Temporary file in the system
        $fileName = 'clientesMorosos.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
    
        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);
    
        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);

    }

    #[Route("/{id}", name: "cobranza_show", methods: ["GET"])]
    public function show(Pago $pago): Response
    {
        $this->denyAccessUnlessGranted('view','cobranza');
        $pagoCuotas=$pago->getPagoCuotas();
        foreach($pagoCuotas as $pagoCuota){
            $cuota=$pagoCuota->getCuota();
            $contrato=$cuota->getContrato();
        }
        return $this->render('cobranza/show.html.twig', [
            'pago' => $pago,
            'contrato'=>$contrato,
            'pagina'=>"Ver Pago",
        ]);
    }
    #[Route("/{id}/vercobranza", name: "vercobranza_index", methods: ["GET","POST"])]
    public function vercobranzas(Request $request, Contrato $contrato,CobranzaRepository $cobranzaRepository,ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','cobranza');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('cobranza',$user->getEmpresaActual());
        $cobranzas=$cobranzaRepository->findByContrato($contrato->getId());

        return $this->render('cobranza/vercobranzas.html.twig', [
            'cobranzas' => $cobranzas,
            'pagina'=>"Ingreso ". $pagina->getNombre(),
            'contrato'=>$contrato,
            
        ]);
    }

    #[Route("/{id}/vercobranza_view", name: "vercobranza_view", methods: ["GET","POST"])]
    public function verpagosShow(Request $request, Contrato $contrato,CobranzaRepository $cobranzaRepository,PagoRepository $pagoRepository,ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','cobranza');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('cobranza',$user->getEmpresaActual());
        $cobranzas=$cobranzaRepository->findByContrato($contrato->getId());
        return $this->render('cobranza/vercobranza_show.html.twig', [
            'pagina'=>"Detalle ".$pagina->getNombre(),
            'contrato'=>$contrato,
            'cobranzas' => $cobranzas,
        ]);

    }
    #[Route("/{id}/detallepagos", name: "detallepagos_index", methods: ["GET","POST"])]
    public function detallepagos(Request $request, Cuota $cuota,PagoRepository $pagoRepository,ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','pago');
        $pagoCuotas=$cuota->getPagoCuotas();

        return $this->render('pago/detallepagos.html.twig', [
            'pagocuotas' => $pagoCuotas,
        ]);
        

    }
    #[Route("/{id}/new", name: "cobranza_new", methods: ["GET","POST"])]
    public function new(Request $request,
                        Contrato $contrato,
                        CuotaRepository $cuotaRepository,
                        ModuloPerRepository $moduloPerRepository,
                        UsuarioRepository $usuarioRepository): Response
    {
        $this->denyAccessUnlessGranted('create','cobranza');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('cobranza',$user->getEmpresaActual());
        $cobranza = new Cobranza();
        $cobranza->setFechaHora(new \DateTime(date('Y-m-d H:i')));
        $cobranza->setFecha(new \DateTime(date('Y-m-d')));
        $cobranza->setUsuarioRegistro($usuarioRepository->find($user->getId()));
        $cobranza->setContrato($contrato);
        
        $form = $this->createForm(cobranzaType::class, $cobranza);
        //$form->add('fechaHora',DateType::class,array('widget'=>'single_text','html5'=>false));
        $form->add('fechaCompromiso',DateType::class,array('widget'=>'single_text','html5'=>false));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;
            $entityManager->persist($cobranza);
            $entityManager->flush();

            $qmov=$contrato->getQMov();
            $qmov++;
            $contrato->setQMov($qmov);

            $contrato->setFechaUltimaGestion(new \DateTime(date('Y-m-d')));
            $contrato->setUltimaFuncion($cobranza->getRespuesta()->getNombre());
            $contrato->setFechaCompromiso($cobranza->getFechaCompromiso());
            
            $entityManager->persist($contrato);
            $entityManager->flush();
            return $this->redirectToRoute('vercobranza_view',['id'=>$contrato->getId()]);
           
        }

        return $this->render('cobranza/new.html.twig', [
            'cobranza' => $cobranza,
            'contrato'=>$contrato,
            'pagina'=>"Agregar Gestión ".$pagina->getNombre(),
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}/lote", name: "cobranza_lote", methods: ["GET","POST"])]
    public function lote(Request $request, 
                        Contrato $contrato,
                        ModuloPerRepository $moduloPerRepository,
                        LotesRepository $lotesRepository,
                        ConfiguracionRepository $configuracionRepository): Response
    { 
        $this->denyAccessUnlessGranted('edit','cobranza');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('cobranza',$user->getEmpresaActual());

        $configuracion=$configuracionRepository->find(1);
       
        if($request->request->get('cboLotes')){
            $contrato->setIdLote($lotesRepository->find($request->request->get('cboLotes')));
            $entityManager = $this->entityManager;
            $entityManager->persist($contrato);
            $entityManager->flush();
            return $this->redirectToRoute('cobranza_index',['id'=>$contrato->getId()]);
        }


        return $this->render('cobranza/lote.html.twig', [
            'contrato'=>$contrato,
            'pagina'=>'Editar Lote Contrato',
            'lotes'=>$lotesRepository->findBy(['empresa'=>$user->getEmpresaActual()]),
        ]);
    }
    
    #[Route("/{id}/cobrador", name: "cobranza_cobrador", methods: ["GET","POST"])]
    public function cobrador(Request $request, 
                        Lotes $lotes): Response
    { 
        $usuarioLotes =$lotes->getUsuarioLotes();
        $cobradores=array();
        foreach($usuarioLotes as $usuarioLote){
            $cobradores=array("nombre"=>$usuarioLote->getUsuario()->getNombre());
        }
        return $this->json($cobradores);
    }
    #[Route("/{id}/edit", name: "cobranza_edit", methods: ["GET","POST"])]
    public function edit(Request $request, Cobranza $cobranza,CuotaRepository $cuotaRepository,ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('edit','cobranza');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('cobranza',$user->getEmpresaActual());
        
        $form = $this->createForm(CobranzaType::class, $cobranza);
        $form->add('fechaHora',DateType::class,array('widget'=>'single_text','html5'=>false));
        $form->add('fechaCompromiso',DateType::class,array('widget'=>'single_text','html5'=>false));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            
            return $this->redirectToRoute('vercobranza_index',['id'=>$cobranza->getContrato()->getId()]);
            
        }

        return $this->render('cobranza/edit.html.twig', [
            'cobranza' => $cobranza,
            'contrato'=>$cobranza->getCuota()->getContrato(),
            'form' => $form->createView(),
            'pagina'=>'Editar '.$pagina->getNombre(),
        ]);
    }
    #[Route("/{id}/terminar", name: "cobranza_terminar", methods: ["GET","POST"])]
    function terminar(Contrato $contrato,
                    DiasPagoRepository $diasPagoRepository,
                    ModuloPerRepository $moduloPerRepository,
                    ContratoFunciones $contratoFunciones,
                    Request $request): Response
    {
        $this->denyAccessUnlessGranted('create','cobranza');
        $user=$this->getUser();
        
        $pagina=$moduloPerRepository->findOneByName('cobranza',$user->getEmpresaActual());

        if(null !== $request->query->get('status')){
            $error_toast=$contratoFunciones->terminarContrato($contrato,$request->query->get('status'),$request->request->get('txtObservacion'));
           
            return $this->redirectToRoute('cobranza_index',['error_toast'=>$error_toast]);

        }else{
            $error_toast="Toast.fire({
                icon: 'danger',
                title: 'Error al desistir'
              })";
        }

        return $this->render('cobranza/show.html.twig', [
            'contrato' => $contrato,
            'agenda'=>$contrato->getAgenda(),
            'pagina'=>$pagina->getNombre(),
            'diasPagos'=>$diasPagoRepository->findAll(),
            'metodo'=>"T",
            
        ]);

    }
   
    #[Route("/{id}/delete", name: "cobranza_delete", methods: ["GET","POST"])]
    public function delete(Request $request, Cobranza $cobranza): Response
    {
        $this->denyAccessUnlessGranted('full','cobranza');
        $user=$this->getUser();
        
        $entityManager = $this->entityManager;
        
        $cobranza->setIsNulo(true);
        $entityManager->persist($cobranza);
        $entityManager->flush();

        return $this->redirectToRoute('vercobranza_index',['id'=>$cobranza->getContrato()->getId()]);
    }

    #[Route("/{id}/compromiso", name: "cobranza_compromiso", methods: ["GET","POST"])]
    public function fechaCompromiso(Request $request, CobranzaRespuesta $cobranzaRespuesta): Response
    {
        if($cobranzaRespuesta->getIsFechaCompromiso()){
            $respuesta="true";
        }else{
            $respuesta="false";
        }
        return $this->render('cobranza/respuesta.html.twig', [
            'respuesta' => $respuesta,           
        ]);
    }
}
