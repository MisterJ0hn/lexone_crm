<?php

namespace App\Controller;

use App\Entity\Agenda;
use App\Entity\AgendaStatus;
use App\Entity\Cuenta;

use App\Form\AgendaType;
use App\Repository\AgendaRepository;
use App\Repository\AgendaStatusRepository;
use App\Entity\AgendaObservacion;
use App\Repository\UsuarioRepository;
use App\Repository\CuentaRepository;
use App\Repository\ContratoRepository;
use App\Repository\InfComisionCobradoresRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/agenda")
 */
class AgendaController extends AbstractController
{
    /**
     * @Route("/", name="agenda_index", methods={"GET"})
     */
    public function index(AgendaRepository $agendaRepository): Response
    {
        return $this->render('agenda/index.html.twig', [
            'agendas' => $agendaRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="agenda_new", methods={"GET","POST"})
     */
    public function new(Request $request,
                        AgendaStatusRepository $agendaStatusRepository,
                        CuentaRepository $cuentaRepository,
                        UsuarioRepository $usuarioRepository,
                        AgendaRepository $agendaRepository,
                        ContratoRepository $contratoRepository
                        ): Response
    {
        $this->denyAccessUnlessGranted('create','agenda');
        $user=$this->getUser();
        $agenda = new Agenda();
        $error='';
        $error_toast="";
            if($request->query->get('msg')=='exito'){
                $error_toast="Toast.fire({
                    icon: 'success',
                    title: 'Registro grabado con exito'
                })";
            }
        $agenda->setStatus($agendaStatusRepository->find(1));
        $agenda->setFechaCarga(new \DateTime(date('Y-m-d H:i:s')));
        $agenda->setEmpresa($user->getEmpresaActual());
        $form = $this->createForm(AgendaType::class, $agenda);
        //$form->add('campania');
        $form->add('ciudadCliente');
        $form->handleRequest($request);

        switch($user->getUsuarioTipo()->getId()){
            case 1:
                $cuentas=$cuentaRepository->findBy(['empresa'=>$user->getEmpresaActual()]);
            break;
            default:
                $cuentas=$cuentaRepository->findBy(['empresa'=>$user->getEmpresaActual()]);
                //$cuentas=$cuentaRepository->findByPers($usuarioRepository->find($user->getId()));
                break;
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $sql="";
            $sql1="";
            $telefono = $form->getData()->getTelefonoCliente();
            $telefonoRecado= $form->getData()->getTelefonoRecadoCliente();
            $canal=$request->request->get('cboCanal');
            if(trim($telefono)!=""){
                $sql=" (a.telefonoCliente='$telefono' or a.telefonoRecadoCliente='$telefono' ) ";
            }

            if(trim($telefonoRecado)!=""){
                $sql1=" and (a.telefonoCliente='$telefonoRecado' or a.telefonoRecadoCliente='$telefonoRecado' ) ";
            }

            $registrar=true;
            //$agenda_existe=$contratoRepository->findByPersSinContr(null,null,null,null,null,3,$sql.$sql1);
            //$contrato_existe=$contratoRepository->findByPers(null,null,null,null,null, $sql.$sql1);
            //if(null != $contrato_existe){
            $agendas=$agendaRepository->findByPers(null,null,null,null,null,3,$sql.$sql1);
            
            if(null != $agendas ){
                $cont_agendas=0;
                foreach($agendas as $_agenda ){
                   
                    $contrato_terminado=$contratoRepository->findOneBy(['agenda'=>$_agenda,'empresa'=>$user->getEmpresaActual(), 'isFinalizado'=>true]);

                    if(null == $contrato_terminado){
                        $cont_agendas+=1;
                    }
                }
                if($cont_agendas>0){
                    $registrar=false;
                }
            }

            
            if($registrar){
                $cuenta=$request->request->get('cboCuenta');
                $usuario=$request->request->get('cboAgendador');
                $agenda->setCuenta($cuentaRepository->find($cuenta));
                $agenda->setAgendador($usuarioRepository->find($usuario));
                $agenda->setEmpresa($user->getEmpresaActual());
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

                return $this->redirectToRoute('agenda_new',['msg'=>'exito']);
            }else{
                $error='<div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon fas fa-ban"></i> Error!!</h5>
                   Este lead ya se encuentra en agenda, favor verifique la información
                 </div>';
            }
           
        }

        return $this->render('agenda/new.html.twig', [
            'agenda' => $agenda,
            'form' => $form->createView(),
            'cuentas'=>$cuentas,
            'pagina'=>"Carga Manual",
            'error'=>$error,
            'error_toast'=>$error_toast,
        ]);
    }

