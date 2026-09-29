<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Agenda;
use App\Entity\AgendaObservacion;
use App\Entity\AgendaStatus;
use App\Entity\AgendaSubStatus;
use App\Entity\Canal;
use App\Entity\Usuario;
use App\Entity\Empresa;
use App\Entity\ModuloPer;
use App\Form\AgendaType;
use App\Repository\AgendaContactoRepository;
use App\Repository\AgendaRepository;
use App\Repository\ReunionRepository;
use App\Repository\UsuarioRepository;
use App\Repository\UsuarioNoDisponibleRepository;
use App\Repository\AgendaStatusRepository;
use App\Repository\AgendaSubStatusRepository;
use App\Repository\ContratoRepository;
use App\Repository\CuentaRepository;
use App\Repository\EmpresaRepository;
use App\Repository\TipoClienteRepository;
use App\Repository\UsuarioTipoRepository;
use App\Service\ActualizaLead;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


/**
 * @Route("/panel_agendador")
 */
class PanelAgendadorController extends AbstractController
{
    /**
     * @Route("/", name="panel_agendador_index")
     */
    public function index(AgendaRepository $agendaRepository,
                          CuentaRepository $cuentaRepository,
                          PaginatorInterface $paginator,
                          UsuarioRepository $usuarioRepository,
                          UsuarioTipoRepository $usuarioTipoRepository,
                          AgendaStatusRepository $agendaStatusRepository,
                          ContratoRepository $contratoRepository,
                          EmpresaRepository $empresaRepository,
                          Request $request): Response
    {
        $this->denyAccessUnlessGranted('view','panel_agendador');

        $user=$this->getUser();
        $usuario=$usuarioRepository->find($user->getId());
        //Carga manual

        $agenda = new Agenda();
        $error='';
        $error_toast="";
            if($request->query->get('msg')=='exito'){
                $error_toast="Toast.fire({
                    icon: 'success',
                    title: 'Registro grabado con exito'
                })";
                
            }
        
        $empresa=$empresaRepository->find($user->getEmpresaActual());
        
        $agenda->setStatus($agendaStatusRepository->find(1));
        $agenda->setFechaCarga(new \DateTime(date('Y-m-d H:i:s')));
        $agenda->setCampania('.');
        $agenda->setEmpresa($empresa);
        $form = $this->createForm(AgendaType::class, $agenda);
        //$form->add('campania');
        $form->add('ciudadCliente');
        $form->add('canal' ,EntityType::class,[
            
            'class' => Canal::class,
            'query_builder' => function (EntityRepository $er) {
                $user=$this->getUser();
                return $er->createQueryBuilder('c')->AndWhere('c.empresa = 1')->andWhere('c.estado=true');
            },
        ]);
        
        $form->handleRequest($request);

        switch($user->getUsuarioTipo()->getId()){
            case 1:
                $cuentas=$cuentaRepository->findBy(['empresa'=>$user->getEmpresaActual()]);
            break;
            default:
                $cuentas=$cuentaRepository->findByPers($user->getId(),$user->getEmpresaActual());
                //$cuentas=$cuentaRepository->findByPers($usuarioRepository->find($user->getId()));
                break;
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $sql="";
            $sql1="";
            $telefono = $form->getData()->getTelefonoCliente();
            //$telefonoRecado= $form->getData()->getTelefonoRecadoCliente();
            //$canal=$request->request->get('cboCanal');
            if(trim($telefono)!=""){
                $sql=" (a.telefonoCliente='$telefono' ) ";
            }

            $canal=$agenda->getCanal()->getNombre();
            
                # code...

            $registrar=true;
            //$agenda_existe=$contratoRepository->findByPersSinContr(null,null,null,null,null,3,$sql.$sql1);
            //$contrato_existe=$contratoRepository->findByPers(null,null,null,null,null, $sql.$sql1);
            //if(null != $contrato_existe){
            $agendas=$agendaRepository->findByPers($user->getId(),$user->getEmpresaActual(),null,null,null, 3,$sql.$sql1);
            
            if(null != $agendas ){
                $cont_agendas=0;
                foreach($agendas as $_agenda ){
                   
                    $contrato_terminado=$contratoRepository->findOneBy(['agenda'=>$_agenda, 'isFinalizado'=>true]);

                    if(null == $contrato_terminado){
                        $cont_agendas+=1;
                    }
                }
                if($cont_agendas>0){
                    $registrar=true;
                }
            }

            
            if($registrar){
               
                //$usuario=$request->request->get('cboAgendador');
                foreach ($cuentas as $cuenta) {
                    $agenda->setCuenta($cuenta);
                }
                $agenda->setAgendador($usuarioRepository->find($usuario));

                $agenda->setCampania($canal);
                
                $entityManager->persist($agenda);
                $entityManager->flush();

                $observacion=new AgendaObservacion();
                $observacion->setAgenda($agenda);
                $observacion->setUsuarioRegistro($usuarioRepository->find($user->getId()));
                $observacion->setStatus($agenda->getStatus());
                $observacion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                $observacion->setObservacion("Genera carga manual");
                // $agenda->setObservacion("");
                $entityManager->persist($observacion);
                $entityManager->flush();
                
                $this->addFlash('success',' Carga realizada con éxito');
               return $this->redirectToRoute('panel_agendador_index',['bStatus'=>1]);
               

                // $agenda=new Agenda();
            }else{
                 $this->addFlash('error','Este lead ya se encuentra en agenda, favor verifique la información');
            }
           
        }



        //fin Carga Manual
        $pagina=$this->getDoctrine()->getRepository(ModuloPer::class)->findOneByName('panel_agendador',1);
        $filtro=null;
        $compania=null;
        $fecha=null;
        $statues='1,2,8';
        $statuesgroup="1,2,8";
        $status=null;
        $tipo_fecha=0;
        $agendador=null;
        if(null !== $request->query->get('bFiltro') && trim($request->query->get('bFiltro'))!=''){
            $filtro=$request->query->get('bFiltro');
        }
        if(null !== $request->query->get('bCompania')&&$request->query->get('bCompania')!=0){
            $compania=$request->query->get('bCompania');
        }

        if(null !== $request->query->get('bFecha')){
            $aux_fecha=explode(" - ",$request->query->get('bFecha'));
            $dateInicio=$aux_fecha[0];
            $dateFin=$aux_fecha[1];
            $statues=$statuesgroup;
        }else{
            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30*24);//2 años atrás
           // $dateInicio="2024-01-01";
            $dateFin=date('Y-m-d');
        }
        if(null !== $request->query->get('bTipofecha') ){
            $tipo_fecha=$request->query->get('bTipofecha');
        }

        if(null !== $request->query->get('bAgendador')){
            if($request->query->get('bAgendador')==0){
                $agendador=null;
            }else{
                $agendador=$request->query->get('bAgendador');
            }

        }

        $agendadores=$usuarioRepository->findBy(['usuarioTipo'=>$usuarioTipoRepository->find(5),'estado'=>1]);

        $fecha="(a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59')
               " ;
        /*switch($tipo_fecha){
            case 0:
                
                break;
            case 1:
                $fecha="a.fechaAsignado between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
            case 2:
                $fecha="a.fechaContrato between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
            default:
                $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
        }*/
        //$fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
       
        if(null !== $request->query->get('bStatus') && trim($request->query->get('bStatus')!='')){
            $status=$request->query->get('bStatus');
            $statues=$status;
            $statuesgroup=$status;
        }
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 1:
            case 8:
            case 13:
                $agendadores=$usuarioRepository->findBy(['usuarioTipo'=>$usuarioTipoRepository->find(5),'estado'=>1]);

                $query=$agendaRepository->findByPers(null,$user->getEmpresaActual(),null,$statues,$filtro,0,$fecha);
                $queryresumen=$agendaRepository->findByPersGroup(null,$user->getEmpresaActual(),null,$statuesgroup,$filtro,0,$fecha);
                $companias=$cuentaRepository->findByPers($user->getId(),1);
            break;
            default:
            $agendadores=$usuarioRepository->findBy(['id'=>$user->getId()]);

                $query=$agendaRepository->findByPers($user->getId(),$user->getEmpresaActual(),$compania,$statues,$filtro,0,$fecha);
                $queryresumen=$agendaRepository->findByPersGroup($user->getId(),$user->getEmpresaActual(),null,$statuesgroup,$filtro,0,$fecha);
                $companias=$cuentaRepository->findByPers($user->getId());
            break;
        }

         
        
        $agendas=$paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page', 1), /*page number*/
        20 /*limit per page*/,
        array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));

        return $this->render('panel_agendador/index.html.twig', [
            'agendas' => $agendas,
            'pagina'=>$pagina->getNombre(),
            'bFiltro'=>$filtro,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'resumenes'=>$queryresumen,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'status'=>$status,
            'statuesGroup'=>$statuesgroup,
            'tipoFecha'=>$tipo_fecha,
            'agendadores'=>$agendadores,
            'bAgendador'=>$agendador,
            'form' => $form->createView(),
            'cuentas'=>$cuentas,
            'error'=>$error,
            'error_toast'=>$error_toast,
        ]);
    }
    /**
     * @Route("/reasignar", name="panel_agendador_reasignar", methods={"GET","POST"})
     */
    public function reasignar(Request $request,UsuarioRepository $usuarioRepository,AgendaRepository $agendaRepository):Response
    {
        $user=$this->getUser();
        $agenda_id=$request->query->get('agenda');
        $agenda=$agendaRepository->find($agenda_id);
        $empresa=$this->getDoctrine()->getRepository(Empresa::class)->find($user->getEmpresaActual());
        return $this->render('panel_agendador/reasignar.html.twig', [
            'cuentas'=>$empresa->getCuentas(),
            'agenda'=> $agenda,     
        ]);
    }

    /**
     * @Route("/sub_status", name="panel_agendador_sub_status", methods={"GET","POST"})
     */
    public function subStatus(Request $request, 
                                AgendaSubStatusRepository $agendaSubStatusRepository,
                                AgendaStatusRepository $agendaStatusRepository):Response
    {
        $status=$agendaStatusRepository->find($request->query->get('status'));
        $agendaSubStatues=$agendaSubStatusRepository->findBy(['agendaStatus'=>$status]);
       
        return $this->render('panel_agendador/sub_status.html.twig',[
                'sub_statues'=>$agendaSubStatues
            ]);
    }


    /**
     * @Route("/horas", name="panel_agendador_horas", methods={"GET","POST"})
     */
    public function hora(Request $request, 
                        UsuarioRepository $usuarioRepository,
                        AgendaRepository $agendaRepository, 
                        UsuarioNoDisponibleRepository $usuarioNoDisponibleRepository):Response
    {
        //$agendas=$agendaRepository->findBy(['cuenta'=>$agenda->getCuenta()->getId(),'status'=>[4,5]]);
        $usuario=$request->query->get('abogado');
        $fecha=$request->query->get('fecha');
        $dia = date("N",strtotime($fecha));
        $abogado=$usuarioRepository->find($usuario);
        $horario_inicio="";
        $horario_fin="";
        $horario=array();
        $nodisponibles=array();
        $status=false;
        $sobrecupo="";
        $mensaje="Horas";
        $agendas="";

        switch($dia)
        {
            case 1: //Lunes
                if($abogado->getLunes()){
                    $horario_inicio=$abogado->getLunesStart();
                    $horario_fin=$abogado->getLunesEnd();
                    $status=true;
                }
            break;
            case 2: //Martes
                if($abogado->getMartes()){
                    $horario_inicio=$abogado->getMartesStart();
                    $horario_fin=$abogado->getMartesEnd();
                    $status=true;
                }
            break;
            case 3: //Miercoles
                if($abogado->getMiercoles()){
                    $horario_inicio=$abogado->getMiercolesStart();
                    $horario_fin=$abogado->getMiercolesEnd();
                    $status=true;
                }
            break;
            case 4: //Jueves
                if($abogado->getJueves()){
                    $horario_inicio=$abogado->getJuevesStart();
                    $horario_fin=$abogado->getJuevesEnd();
                    $status=true;
                }
            break;
            case 5: //Viernes
                if($abogado->getViernes()){
                    $horario_inicio=$abogado->getViernesStart();
                    $horario_fin=$abogado->getViernesEnd();
                    $status=true;
                }
            break;
            case 6: //Sabado
                if($abogado->getSabado()){
                    $horario_inicio=$abogado->getSabadoStart();
                    $horario_fin=$abogado->getSabadoEnd();
                    $status=true;
                }
            break;
            case 7: //Domingo
                if($abogado->getDomingo()){
                    $horario_inicio=$abogado->getDomingoStart();
                    $horario_fin=$abogado->getDomingoEnd();
                    $status=true;
                }
            break;
        }
        if($abogado->getUsuarioTipo()->getId()==6){
            $agendas=$agendaRepository->findByPers($usuario,null,null,'4,5,7,6,8,9,10', null,1," a.fechaAsignado >= '$fecha' and  a.fechaAsignado <= '$fecha 23:59:59'");
            $nodisponibleI=$usuarioNoDisponibleRepository->findByIntervalo($usuario,$fecha);
            $nodisponibleD=$usuarioNoDisponibleRepository->findByDinamico($usuario,$fecha);
            if($status){
                $horario_inicio=explode(":",$horario_inicio->format("G:i"));
                $horario_fin=explode(":",$horario_fin->format("G:i"));
            
                if(strtotime($fecha)>=strtotime(date('Y-m-d'))){
                    for($i=intval($horario_inicio[0]);$i<=intval($horario_fin[0]);$i++){

                        if($i==intval($horario_inicio[0]) && $horario_inicio[1]=="30"){
                            if(strtotime(date('Y-m-d H:i:00'))<strtotime($fecha." $i:30:00"))
                                $horario[]="$i:30";
         
                            continue;
                        }
                        if($i==intval($horario_fin[0]) && intval($horario_fin[1])==00){
                            if(strtotime(date('Y-m-d H:i:00'))<strtotime($fecha." $i:00:00"))
                                $horario[]="$i:00";
                            continue;
                        }
                        if(strtotime(date('Y-m-d H:i:00'))<strtotime($fecha." $i:00:00"))
                            $horario[]="$i:00";
                        if(strtotime(date('Y-m-d H:i:00'))<strtotime($fecha." $i:30:00"))
                            $horario[]="$i:30";
                        
                    }
                }else{
                    $mensaje="Sin horas";
                }
                
            }else{
                $mensaje="Sin horas";
            }

            foreach($nodisponibleI as $nd){
                $nd_inicio=explode(":",$nd->getHoraInicio()->format("G:i"));
                $nd_fin=explode(":",$nd->getHoraFin()->format("G:i"));
            
                for($i=intval($nd_inicio[0]);$i<=intval($nd_fin[0]);$i++){
                    if($i==intval($nd_inicio[0]) && $nd_inicio[1]=="30"){
                        $nodisponibles[]="$i:30";
                        continue;
                    }
                    if($i==intval($nd_fin[0]) && intval($nd_fin[1])==00){
                        $nodisponibles[]="$i:00";
                        continue;
                    }
                    $nodisponibles[]="$i:00";
                    $nodisponibles[]="$i:30";
                    
                }
            }
            foreach($nodisponibleD as $nd){
                $nd_inicio=explode(":",$nd->getHoraInicio()->format("G:i"));
                $nd_fin=explode(":",$nd->getHoraFin()->format("G:i"));
            
                for($i=intval($nd_inicio[0]);$i<=intval($nd_fin[0]);$i++){
                    if($i==intval($nd_inicio[0]) && $nd_inicio[1]=="30"){
                        $nodisponibles[]="$i:30";
                        continue;
                    }
                    if($i==intval($nd_fin[0]) && intval($nd_fin[1])==00){
                        $nodisponibles[]="$i:00";
                        continue;
                    }
                    $nodisponibles[]="$i:00";
                    $nodisponibles[]="$i:30";
                    
                }
            }

            $nodisponibles=array_unique($nodisponibles);

            if(strtotime($fecha)>=strtotime(date('Y-m-d'))){
                if($abogado->getSobrecupo()>0){
                    $agenda_sobrecupos=$agendaRepository->findByPers($usuario,null,null,'4,5,7,8,9,10', null,1," a.fechaAsignado = '$fecha 00:00:00'");
                    $cont=0;
                    foreach($agenda_sobrecupos as $agenda_sobrecupo){
                        $cont++;
                    }
                    if($cont<$abogado->getSobrecupo()){
                        $sobrecupo="Sobre Cupo";
                    }
                }
            }
        }else{
            $horario_inicio=explode(":","00:00");
            $horario_fin=explode(":","23:30");
        
            for($i=intval($horario_inicio[0]);$i<=intval($horario_fin[0]);$i++){
                if($i==intval($horario_inicio[0]) && $horario_inicio[1]=="30"){
                    $horario[]="$i:30";
                    continue;
                }
                if($i==intval($horario_fin[0]) && intval($horario_fin[1])==00){
                    $horario[]="$i:00";
                    continue;
                }
                $horario[]="$i:00";
                $horario[]="$i:30";
                
            }
        }  
        return $this->render('panel_agendador/horas.html.twig',[
            'agendas'=>$agendas,
            'horarios'=>$horario,
            'nodisponibles'=>$nodisponibles,
            'mensaje'=>$mensaje,
            'sobrecupo'=>$sobrecupo,
        ]);
    }
    /**
     * @Route("/{id}", name="panel_agendador_new", methods={"GET","POST"})
     */
    public function new(Agenda $agenda,
                        AgendaRepository $agendaRepository,
                        AgendaStatusRepository $agendaStatusRepository,
                        CuentaRepository $cuentaRepository,
                        UsuarioRepository $usuarioRepository,
                        ReunionRepository $reunionRepository,
                        ActualizaLead $actualizaLead,
                        AgendaContactoRepository $agendaContactoRepository,
                        AgendaSubStatusRepository $agendaSubStatusRepository,
                        ContratoRepository $contratoRepository,
                        TipoClienteRepository $tipoClienteRepository,
                        Request $request): Response
    {
        $this->denyAccessUnlessGranted('create','panel_agendador');

        //Buscar si tiene Campaña::::
        $user=$this->getUser();
        $actualizaLead->completar($agenda);
        $companias=$cuentaRepository->findByPers($user->getId(),1);
        $error='';
        $abortar=false;
        $subStatus=null;
        $user=$this->getUser();
        $pagina=$this->getDoctrine()->getRepository(ModuloPer::class)->findOneByName('panel_agendador',1);

        $tipoClientes = $tipoClienteRepository->findAll();
        $form = $this->createForm(AgendaType::class, $agenda);
        
        $form->handleRequest($request);

            $sql="";
            $sql1="";
            $telefono = $agenda->getTelefonoCliente();
            //$telefonoRecado= $form->getData()->getTelefonoRecadoCliente();
            //$canal=$request->request->get('cboCanal');
            if(trim($telefono)!=""){
                $sql=" (a.telefonoCliente='$telefono' ) ";
            }

            $registrar=true;
            //$agenda_existe=$contratoRepository->findByPersSinContr(null,null,null,null,null,3,$sql.$sql1);
            //$contrato_existe=$contratoRepository->findByPers(null,null,null,null,null, $sql.$sql1);
            //if(null != $contrato_existe){
            $agendas=$agendaRepository->findByPers(null,$user->getEmpresaActual(),null,null,null, 3,$sql.$sql1);
            
            if(null != $agendas ){
                $cont_agendas=0;
                foreach($agendas as $_agenda ){
                   
                    $contrato_terminado=$contratoRepository->findOneBy(['agenda'=>$_agenda, 'isFinalizado'=>true]);

                    if(null == $contrato_terminado){
                        $cont_agendas+=1;
                    }
                }
                if($cont_agendas>1){
                    $registrar=false;
                }
            }

            
            if(!$registrar){

                $error='<div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon fas fa-ban"></i> Advertencia!!</h5>
                Teléfono duplicado
                 </div>';

            }

        if ($form->isSubmitted() && $form->isValid()) {
            

           
            if($request->request->get('chkStatus')==5){
                if(null !== $request->request->get('cboAbogado')){
                    $abogado=$usuarioRepository->find($request->request->get('cboAbogado'));
                    $agenda->setAbogado($abogado);
                }
                if($request->request->get('cboHoras')=='00:00'){
                    $agenda_sobrecupos=$agendaRepository->findByPers($request->request->get('cboAbogado'),null,null,'4,5,7,8,9,10', null,1," a.fechaAsignado = '".$request->request->get('txtFechaAgendamiento')." 00:00:00'");
                    $cont=0;
                    $isAgendado=true;
                    foreach($agenda_sobrecupos as $agenda_sobrecupo){
                        $cont++;
                    }
                    if($cont<$abogado->getSobrecupo()){
                        //$sobrecupo="Sobre Cupo";
                        $isAgendado=null;
                    }
                    
                }else{
                    $isAgendado=$agendaRepository->findBy(['abogado'=>$request->request->get('cboAbogado'),
                                                    'fechaAsignado'=>new \DateTime($request->request->get('txtFechaAgendamiento')." ".$request->request->get('cboHoras').":00"),
                                                    'status'=>$request->request->get('chkStatus')]);
                }
                if(null != $isAgendado){
                    $error='<div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h5><i class="icon fas fa-ban"></i> Alguien se adelanto!!</h5>
                    En el momento que presionaste en Gestionar, otro agendador asignó al abogado '.$abogado->getNombre().' a las '.$request->request->get('txtFechaAgendamiento')." ".$request->request->get('cboHoras').":00".' hrs. Intenta otro Horario. 
                  </div>';
                    $abortar=true;
                }
            }
            
            
            /*if(null !== $request->request->get('cboAbogado')){
                $abogado=$usuarioRepository->find($request->request->get('cboAbogado'));
                $agenda->setAbogado($abogado);
            }*/
            

            switch($request->request->get('chkStatus')){
               
                case 9:
                    $agenda->setAbogado(null);
                    $subStatus=$agendaSubStatusRepository->find($request->request->get('cboSubStatus'));
                break;
               
            }

            if(null !== $request->request->get('cboAgendador')){
                $agenda->setAgendador($usuarioRepository->find($request->request->get('cboAgendador')));
            }
            
            if(null !== $request->request->get('txtCiudad')){
                $agenda->setCiudadCliente($request->request->get('txtCiudad'));
            }
            if(null !== $request->request->get('txtFechaAgendamiento')){
                $agenda->setFechaAsignado(new \DateTime($request->request->get('txtFechaAgendamiento')." ".$request->request->get('cboHoras').":00"));
            }
            if(null !== $request->request->get('txtMonto')){
                $agenda->setMonto($request->request->get('txtMonto'));
            }
            if(null !== $request->request->get('txtPagoActual')){
                $agenda->setPagoActual($request->request->get('txtPagoActual'));
            }
            if(null !== $request->request->get('cboReunion')){
                $agenda->setReunion($reunionRepository->find($request->request->get('cboReunion')));
            }
            if(null !== $request->request->get('cboContacto')){
                $agenda->setAgendaContacto($agendaContactoRepository->find($request->request->get('cboContacto')));
            }
            $agenda->setObservacion($request->request->get('txtObservacion'));

            $this->getDoctrine()->getManager()->flush();
            $entityManager = $this->getDoctrine()->getManager();
            
            if(!$abortar){
                $agenda->setStatus($agendaStatusRepository->find($request->request->get('chkStatus')));
                $observacion=new AgendaObservacion();
                $observacion->setAgenda($agenda);
                $observacion->setUsuarioRegistro($usuarioRepository->find($user->getId()));
                $observacion->setStatus($agendaStatusRepository->find($request->request->get('chkStatus')));
                $observacion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
                $observacion->setObservacion($request->request->get('txtObservacion'));
                $observacion->setSubStatus($subStatus);
                $entityManager->persist($observacion);
                $entityManager->flush();
                $entityManager->persist($agenda);
                $entityManager->flush();
                return $this->redirectToRoute('panel_agendador_index');
            }

        }
       
        return $this->render('panel_agendador/new.html.twig', [
            'agenda'=>$agenda,
            'error'=>$error,
            'form' => $form->createView(),
            'pagina'=>$pagina->getNombre().' | Gestionar',
            'companias'=>$companias,
            'statues'=>$agendaStatusRepository->findBy(['id'=>$agenda->getAgendador()->getUsuarioTipo()->getStatues()],['orden'=>'asc']),
            'tipoClientes'=>$tipoClientes
        ]);

    }
    /**
     * Guarda de inmediato el tipo de cliente elegido en el prospecto (Persona/Empresa/Convenio).
     * Se llama vía AJAX al cambiar el combo, para que el formulario "Contrata" (cargado
     * aparte, con otra petición) refleje el tipo de cliente vigente al momento de renderizarse.
     *
     * @Route("/{id}/tipo_cliente", name="panel_agendador_tipo_cliente", methods={"POST"})
     */
    public function tipoCliente(Agenda $agenda, Request $request, TipoClienteRepository $tipoClienteRepository): Response
    {
        $this->denyAccessUnlessGranted('create','panel_agendador');

        $tipoClienteId = $request->request->get('tipoCliente');
        $agenda->setTipoCliente($tipoClienteId ? $tipoClienteRepository->find($tipoClienteId) : null);
        $this->getDoctrine()->getManager()->flush();

        return new Response('ok');
    }

    /**
     * @Route("/{id}/engestion", name="panel_agendador_engestion", methods={"GET","POST"})
     */
    public function engestion(Agenda $agenda):Response
    {
        return $this->render('panel_agendador/engestion.html.twig');
    }

    

    /**
     * @Route("/{id}/abogados", name="panel_agendador_abogados", methods={"GET","POST"})
     */
    public function abogados(Agenda $agenda,Request $request,
                        UsuarioRepository $usuarioRepository,
                        ReunionRepository $reunionRepository,
                        AgendaContactoRepository $agendaContactoRepository):Response
    {
        return $this->render('panel_agendador/abogados.html.twig', [
            'abogados'=>$usuarioRepository->findByCuenta($agenda->getCuenta()->getId(),['usuarioTipo'=>6,'estado'=>1]),
            'agenda'=>$agenda,
            'contactos'=>$agendaContactoRepository->findAll(),
            'reuniones'=>$reunionRepository->findAll(),
            
        ]);
    }

    /**
     * @Route("/{id}/calendario", name="panel_agendador_calendario", methods={"GET","POST"})
     */
    public function calendario(Agenda $agenda, Request $request, UsuarioRepository $usuarioRepository,AgendaRepository $agendaRepository):Response
    {
        //$agendas=$agendaRepository->findBy(['cuenta'=>$agenda->getCuenta()->getId(),'status'=>[4,5]]);
        $usuario=$request->query->get('abogado');
        $usuario=$usuarioRepository->find($request->query->get('abogado'));
        
        $agendas=$agendaRepository->findByPers($usuario->getId(),$agenda->getCuenta()->getEmpresa()->getId(),null,'4,5,7,8,9,10', null,1);
        
        return $this->render('panel_agendador/calendario.html.twig',[
            'agendas'=>$agendas,
            'abogado'=>$usuario,
            
        ]);
    }
    
    /**
     * @Route("/{id}/edit", name="panel_agendador_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Agenda $agenda): Response
    {
        $this->denyAccessUnlessGranted('edit','panel_agendador');
        $form = $this->createForm(AgendaType::class, $agenda);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('panel_agendador_index');
        }

        return $this->render('panel_agendador/edit.html.twig', [
            'agenda' => $agenda,
            'form' => $form->createView(),
        ]);
    }
     
}
