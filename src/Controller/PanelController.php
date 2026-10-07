<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\AgendaRepository;
use App\Repository\PagoRepository;

#[Route("/panel")]
class PanelController extends AbstractController
{
    #[Route("/", name: "panel_index")]
    public function index(AgendaRepository $agendaRepository, 
                          PagoRepository $pagoRepository): Response
    {
        
        $this->denyAccessUnlessGranted('view','panel');
        $user=$this->getUser();
        $otrosL='';
        $otrosA='';
        $otrosC='';
        $otrosPA='';
        $otrosPP='';
        $fecha_actual_final=date('Y-m-d');
        $fecha_actual_inicial=date('Y-m').'-01';

        //Leads Fecha Carga
        $fecha_5_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 5 month"));
        $fecha_5_final = date('Y-m-d',strtotime($fecha_actual_final." - 5 month"));
        $otrosL="  a.fechaCarga between '$fecha_5_inicial' and '$fecha_5_final ".date("H:i:s")."'";
        $leads5=$agendaRepository->findByPersGroupPeriodo($user->getId(),$user->getEmpresaActual(),null,null,null,null, $otrosL, 'a.fechaCarga',true);
        $valorLeads=0;
        if($leads5!=null){
            $valorLeads=$leads5[0]['valor'];
        }
        $leads[]=$valorLeads;

        $fecha_4_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 4 month"));
        $fecha_4_final = date('Y-m-d',strtotime($fecha_actual_final." - 4 month"));
        $otrosL=" a.fechaCarga between '$fecha_4_inicial' and '$fecha_4_final ".date("H:i:s")."' ";
        $leads4=$agendaRepository->findByPersGroupPeriodo($user->getId(),$user->getEmpresaActual(),null,null,null,null, $otrosL, 'a.fechaCarga',true);
        $valorLeads=0;
        if($leads4!=null){
            $valorLeads=$leads4[0]['valor'];
        }
        $leads[]=$valorLeads;

        $fecha_3_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 3 month"));
        $fecha_3_final = date('Y-m-d',strtotime($fecha_actual_final." - 3 month"));
        $otrosL=" a.fechaCarga between '$fecha_3_inicial' and '$fecha_3_final ".date("H:i:s")."' ";
        $leads3=$agendaRepository->findByPersGroupPeriodo($user->getId(),$user->getEmpresaActual(),null,null,null,null, $otrosL, 'a.fechaCarga',true);
        $valorLeads=0;
        if($leads3!=null){
            $valorLeads=$leads3[0]['valor'];
        }
        $leads[]=$valorLeads;

        $fecha_2_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 2 month"));
        $fecha_2_final = date('Y-m-d',strtotime($fecha_actual_final." - 2 month"));
        $otrosL=" a.fechaCarga between '$fecha_2_inicial' and '$fecha_2_final ".date("H:i:s")."' ";
        $leads2=$agendaRepository->findByPersGroupPeriodo($user->getId(),null,$user->getEmpresaActual(),null,null,null, $otrosL, 'a.fechaCarga',true);
        $valorLeads=0;
        if($leads2!=null){
            $valorLeads=$leads2[0]['valor'];
        }
        $leads[]=$valorLeads;

        $fecha_1_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 1 month"));
        $fecha_1_final = date('Y-m-d',strtotime($fecha_actual_final." - 1 month"));
        $otrosL=" a.fechaCarga between '$fecha_1_inicial' and '$fecha_1_final ".date("H:i:s")."' ";
        $leads1=$agendaRepository->findByPersGroupPeriodo($user->getId(),$user->getEmpresaActual(),null,null,null,null, $otrosL, 'a.fechaCarga',true);
        $valorLeads=0;
        if($leads1!=null){
            $valorLeads=$leads1[0]['valor'];
        }
        $leads[]=$valorLeads;

        $otrosL=" a.fechaCarga between '$fecha_actual_inicial' and '$fecha_actual_final ".date("H:i:s")."' ";
        $leads0=$agendaRepository->findByPersGroupPeriodo($user->getId(),$user->getEmpresaActual(),null,null,null,null, $otrosL, 'a.fechaCarga',true);
        $valorLeads=0;
        if($leads0!=null){
            $valorLeads=$leads0[0]['valor'];
        }
        $leads[]=$valorLeads;
        //var_dump($leads);
        //Leads Fecha Carga

        //Agendo Fecha Asignado
        //$otrosA.=" a.fechaAsignado between '$fecha_actual_inicial' and '$fecha_actual_final ".date("H:i:s")."' ";
        
        $fecha_5A_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 5 month"));
        $fecha_5A_final = date('Y-m-d',strtotime($fecha_actual_final." - 5 month"));
        $otrosA=" a.fechaAsignado between '$fecha_5A_inicial' and '$fecha_5A_final ".date("H:i:s")."' ";
        $agenda5=$agendaRepository->findByPersGroupPeriodo($user->getId(),$user->getEmpresaActual(),null,null,null,null, $otrosA, 'a.fechaAsignado',true);
        $valorAgenda=0;
        if($agenda5!=null){
            $valorAgenda=$agenda5[0]['valor'];
        }
        $agenda[]=$valorAgenda;

        $fecha_4A_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 4 month"));
        $fecha_4A_final = date('Y-m-d',strtotime($fecha_actual_final." - 4 month"));
        $otrosA=" a.fechaAsignado between '$fecha_4A_inicial' and '$fecha_4A_final ".date("H:i:s")."' ";
        $agenda4=$agendaRepository->findByPersGroupPeriodo($user->getId(),$user->getEmpresaActual(),null,null,null,null, $otrosA, 'a.fechaAsignado',true);
        $valorAgenda=0;
        if($agenda4!=null){
            $valorAgenda=$agenda4[0]['valor'];
        }
        $agenda[]=$valorAgenda;

        $fecha_3A_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 3 month"));
        $fecha_3A_final = date('Y-m-d',strtotime($fecha_actual_final." - 3 month"));
        $otrosA=" a.fechaAsignado between '$fecha_3A_inicial' and '$fecha_3A_final ".date("H:i:s")."' ";
        $agenda3=$agendaRepository->findByPersGroupPeriodo($user->getId(),$user->getEmpresaActual(),null,null,null,null, $otrosA, 'a.fechaAsignado',true);
        $valorAgenda=0;
        if($agenda3!=null){
            $valorAgenda=$agenda3[0]['valor'];
        }
        $agenda[]=$valorAgenda;

        $fecha_2A_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 2 month"));
        $fecha_2A_final = date('Y-m-d',strtotime($fecha_actual_final." - 2 month"));
        $otrosA=" a.fechaAsignado between '$fecha_2A_inicial' and '$fecha_2A_final ".date("H:i:s")."' ";
        $agenda2=$agendaRepository->findByPersGroupPeriodo($user->getId(),$user->getEmpresaActual(),null,null,null,null, $otrosA, 'a.fechaAsignado',true);
        $valorAgenda=0;
        if($agenda2!=null){
            $valorAgenda=$agenda2[0]['valor'];
        }
        $agenda[]=$valorAgenda;

        $fecha_1A_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 1 month"));
        $fecha_1A_final = date('Y-m-d',strtotime($fecha_actual_final." - 1 month"));
        $otrosA=" a.fechaAsignado between '$fecha_1A_inicial' and '$fecha_1A_final ".date("H:i:s")."' ";
        $agenda1=$agendaRepository->findByPersGroupPeriodo($user->getId(),$user->getEmpresaActual(),null,null,null,null, $otrosA, 'a.fechaAsignado',true);
        $valorAgenda=0;
        if($agenda1!=null){
            $valorAgenda=$agenda1[0]['valor'];
        }
        $agenda[]=$valorAgenda;

        $otrosA=" a.fechaAsignado between '$fecha_actual_inicial' and '$fecha_actual_final ".date("H:i:s")."' ";
        $agenda0=$agendaRepository->findByPersGroupPeriodo($user->getId(),$user->getEmpresaActual(),null,null,null,null, $otrosA, 'a.fechaAsignado',true);
        $valorAgenda=0;
        if($agenda0!=null){
            $valorAgenda=$agenda0[0]['valor'];
        }
        $agenda[]=$valorAgenda;
        //Agendo Fecha Asignado

        //Agendo Fecha Contrato
        $fecha_5C_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 5 month"));
        $fecha_5C_final = date('Y-m-d',strtotime($fecha_actual_final." - 5 month"));
        $otrosC=" a.fechaContrato between '$fecha_5C_inicial' and '$fecha_5C_final ".date("H:i:s")."' ";
        $cerrado5=$agendaRepository->findByPersGroupPeriodo($user->getId(),$user->getEmpresaActual(),null,'7,12,13,14,15',null,null, $otrosC, 'a.fechaContrato',true); 
        $valorCerrado5=0;
        if($cerrado5!=null){
            $valorCerrado5=$cerrado5[0]['valor'];
        }

        $fecha_4C_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 4 month"));
        $fecha_4C_final = date('Y-m-d',strtotime($fecha_actual_final." - 4 month"));
        $otrosC=" a.fechaContrato between '$fecha_4C_inicial' and '$fecha_4C_final ".date("H:i:s")."' ";
        $cerrado4=$agendaRepository->findByPersGroupPeriodo($user->getId(),$user->getEmpresaActual(),null,'7,12,13,14,15',null,null, $otrosC, 'a.fechaContrato',true);
        $valorCerrado4=0;
        if($cerrado4!=null){
            $valorCerrado4=$cerrado4[0]['valor'];
        }

        $fecha_3C_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 3 month"));
        $fecha_3C_final = date('Y-m-d',strtotime($fecha_actual_final." - 3 month"));
        $otrosC=" a.fechaContrato between '$fecha_3C_inicial' and '$fecha_3C_final ".date("H:i:s")."' ";
        $cerrado3=$agendaRepository->findByPersGroupPeriodo($user->getId(),$user->getEmpresaActual(),null,'7,12,13,14,15',null,null, $otrosC, 'a.fechaContrato',true);
        $valorCerrado3=0;
        if($cerrado3!=null){
            $valorCerrado3=$cerrado3[0]['valor'];
        }

        $fecha_2C_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 2 month"));
        $fecha_2C_final = date('Y-m-d',strtotime($fecha_actual_final." - 2 month"));
        $otrosC=" a.fechaContrato between '$fecha_2C_inicial' and '$fecha_2C_final ".date("H:i:s")."' ";
        $cerrado2=$agendaRepository->findByPersGroupPeriodo($user->getId(),$user->getEmpresaActual(),null,'7,12,13,14,15',null,null, $otrosC, 'a.fechaContrato',true);
        $valorCerrado2=0;
        if($cerrado2!=null){
            $valorCerrado2=$cerrado2[0]['valor'];
        }

        $fecha_1C_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 1 month"));
        $fecha_1C_final = date('Y-m-d',strtotime($fecha_actual_final." - 1 month"));
        $otrosC=" a.fechaContrato between '$fecha_1C_inicial' and '$fecha_1C_final ".date("H:i:s")."' ";
        $cerrado1=$agendaRepository->findByPersGroupPeriodo($user->getId(),$user->getEmpresaActual(),null,'7,12,13,14,15',null,null, $otrosC, 'a.fechaContrato',true);
        $valorCerrado1=0;
        if($cerrado1!=null){
            $valorCerrado1=$cerrado1[0]['valor'];
        }

        $otrosC=" a.fechaContrato between '$fecha_actual_inicial' and '$fecha_actual_final ".date("H:i:s")."' ";
        $cerrado0=$agendaRepository->findByPersGroupPeriodo($user->getId(),$user->getEmpresaActual(),null,'7,12,13,14,15',null,null, $otrosC, 'a.fechaContrato',true);
        $valorCerrado0=0;
        if($cerrado0!=null){
            $valorCerrado0=$cerrado0[0]['valor'];
        }

        $cerrado=[
            $valorCerrado5,
            $valorCerrado4,
            $valorCerrado3,
            $valorCerrado2,
            $valorCerrado1,
            $valorCerrado0
        ]  ;
        //Agendo Fecha Contrato

        //Pagos
        //Año Actual
        $fecha_5PA_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 5 month"));
        $fecha_5PA_final = date('Y-m-d',strtotime($fecha_actual_final." - 5 month"));
        $otrosPA="  p.fechaPago between '$fecha_5PA_inicial' and '$fecha_5PA_final ".date("H:i:s")."' ";
        $pagos5PA=$pagoRepository->findByPersCountPeriodoPagos(null,$user->getEmpresaActual(),null,null, $otrosPA);
        $valorPagos=0;
        if($pagos5PA['valor']!=null){
            $valorPagos=$pagos5PA['valor'];
        }
        $pagosA[]=$valorPagos;
        //var_dump($pagosA);

        $fecha_4PA_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 4 month"));
        $fecha_4PA_final = date('Y-m-d',strtotime($fecha_actual_final." - 4 month"));
        $otrosPA="  p.fechaPago between '$fecha_4PA_inicial' and '$fecha_4PA_final ".date("H:i:s")."' ";
        $pagos4PA=$pagoRepository->findByPersCountPeriodoPagos(null,$user->getEmpresaActual(),null,null, $otrosPA);
        $valorPagos=0;
        if($pagos4PA['valor']!=null){
            $valorPagos=$pagos4PA['valor'];
        }
        $pagosA[]=$valorPagos;
        //var_dump($pagosA);

        $fecha_3PA_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 3 month"));
        $fecha_3PA_final = date('Y-m-d',strtotime($fecha_actual_final." - 3 month"));
        $otrosPA="  p.fechaPago between '$fecha_3PA_inicial' and '$fecha_3PA_final ".date("H:i:s")."' ";
        $pagos3PA=$pagoRepository->findByPersCountPeriodoPagos(null,$user->getEmpresaActual(),null,null, $otrosPA);
        $valorPagos=0;
        if($pagos3PA['valor']!=null){
            $valorPagos=$pagos3PA['valor'];
        }
        $pagosA[]=$valorPagos;
        //var_dump($pagosA);

        $fecha_2PA_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 2 month"));
        $fecha_2PA_final = date('Y-m-d',strtotime($fecha_actual_final." - 2 month"));
        $otrosPA="  p.fechaPago between '$fecha_2PA_inicial' and '$fecha_2PA_final ".date("H:i:s")."' ";
        $pagos2PA=$pagoRepository->findByPersCountPeriodoPagos(null,$user->getEmpresaActual(),null,null, $otrosPA);
        $valorPagos=0;
        if($pagos2PA['valor']!=null){
            $valorPagos=$pagos2PA['valor'];
        }
        $pagosA[]=$valorPagos;
        //var_dump($pagosA);

        $fecha_1PA_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 1 month"));
        $fecha_1PA_final = date('Y-m-d',strtotime($fecha_actual_final." - 1 month"));
        $otrosPA="  p.fechaPago between '$fecha_1PA_inicial' and '$fecha_1PA_final ".date("H:i:s")."' ";
        $pagos1PA=$pagoRepository->findByPersCountPeriodoPagos(null,$user->getEmpresaActual(),null,null, $otrosPA);
        $valorPagos=0;
        if($pagos1PA['valor']!=null){
            $valorPagos=$pagos1PA['valor'];
        }
        $pagosA[]=$valorPagos;
        //var_dump($pagosA);

        $otrosPA="  p.fechaPago between '$fecha_actual_inicial' and '$fecha_actual_final ".date("H:i:s")."' ";
        $pagos0PA=$pagoRepository->findByPersCountPeriodoPagos(null,$user->getEmpresaActual(),null,null, $otrosPA);
        $valorPagos=0;
        if($pagos0PA['valor']!=null){
            $valorPagos=$pagos0PA['valor'];
        }
        $pagosA[]=$valorPagos;
        //var_dump($pagosA);
        //Año Actual

        //Año Pasado
        $fecha_5PP_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 17 month"));
        $fecha_5PP_final = date('Y-m-d',strtotime($fecha_actual_final." - 17 month"));
        $otrosPP="  p.fechaPago between '$fecha_5PP_inicial' and '$fecha_5PP_final ".date("H:i:s")."' ";
        $pagos5PP=$pagoRepository->findByPersCountPeriodoPagos(null,$user->getEmpresaActual(),null,null, $otrosPP);
        $valorPagosP=0;
        if($pagos5PP['valor']!=null){
            $valorPagosP=$pagos5PP['valor'];
        }
        $pagosP[]=$valorPagosP;
        //var_dump($pagosP);

        $fecha_4PP_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 16 month"));
        $fecha_4PP_final = date('Y-m-d',strtotime($fecha_actual_final." - 16 month"));
        $otrosPP="  p.fechaPago between '$fecha_4PP_inicial' and '$fecha_4PP_final ".date("H:i:s")."' ";
        $pagos4PP=$pagoRepository->findByPersCountPeriodoPagos(null,$user->getEmpresaActual(),null,null, $otrosPP);
        $valorPagosP=0;
        if($pagos4PP['valor']!=null){
            $valorPagosP=$pagos4PP['valor'];
        }
        $pagosP[]=$valorPagosP;
        //var_dump($pagosP);

        $fecha_3PP_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 15 month"));
        $fecha_3PP_final = date('Y-m-d',strtotime($fecha_actual_final." - 15 month"));
        $otrosPP="  p.fechaPago between '$fecha_3PP_inicial' and '$fecha_3PP_final ".date("H:i:s")."' ";
        $pagos3PP=$pagoRepository->findByPersCountPeriodoPagos(null,$user->getEmpresaActual(),null,null, $otrosPP);
        $valorPagosP=0;
        if($pagos3PP['valor']!=null){
            $valorPagosP=$pagos3PP['valor'];
        }
        $pagosP[]=$valorPagosP;
        //var_dump($pagosP);

        $fecha_2PP_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 14 month"));
        $fecha_2PP_final = date('Y-m-d',strtotime($fecha_actual_final." - 14 month"));
        $otrosPP="  p.fechaPago between '$fecha_2PP_inicial' and '$fecha_2PP_final ".date("H:i:s")."' ";
        $pagos2PP=$pagoRepository->findByPersCountPeriodoPagos(null,$user->getEmpresaActual(),null,null, $otrosPP);
        $valorPagosP=0;
        if($pagos2PP['valor']!=null){
            $valorPagosP=$pagos2PP['valor'];
        }
        $pagosP[]=$valorPagosP;
        //var_dump($pagosP);

        $fecha_1PP_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 13 month"));
        $fecha_1PP_final = date('Y-m-d',strtotime($fecha_actual_final." - 13 month"));
        $otrosPP="  p.fechaPago between '$fecha_1PP_inicial' and '$fecha_1PP_final ".date("H:i:s")."' ";
        $pagos1PP=$pagoRepository->findByPersCountPeriodoPagos(null,$user->getEmpresaActual(),null,null, $otrosPP);
        $valorPagosP=0;
        if($pagos1PP['valor']!=null){
            $valorPagosP=$pagos1PP['valor'];
        }
        $pagosP[]=$valorPagosP;
        //var_dump($pagosP);

        $fecha_0PP_inicial = date('Y-m-d',strtotime($fecha_actual_inicial." - 12 month"));
        $fecha_0PP_final = date('Y-m-d',strtotime($fecha_actual_final." - 12 month"));
        $otrosPP="  p.fechaPago between '$fecha_0PP_inicial' and '$fecha_0PP_final ".date("H:i:s")."' ";
        $pagos0PP=$pagoRepository->findByPersCountPeriodoPagos(null,$user->getEmpresaActual(),null,null, $otrosPP);
        $valorPagosP=0;
        if($pagos0PP['valor']!=null){
            $valorPagosP=$pagos0PP['valor'];
        }
        $pagosP[]=$valorPagosP;
        //var_dump($pagosP);
        //Año Pasado
        //Pagos

        //ejmplo de llenado array
        //$array=[
            //'valor1',
            //'valor2',
            //'valor3'
        //];

        //echo "array 1";
        //var_dump($array);

        //$array2[]='valor1';
        //$array2[]='valor2';
        //$array2[]='valor3';

        //echo "array 2";
        //var_dump($array2);
        //ejmplo de llenado array
        

        //$leads=$agendaRepository->findByPersGroupPeriodo(null,null,null,null,null,null, $otrosL, 'a.fechaCarga');
        $agendado=$agendaRepository->findByPersGroupPeriodo(null,$user->getEmpresaActual(),null,'3,4,5,6,7,8,12,13,14,15',null,null, $otrosA, 'a.fechaAsignado');
       
        return $this->render('panel/index.html.twig', [
            'controller_name' => 'PanelController',
            'PeriodoActual'=>$fecha_actual_inicial,
            'PeriodoFinal'=>$fecha_actual_final,
            'Periodo5'=>$fecha_5_inicial,
            'Periodo4'=>$fecha_4_inicial,
            'Periodo3'=>$fecha_3_inicial,
            'Periodo2'=>$fecha_2_inicial,
            'Periodo1'=>$fecha_1_inicial,
            'leads'=>$leads,
            'agendas'=>$agenda,
            'cerrado'=>$cerrado,
            'pagosA'=>$pagosA,
            'pagosP'=>$pagosP,
        ]);
    }
}
