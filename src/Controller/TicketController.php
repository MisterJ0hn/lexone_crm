<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Contrato;
use App\Entity\ModuloPer;
use App\Entity\Ticket;
use App\Entity\TicketEstado;
use App\Entity\TicketHistorial;
use App\Form\TicketType;
use App\Repository\ContratoRepository;
use App\Repository\CuentaRepository;
use App\Repository\EmpresaRepository;
use App\Repository\TicketEstadoRepository;
use App\Repository\TicketHistorialRepository;
use App\Repository\TicketRepository;
use App\Repository\UsuarioRepository;
use App\Repository\UsuarioTipoRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/ticket")]
class TicketController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }
    #[Route("/", name: "app_ticket_index", methods: ["GET"])]
    public function index(TicketRepository $ticketRepository,
                        PaginatorInterface $paginator,
                        Request $request,
                        CuentaRepository $cuentaRepository                        
                        ): Response
    {
        $this->denyAccessUnlessGranted('view','ticket');
        $user=$this->getUser();

        $pagina=$this->entityManager->getRepository(ModuloPer::class)->findOneByName('ticket',$user->getEmpresaActual());
        
        
        $statues='1';
        $statuesgroup="1,2,4";
        $status=null;
        $folio='';
        $compania=null;
        $companias=null;
        $tipo_fecha=0;
        $origen=0;
        $filtro=null;
        $otros=null;

        if(null !== $request->query->get('bFiltro') && trim($request->query->get('bFiltro'))!=''){
            $filtro=$request->query->get('bFiltro');
            $otros=" (c.nombre like '%$filtro%' or c.email like '%$filtro%' or c.telefono like '%$filtro%') and ";
        }

        if(null !== $request->query->get('bStatus') && trim($request->query->get('bStatus'))!=''){
            $status=$request->query->get('bStatus');
            $statuesgroup=$status;
        }
        
        if(null !== $request->query->get('bCompania') && $request->query->get('bCompania')!=0){
            $compania=$request->query->get('bCompania');
        }
        

        if(null !== $request->query->get('bFecha')){
            $aux_fecha=explode(" - ",$request->query->get('bFecha'));
            $dateInicio=$aux_fecha[0];
            $dateFin=$aux_fecha[1];
        }else{
            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30*24);//2 años atrás
            $dateFin=date('Y-m-d');
        }
        if(null !== $request->query->get('bTipofecha') ){
            $tipo_fecha=$request->query->get('bTipofecha');
        }
        switch($tipo_fecha){
            case 0:
                $fecha=" t.fechaNuevo between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
            case 1:
                $fecha=" t.fechaAsignado between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
            case 2:
                $fecha=" t.fechaRespuesta between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
            case 3:
                $fecha=" t.fechaCierre between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
            default:
                $fecha=" t.fechaNuevo between '$dateInicio' and '$dateFin 23:59:59'" ;
                break;
        }
        $otros.=$fecha;

        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 4:
            case 1:
            case 8:
                $origen=2;
                $query=$ticketRepository->findByPers($user->getId(),$user->getEmpresaActual(),$statuesgroup,3,$otros);
                $queryresumen=$ticketRepository->findByPersGroup(null,$user->getEmpresaActual(),$statuesgroup,null,3,$otros);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;
            default:
                $query=$ticketRepository->findByPers($user->getId(),$user->getEmpresaActual(),$statuesgroup,$origen,$otros);
                $queryresumen=$ticketRepository->findByPersGroup($user->getId(),$user->getEmpresaActual(),$statuesgroup,null,$origen,$otros);
                $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());
                break;
        }

        $error_toast='';
        if(null !== $request->query->get('error_toast')){
            $error_toast=$request->query->get('error_toast');
        }
        $tickets=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            20 /*limit per page*/,
            array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));
        return $this->render('ticket/index.html.twig', [
            'pagina'=>$pagina->getNombre(),
            'tickets' => $tickets,
            'error_toast'=>$error_toast,
            'bFolio'=>$folio,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'status'=>$status,
            'statuesGroup'=>$statuesgroup,
            'resumenes'=>$queryresumen,
            'tipoFecha'=>$tipo_fecha,
            'origen'=>$origen,
            'bFiltro'=>$filtro
        ]);
    }

    #[Route("/search", name: "app_ticket_search", methods: ["GET", "POST"])]
    public function search(Request $request, ContratoRepository $contratoRepository, TicketRepository $ticketRepository): Response
    {

        $this->denyAccessUnlessGranted('create','ticket');
        $user=$this->getUser();
        $ticket=null;
        $contratos=null;
        $pagina=$this->entityManager->getRepository(ModuloPer::class)->findOneByName('ticket_new',$user->getEmpresaActual());
        

        $folio = $request->request->get('txtFolio');
        $nombrerut = $request->request->get('txtNombreRut');
        
        if($folio!=''){

            $ticket=$ticketRepository->findAbierto($folio);

            if($ticket){
                
            }else{
                $contrato=$contratoRepository->findOneBy(['folio'=>$folio]);

                if($contrato)
                    return $this->redirectToRoute('app_ticket_new',['id'=>$contrato->getId()]);

            }
        }else{

            if($nombrerut!=''){

                $contratos = $contratoRepository->findNombreRut($nombrerut);
            }
        }
        

        return $this->render('ticket/search.html.twig', [
            'pagina'=>$pagina->getNombre(),
            'ticket'=>$ticket,
            'contratos'=>$contratos,
           
        ]);
    }


    
    #[Route("/resumen", name: "app_ticket_resumen", methods: ["GET","POST"])]
    public function resumen(Request $request,$ticketEstado,String $fechainicio, String $fechafin,$compania,$filtro,$totalStatus,$tipoFecha,$origen, TicketRepository $ticketRepository): Response
    {
        $user=$this->getUser();
        switch($tipoFecha){
            case 0:
                $fecha="t.fechaNuevo between '$fechainicio' and '$fechafin 23:59:59'" ;
                break;
            case 1:
                $fecha="t.fechaAsignado between '$fechainicio' and '$fechafin 23:59:59'" ;
                break;
            case 2:
                $fecha="t.fechaRespuesta between '$fechainicio' and '$fechafin 23:59:59'" ;
                break;
            case 3:
                $fecha="t.fechaCierre between '$fechainicio' and '$fechafin 23:59:59'" ;
                break;
            default:
                $fecha="t.fechaNuevo between '$fechainicio' and '$fechafin 23:59:59'" ;
                break;
        }
        //$fecha="a.fechaCarga between '$fechainicio' and '$fechafin 23:59:59'" ;
        $nombre_status="";
        if(null != $ticketEstado){
            $status=$this->entityManager->getRepository(TicketEstado::class)->find($ticketEstado);
            $nombre_status=$status->getNombre();
        }
        //$queryresumen=$agendaRepository->findByAgendGroup(null,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,null,$fecha);   
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 1:
            case 8:
            case 13:
                $queryresumen=$ticketRepository->findByTicketGroup($user->getId(),$user->getEmpresaActual(),$ticketEstado,$origen,$fecha);   
            break;
            default:
                $queryresumen=$ticketRepository->findByTicketGroup($user->getId(),$user->getEmpresaActual(),$ticketEstado,$origen,$fecha);   
            break;
        }

        return $this->render('ticket/_resumentickets.html.twig',[
            'tickets'=>$queryresumen,
            'total'=>$totalStatus,
            'nombre_status'=>$nombre_status,
        ]);
    }

    #[Route("/{id}", name: "app_ticket_show", methods: ["GET"])]
    public function show(Ticket $ticket): Response
    {
        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }

    #[Route("/{id}/new", name: "app_ticket_new", methods: ["GET", "POST"])]
    public function new(Contrato $contrato,Request $request, 
                        TicketRepository $ticketRepository,
                        EmpresaRepository $empresaRepository,
                        TicketEstadoRepository $ticketEstadoRepository,
                        TicketHistorialRepository $ticketHistorialRepository): Response
    {

        $this->denyAccessUnlessGranted('create','ticket');


        $user=$this->getUser();

        $pagina=$this->entityManager->getRepository(ModuloPer::class)->findOneByName('ticket_new',$user->getEmpresaActual());
        

        $ticket = new Ticket();
        $ticket->setEmpresa($empresaRepository->find($user->getEmpresaActual()));
        $ticket->setContrato($contrato);
        $ticket->setEstado($ticketEstadoRepository->find(1));
        $ticket->setFolio(0);
        $ticket->setOrigen($user);
        $ticket->setFechaNuevo(new \DateTime(date('Y-m-d H:i:s')));
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ultimoTicket=$ticketRepository->ultimoTicket();
            $folio=0;
            if($ultimoTicket!=null){
                $folio=$ultimoTicket->getFolio()+1;
            }
            $ticket->setFolio($folio);
            $ticket->setFolioSac("S".$ticket->getFolio()."-".$ticket->getContrato()->getFolio());
            $ticketRepository->add($ticket);

            $ticketHistoria = new TicketHistorial();
            $ticketHistoria->setTicket($ticket);
            $ticketHistoria->setObservacion($ticket->getMotivo());
            $ticketHistoria->setUsuarioRegistro($user);

            $ticketHistorialRepository->add($ticketHistoria);
            return $this->redirectToRoute('app_ticket_index', ['bStatus'=>1], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ticket/new.html.twig', [
            'pagina'=>$pagina->getNombre(),
            'ticket' => $ticket,
            'form' => $form->createView(),
            'contrato'=>$contrato,
        ]);
    }

    #[Route("/{id}/gestionar", name: "app_ticket_gestionar", methods: ["GET", "POST"])]
    public function gestionar(Request $request, 
                            Ticket $ticket, 
                            TicketRepository $ticketRepository, 
                            TicketEstadoRepository $ticketEstadoRepository,
                            EmpresaRepository $empresaRepository,
                            UsuarioTipoRepository $usuarioTipoRepository,
                            UsuarioRepository $usuarioRepository,
                            TicketHistorialRepository $ticketHistorialRepository): Response
    {
        $user=$this->getUser();

        $pagina="Folio SAC ".$ticket->getFolioSac();
        $usuarioTipos=$usuarioTipoRepository->findBy([],['nombre'=>'Asc']);
        $form = $this->createForm(TicketType::class, $ticket);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($ticket->getEstado()->getId()==1){
                $destino= $usuarioTipoRepository->find($request->request->get('cboDestino'));
                $encargado = $usuarioRepository->find($request->request->get('cboEncargado'));
                $estado=$ticketEstadoRepository->find(2);
                $ticket->setDestino($destino);
                $ticket->setEncargado($encargado);
                $ticket->setEstado($estado);
                $ticket->setFechaAsignado(new \DateTime(date('Y-m-d H:i:s')));
                $ticketRepository->add($ticket);

                $ticketHistoria = new TicketHistorial();
                $ticketHistoria->setTicket($ticket);
                $ticketHistoria->setObservacion($request->request->get('txtObservacion'));
                $ticketHistoria->setUsuarioRegistro($encargado);
                $ticketHistoria->setFecha(new \DateTime(date('Y-m-d H:i:s')));
                $ticketHistoria->setEstado($estado);

                $ticketHistorialRepository->add($ticketHistoria);
                $error_toast="Toast.fire({
                    icon: 'success',
                    title: 'Registro Grabado con exito!!'
                  })";
                return $this->redirectToRoute('app_ticket_index', ['error_toast'=>$error_toast], Response::HTTP_SEE_OTHER);
            }
            if($ticket->getEstado()->getId()==2){

                $observacion=$request->request->get('txtObservacion');
                $estado=$ticketEstadoRepository->find(4);
                $ticket->setRespuesta($observacion);
                $ticket->setEstado($estado);
                //$ticket->setFechaRespuesta(new \DateTime(date('Y-m-d H:i:s')));
                $ticket->setFechaCierre(new \DateTime(date('Y-m-d H:i:s')));
                $ticketRepository->add($ticket);

                $ticketHistoria = new TicketHistorial();
                $ticketHistoria->setTicket($ticket);
                $ticketHistoria->setFecha(new \DateTime(date('Y-m-d H:i:s')));
                $ticketHistoria->setObservacion($observacion);
                $ticketHistoria->setUsuarioRegistro($user);
                
                $ticketHistoria->setEstado($estado);

                $ticketHistorialRepository->add($ticketHistoria);
                $error_toast="Toast.fire({
                    icon: 'success',
                    title: 'Registro Grabado con exito!!'
                  })";
                return $this->redirectToRoute('app_ticket_index', ['error_toast'=>$error_toast], Response::HTTP_SEE_OTHER);
            }
            if($ticket->getEstado()->getId()==3){

                $observacion=$request->request->get('txtObservacion');
                $estado=$ticketEstadoRepository->find(4);
                $ticket->setRespuesta($observacion);
                $ticket->setEstado($estado);
                $ticket->setFechaCierre(new \DateTime(date('Y-m-d H:i:s')));
                $ticketRepository->add($ticket);

                $ticketHistoria = new TicketHistorial();
                $ticketHistoria->setTicket($ticket);
                $ticketHistoria->setFecha(new \DateTime(date('Y-m-d H:i:s')));
                $ticketHistoria->setObservacion($observacion);
                $ticketHistoria->setUsuarioRegistro($user);
                
                $ticketHistoria->setEstado($estado);

                $ticketHistorialRepository->add($ticketHistoria);
                $error_toast="Toast.fire({
                    icon: 'success',
                    title: 'Registro Grabado con exito!!'
                  })";
                return $this->redirectToRoute('app_ticket_index', ['error_toast'=>$error_toast], Response::HTTP_SEE_OTHER);
            }
           

        }
        return $this->render('ticket/gestion.html.twig', [
            'ticket' => $ticket,
            'usuarioTipos'=>$usuarioTipos,
            'pagina'=>$pagina,
            'contrato'=>$ticket->getContrato(),
            'form' => $form->createView(),
            
        ]);
    }

    
    #[Route("/{id}/edit", name: "app_ticket_edit", methods: ["GET", "POST"])]
    public function edit(Request $request, Ticket $ticket, TicketRepository $ticketRepository): Response
    {
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ticketRepository->add($ticket);
            return $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ticket/edit.html.twig', [
            'ticket' => $ticket,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name: "app_ticket_delete", methods: ["POST"])]
    public function delete(Request $request, Ticket $ticket, TicketRepository $ticketRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ticket->getId(), $request->request->get('_token'))) {
            $ticketRepository->remove($ticket);
        }

        return $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
    }
}