    /**
     * @Route("/{id}", name="agenda_show", methods={"GET"})
     */
    public function show(Agenda $agenda): Response
    {
        return $this->render('agenda/show.html.twig', [
            'agenda' => $agenda,
        ]);
    }
    /**
     * @Route("/resumenagendadores", name="agenda_resumenagendadores", methods={"GET","POST"})
     */
    public function resumenagendadores(Request $request,$agendaStatus,String $fechainicio, String $fechafin,$compania,$filtro,$totalStatus,$tipoFecha,$agendador, AgendaRepository $agendaRepository): Response
    {
        $user=$this->getUser();
        switch($tipoFecha){
            case 0:
                $fecha="a.fechaCarga between '$fechainicio' and '$fechafin 23:59:59'" ;
                break;
            case 1:
                $fecha="a.fechaAsignado between '$fechainicio' and '$fechafin 23:59:59'" ;
                break;
            case 2:
                $fecha="a.fechaContrato between '$fechainicio' and '$fechafin 23:59:59'" ;
                break;
            default:
                $fecha="a.fechaCarga between '$fechainicio' and '$fechafin 23:59:59'" ;
                break;
        }
        //$fecha="a.fechaCarga between '$fechainicio' and '$fechafin 23:59:59'" ;
        $nombre_status="";
        if(null != $agendaStatus){
            $status=$this->getDoctrine()->getRepository(AgendaStatus::class)->find($agendaStatus);
            $nombre_status=$status->getNombre();
        }
        //$queryresumen=$agendaRepository->findByAgendGroup(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,null,$fecha);   
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 4:
            case 1:
                $queryresumen=$agendaRepository->findByAgendGroup($agendador,$user->getEmpresaActual(),$compania,$agendaStatus,$filtro,0,$fecha);   
            break;
            default:
                $queryresumen=$agendaRepository->findByAgendGroup($user->getId(),$user->getEmpresaActual(),$compania,$agendaStatus,$filtro,0,$fecha);   
            break;
        }
        
