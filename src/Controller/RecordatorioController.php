<?php

namespace App\Controller;

use App\Entity\Contrato;
use App\Entity\Empresa;
use App\Entity\Recordatorio;
use App\Form\RecordatorioType;
use App\Repository\CuentaRepository;
use App\Repository\EmpresaRepository;
use App\Repository\RecordatorioRepository;
use DateTime;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route("/recordatorio")]
class RecordatorioController extends AbstractController
{
    #[Route("/", name: "recordatorio_index")]
    public function index(Request $request,
                        PaginatorInterface $paginator,
                        RecordatorioRepository $recordatorioRepository,
                        CuentaRepository $cuentaRepository): Response
    {
        $user=$this->getUser();
        $filtro=null;
        $error='';
        $error_toast="";
        $otros="";
        $folio="";
        $compania=null;
        $criterios=[];
        $joins=[];
        $criteriosPersonalizado=[];
        $orCriteriosPersonalizado=[];

      
        array_push($joins,[
            'r.contrato'=>'c',
            'c.agenda'=>'a']);
        if(null !== $request->query->get('bFiltro') && $request->query->get('bFiltro')!=''){
            $filtro=$request->query->get('bFiltro');
            array_push($criteriosPersonalizado,["c.nombre like '%$filtro%' or c.telefono like '%$filtro%' or c.email like '%$filtro%' ",'','']);
      
        }
        if(null !== $request->query->get('bCompania') && $request->query->get('bCompania')!=0){
            $compania=$request->query->get('bCompania');
            
            array_push($criteriosPersonalizado,['a.cuenta','=',$compania]);


        }
        if(null !== $request->query->get('bFecha')){
            $aux_fecha=explode(" - ",$request->query->get('bFecha'));
            $dateInicio=$aux_fecha[0];
            $dateFin=$aux_fecha[1];
        }else{
            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            //$dateInicio=date('Y-m-d');
            
            $dateFin=date('Y-m-d');
        }
        //$fecha="r.fechaAviso between '$dateInicio' and '$dateFin 23:59:59'" ;

        array_push($criteriosPersonalizado,['r.fechaAviso','between'," '$dateInicio' and '$dateFin 23:59:59'"]);
                


        $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());


        $query=$recordatorioRepository->findByPers(['usuarioRegistro'=>$user->getId()],['leido'=>'Asc','fechaAviso'=>'Asc'],null,null,$joins,$criteriosPersonalizado,$orCriteriosPersonalizado);

        $recordatorios=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            20 /*limit per page*/,
            array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));
        return $this->render('recordatorio/index.html.twig', [
            'pagina' => 'Recordatorios',
            'recordatorios'=>$recordatorios,
            'bFiltro'=>$filtro,
            'bFolio'=>$folio,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
        ]);
    }

    #[Route("/agenda", name: "recordatorio_avisos", methods: ["GET","POST"])]
    public function avisos(RecordatorioRepository $recordatorioRepository): Response
    {
        $user=$this->getUser();
        $recordatorios = $recordatorioRepository->findByVencidas($user->getId(),date('Y-m-d'));
        $recordatorioCount= $recordatorioRepository->findByVencidasCount($user->getId(),date('Y-m-d'));
        return $this->render('recordatorio/avisos.html.twig',[
            'pagina'=>'Agenda',
            'recordatorios'=>$recordatorios,
            'recordatorioCount'=>$recordatorioCount
        ]);
    }

    #[Route("/agenda/{id}", name: "recordatorio_agenda", methods: ["GET","POST"])]
    public function agenda(Contrato $contrato,Request $request,RecordatorioRepository $recordatorioRepository): Response
    {
        $user=$this->getUser();
        $recordatorio=new Recordatorio();
        $recordatorio->setUsuarioRegistro($user);
        $recordatorio->setFechaCreacion(new DateTime(date("")));
        $recordatorio->setContrato($contrato);
        $recordatorio->setLeido(false);
        $form = $this->createForm(RecordatorioType::class, $recordatorio);
        $form->add('fechaAviso',DateType::class, [
            // renders it as a single text box
            'widget' => 'single_text',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($recordatorio);
            $entityManager->flush();
            return $this->redirectToRoute('recordatorio_agenda',['id'=>$contrato->getId()]);
        }

        $recordatorios = $recordatorioRepository->findBy(['contrato'=>$contrato,'usuarioRegistro'=>$user]);

        return $this->render('recordatorio/agenda.html.twig',[
            'pagina'=>'Agenda',
            'contrato'=>$contrato,
            'form'=>$form->createView(),
            'recordatorios'=>$recordatorios
        ]);

    }

    #[Route("/{id}", name: "recordatorio_show", methods: ["GET"])]
    public function show(Recordatorio $recordatorio): Response
    {
        
        $user=$this->getUser();
        
        return $this->render('recordatorio/show.html.twig', [
            'contrato' => $recordatorio->getContrato(),
    
            'pagina'=>'Ver recordatorio',
            'recordatorio'=>$recordatorio
            
        ]);
    }
    #[Route("/{id}/leido", name: "recordatorio_leido", methods: ["GET"])]
    public function leidoAgenda(Recordatorio $recordatorio): Response
    {
        
        $this->marcarLeido($recordatorio);
        return $this->redirectToRoute('recordatorio_agenda',['id'=>$recordatorio->getContrato()->getId()]);
        
        
    }

    #[Route("/{id}/leido_list", name: "recordatorio_leido_list", methods: ["GET"])]
    public function leidoList(Recordatorio $recordatorio): Response
    {
        
        $this->marcarLeido($recordatorio);
        
        return $this->redirectToRoute('recordatorio_index');
        
        
    }

    public function marcarLeido(Recordatorio $recordatorio){
        $recordatorio->setLeido(true);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($recordatorio);
        $entityManager->flush();
    }


    
}