        return $this->render('agenda/_resumenagendadores.html.twig',[
            'agendadores'=>$queryresumen,
            'total'=>$totalStatus,
            'nombre_status'=>$nombre_status,
        ]);
    }
    /**
     * @Route("/resumenabogados", name="agenda_resumenabogados", methods={"GET","POST"})
     */
    public function resumenabogados(Request $request, $agendaStatus,String $fechainicio, String $fechafin,$compania,$filtro,$totalStatus,$tipoFecha,$abogado, AgendaRepository $agendaRepository): Response
    {
        $user=$this->getUser();

        switch($tipoFecha){
            case 0:
                $fecha="a.fechaCarga between '$fechainicio' and '$fechafin 23:59:59'" ;
                break;
            case 1:
                $fecha="a.fechaAsignado between '$fechainicio' and '$fechafin 23:59:59'" ;
                break;
            case 2:
                $fecha="a.fechaContrato between '$fechainicio' and '$fechafin 23:59:59'" ;
                break;
            default:
                $fecha="a.fechaCarga between '$fechainicio' and '$fechafin 23:59:59'" ;
                break;
        }
        //$fecha="a.fechaAsignado between '$fechainicio' and '$fechafin 23:59:59'" ;
        $nombre_status="";
        if(stristr($agendaStatus, ',')===False){
            $status=$this->getDoctrine()->getRepository(AgendaStatus::class)->find($agendaStatus);
            $nombre_status=$status->getNombre();
        }
        
        //$queryresumen=$agendaRepository->findByAgendGroup(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,null,$fecha);   
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 1:
            case 4:
                $queryresumen=$agendaRepository->findByAgendGroup($abogado,$user->getEmpresaActual(),$compania,$agendaStatus,$filtro,1,$fecha);   
        
            break;
            default:
                $queryresumen=$agendaRepository->findByAgendGroup($user->getId(),$user->getEmpresaActual(),$compania,$agendaStatus,$filtro,1,$fecha);   
            break;
        }
        return $this->render('agenda/_resumenabogados.html.twig',[
            'agendadores'=>$queryresumen,
            'total'=>$totalStatus,
            'nombre_status'=>$nombre_status,
        ]);
    }
    /**
     * @Route("/resumencobradores", name="agenda_resumencobradores", methods={"GET","POST"})
     */
    public function resumencobradores(int $status,int $total, float $montoTotal, InfComisionCobradoresRepository $infComisionCobradoresRepository): Response
    {
        $user=$this->getUser();
        switch ($status) {
            case 1:
                $nombre_status="Gestiones";
                break;
            case 2:
                $nombre_status="Recupero";
                break;
        
            default:
                # code...
                break;
        }
        
        $queryresumen=$infComisionCobradoresRepository->totalesUsuario($user->getId());   
        
        return $this->render('agenda/_resumencobradores.html.twig',[
            'informes'=>$queryresumen,
            'status'=>$status,
            'total'=>$total,
            'montoTotal'=>$montoTotal,
            'nombre_status'=>$nombre_status,
        ]);
    }

    /**
     * @Route("/resumencierre", name="agenda_resumencierre", methods={"GET","POST"})
     */
    public function resumencierre(float $montoTotal,string $fechaInicio,string $fechaFin,int $status, ContratoRepository $contratoRepository): Response
    {
      

        $user=$this->getUser();


        $fecha="p.fechaPago between '$fechaInicio' and '$fechaFin 23:59:59' ";
        $fecha.=" and DATEDIFF(p.fechaPago, cuo.fechaPago)>=30 and cuo.numero=1 and cuo.monto<=cuo.pagado";
        
        switch($user->getUsuarioTipo()->getId()){
            case 1://tramitador
            case 3:
            case 4:
            case 8:
            case 12:
                $queryresumen=$contratoRepository->findByCerradoresGroup(null,null,null,$fecha);
            break;
            case 6: //abogado
                $queryresumen=$contratoRepository->findByCerradoresGroup($user->getId(),null,null,$fecha);
            break;
            default:
                $queryresumen=$contratoRepository->findByCerradoresGroup($user->getId(),null,null,$fecha);
            break;
        }
           
        
        return $this->render('agenda/_resumencierre.html.twig',[
            'informes'=>$queryresumen,
            'total'=>$montoTotal,
            'status'=>$status
        
            
        ]);
    }

    /**
     * @Route("/{id}/edit", name="agenda_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Agenda $agenda): Response
    {
        $form = $this->createForm(AgendaType::class, $agenda);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('agenda_index');
        }

        return $this->render('agenda/edit.html.twig', [
            'agenda' => $agenda,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/{id}/agendadores", name="agenda_agendadores", methods={"GET","POST"})
     */
    public function agendadores(Request $request, Cuenta $cuenta,UsuarioRepository $usuarioRepository): Response
    {
        $user=$this->getUser();
        $agendador=0;
        
        if($request->query->get('agendador')){
            
            $agendador=$request->query->get('agendador');
        }
        if($user->getUsuarioTipo()->getId()>4){
            $agendadores= $usuarioRepository->findBy(['id'=>$user->getId()]);
        }else{
            $agendadores= $usuarioRepository->findBy(['usuarioTipo'=>5,'estado'=>1]);
            
               
        }

        return $this->render('agenda/_agendadores.html.twig', [
            'agendadores' => $agendadores,
            'agendador_id'=>$agendador,
        ]);
    }

    /**
     * @Route("/{id}", name="agenda_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Agenda $agenda): Response
    {
        if ($this->isCsrfTokenValid('delete'.$agenda->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($agenda);
            $entityManager->flush();
        }

        return $this->redirectToRoute('agenda_index');
    }
}
