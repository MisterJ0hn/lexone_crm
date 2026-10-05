<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Agenda;
use App\Entity\Usuario;
use App\Entity\ContratoRol;
use App\Entity\AgendaObservacion;
use App\Entity\Causa;
use App\Entity\Cliente;
use App\Entity\Contrato;
use App\Entity\Cuenta;
use App\Entity\Empresa;
use App\Entity\Pais;
use App\Form\ContratoType;
use App\Repository\AgendaRepository;
use App\Repository\JuzgadoRepository;
use App\Repository\ContratoRepository;
use App\Repository\ContratoRolRepository;
use App\Repository\UsuarioRepository;
use App\Repository\UsuarioTipoRepository;
use App\Repository\AgendaStatusRepository;
use App\Repository\CiudadRepository;
use App\Repository\ComunaRepository;
use App\Repository\SucursalRepository;
use App\Repository\CuentaRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\DiasPagoRepository;
use App\Repository\ServicioRepository;
use App\Repository\MateriaRepository;
use App\Repository\ReunionRepository;
use App\Repository\RegionRepository;
use App\Entity\ContratoTemplate;
use App\Repository\ContratoTemplateRepository;
use App\Repository\TipoClienteRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route("/panel_abogado")]
class PanelAbogadoController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }
    #[Route("/", name: "panel_abogado_index", methods: ["GET","POST"])]
    public function index(AgendaRepository $agendaRepository,
                        CuentaRepository $cuentaRepository,
                        PaginatorInterface $paginator,
                        UsuarioTipoRepository $usuarioTipoRepository,
                        UsuarioRepository $usuarioRepository,
                        Request $request,
                        ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','panel_abogado');

        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('panel_abogado',$user->getEmpresaActual());
        $filtro=null;
        $compania=null;
        $fecha=null;
        $statues='5';
        $statuesgroup='4,3,5,7,6,8,14,15,12,13';
        $status=null;
        $tipo_fecha=1;
        $abogado=null;
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
           // $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30*12);
            $dateFin=date('Y-m-d');

        }
        if(null !== $request->query->get('bTipofecha') ){
            $tipo_fecha=$request->query->get('bTipofecha');
        }
        if(null !== $request->query->get('bAbogado')){
            if($request->query->get('bAbogado')==0){
                $abogado=null;
            }else{
                $abogado=$request->query->get('bAbogado');
            }

        }

        
        switch($tipo_fecha){
            case 0:
                $fecha="a.fechaCarga between '$dateInicio' and '$dateFin 23:59:59'" ;
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
        }

       // $fecha="a.fechaAsignado between '$dateInicio' and '$dateFin 23:59:59'" ;
        
        if(null !== $request->query->get('bStatus') && trim($request->query->get('bStatus')!='')){
            $status=$request->query->get('bStatus');
            $statues=$status;
            $statuesgroup=$status;
        }

        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 4:
            case 1:
                $abogados=$usuarioRepository->findBy(['usuarioTipo'=>$usuarioTipoRepository->find(6),'estado'=>1]);

                $query=$agendaRepository->findByPers($abogado,$user->getEmpresaActual(),$compania,$statues,$filtro,1,$fecha);
                $companias=$cuentaRepository->findByPers($abogado,$user->getEmpresaActual());
                $queryresumen=$agendaRepository->findByPersGroup($abogado,$user->getEmpresaActual(),$compania,$statuesgroup,$filtro,1,$fecha);
                
            break;
            default:
                $abogados=$usuarioRepository->findBy(['id'=>$user->getId()]);

                $query=$agendaRepository->findByPers($user->getId(),null,$compania,$statues,$filtro,1,$fecha);
                $companias=$cuentaRepository->findByPers($user->getId());
                $queryresumen=$agendaRepository->findByPersGroup($user->getId(),null,$compania,$statuesgroup,$filtro,1,$fecha);
            break;
        }

        
        $agendas=$paginator->paginate(
        $query, /* query NOT result */
        $request->query->getInt('page', 1), /*page number*/
        20 /*limit per page*/,
        array('defaultSortFieldName' => 'fechaAsignado', 'defaultSortDirection' => 'asc'));

        return $this->render('panel_abogado/index.html.twig', [
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
            'abogados'=>$abogados,
            'bAbogado'=>$abogado
        ]);
    }
    #[Route("/new_rol", name: "panel_abogado_new_rol", methods: ["GET","POST"])]
    public function newRol(Request $request,
                            JuzgadoRepository $juzgadoRepository,
                            ContratoRolRepository $contratoRolRepository,
                            ModuloPerRepository $moduloPerRepository): Response
    {
        
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('panel_abogado',$user->getEmpresaActual());
        $contrato_rol = new ContratoRol();
        $abogado=$this->entityManager->getRepository(Usuario::class)->find($user->getId());
        $contrato_rol->setAbogado($abogado);

        if(isset($_GET['nombre'])){

            $contrato_rol->setNombreRol($_GET['nombre']);
            $contrato_rol->setInstitucionAcreedora($_GET['institucion']);
            $contrato_rol->setJuzgado($juzgadoRepository->find($_GET['juzgado']));

            $entityManager = $this->entityManager;
            $entityManager->persist($contrato_rol);
            $entityManager->flush();

        }
        return $this->render('panel_abogado/contratoRoles.html.twig', [
            'pagina'=>$pagina->getNombre(),
            'contrato_rols' => $contratoRolRepository->findByTemporal($abogado->getId()),
           
        ]);
    }

    #[Route("/reasignar", name: "panel_abogado_reasignar", methods: ["GET","POST"])]
    public function reasignar(Request $request,UsuarioRepository $usuarioRepository,AgendaRepository $agendaRepository):Response
    {
        $user=$this->getUser();
        $agenda_id=$request->query->get('agenda');
        $agenda=$agendaRepository->find($agenda_id);
        $empresa=$this->entityManager->getRepository(Empresa::class)->find($user->getEmpresaActual());
        return $this->render('panel_abogado/reasignar.html.twig', [
            'cuentas'=>$empresa->getCuentas(),
            'agenda'=> $agenda,     
        ]);
    }

    #[Route("/{id}/tramitadores", name: "panel_abogado_tramitadores", methods: ["GET","POST"])]
    public function tramitadores(Cuenta $cuenta, Request $request,UsuarioRepository $usuarioRepository): Response
    {
        
        return $this->render('panel_abogado/tramitadores.html.twig', [
            'tramitadores'=>$usuarioRepository->findByCuenta($cuenta->getId(),['usuarioTipo'=>7,'estado'=>1]),
        ]);
    }
    #[Route("/{id}/sucursales", name: "panel_abogado_sucursales", methods: ["GET","POST"])]
    public function sucursales(Cuenta $cuenta, Request $request,SucursalRepository $sucursalRepository): Response
    {
        
        return $this->render('panel_abogado/sucursales.html.twig', [
            'sucursales'=>$sucursalRepository->findBy(['cuenta'=>$cuenta->getId()]),
        ]);
    }
    #[Route("/{id}", name: "panel_abogado_new", methods: ["GET","POST"])]
    public function new(Agenda $agenda,
                        AgendaRepository $agendaRepository,
                        AgendaStatusRepository $agendaStatusRepository,
                        CuentaRepository $cuentaRepository,
                        UsuarioRepository $usuarioRepository,
                        ReunionRepository $reunionRepository,
                        Request $request,
                        ModuloPerRepository $moduloPerRepository,
                        MateriaRepository $materiaRepository): Response
    {
        $this->denyAccessUnlessGranted('create','panel_abogado');

        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('panel_abogado',$user->getEmpresaActual());
        $companias=$cuentaRepository->findByPers(null,$user->getEmpresaActual());

        if(null != $request->request->get('chkStatus')){
            $agenda->setStatus($agendaStatusRepository->find($request->request->get('chkStatus')));
            if(null !== $request->request->get('cboAbogado')){
                $agenda->setAbogado($usuarioRepository->find($request->request->get('cboAbogado')));
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
            $entityManager = $this->entityManager;

            

            $observacion=new AgendaObservacion();
            $observacion->setAgenda($agenda);
            $observacion->setUsuarioRegistro($usuarioRepository->find($user->getId()));
            $observacion->setStatus($agendaStatusRepository->find($request->request->get('chkStatus')));
            $observacion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
            $observacion->setObservacion($request->request->get('txtObservacion'));
           
            $entityManager->persist($observacion);
            $entityManager->flush();
            $entityManager->persist($agenda);
            $entityManager->flush();
            return $this->redirectToRoute('panel_abogado_index');
        }

        
        $estados=$agenda->getAbogado()->getUsuarioTipo()->getStatues();

    
        return $this->render('panel_abogado/new.html.twig', [
            'agenda'=>$agenda,
            'pagina'=>$pagina->getNombre().' | Gestionar',
            'companias'=>$companias,
            'statues'=>$agendaStatusRepository->findBy(['id'=>$agenda->getAbogado()->getUsuarioTipo()->getStatues()],['orden'=>'asc']),
           
        ]);

    }
    #[Route("/{id}/del_rol", name: "panel_abogado_del_rol",  methods: ["DELETE"])]
    public function delRol(ContratoRol $contratoRol,Request $request,JuzgadoRepository $juzgadoRepository,ContratoRolRepository $contratoRolRepository): Response
    {
        
        $user=$this->getUser();

        
        $abogado=$this->entityManager->getRepository(Usuario::class)->find($user);

            $entityManager = $this->entityManager;
            $entityManager->remove($contratoRol);
            $entityManager->flush();

        
        return $this->render('panel_abogado/contratoRoles.html.twig', [
            'contrato_rols' => $contratoRolRepository->findByTemporal($abogado->getId()),
           
        ]);
    }
    #[Route("/{id}/contrata", name: "panel_abogado_contrata", methods: ["GET","POST"])]
    public function contrata(Agenda $agenda,Request $request,
                            \App\Service\CorreoBienvenidaService $correoBienvenida,
                            AgendaStatusRepository  $agendaStatusRepository,
                            JuzgadoRepository $juzgadoRepository,
                            SucursalRepository $sucursalRepository,
                            DiasPagoRepository $diasPagoRepository,
                            UsuarioRepository $usuarioRepository,
                            ContratoRepository $contratoRepository,
                            RegionRepository $regionRepository,
                            ComunaRepository $comunaRepository,
                            CiudadRepository $ciudadRepository,
                            ReunionRepository $reunionRepository,
                            ServicioRepository $servicioRepository,
                            MateriaRepository $materiaRepository,
                            ContratoTemplateRepository $contratoTemplateRepository,
                            TipoClienteRepository $tipoClienteRepository
                            ):Response
    {
        $this->denyAccessUnlessGranted('create','panel_abogado');

        $user=$this->getUser();
        
        
        $juzgados=$juzgadoRepository->findAll();
        if(null != $request->request->get('chkStatus')){
            $agenda->setStatus($agendaStatusRepository->find($request->request->get('chkStatus')));
            if(null !== $request->request->get('cboAbogado')){
                $agenda->setAbogado($usuarioRepository->find($request->request->get('cboAbogado')));
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
            
            $entityManager = $this->entityManager;
            $entityManager->persist($agenda);
            $entityManager->flush();

            
            return $this->redirectToRoute('panel_abogado_index');
        }

        $contrato=$contratoRepository->findOneBy(['agenda'=>$agenda->getId()]);
        if(null == $contrato){
            
            $contrato=new Contrato();
             $contrato->setAgenda($agenda);
            $cliente=new Cliente();
            $cliente->setNombre($agenda->getNombreCliente());
            $cliente->setTelefono($agenda->getTelefonoCliente());
            $cliente->setCorreo($agenda->getEmailCliente());
            $cliente->setRut($agenda->getRutCliente());
            $cliente->setTelefonoRecado($agenda->getTelefonoRecadoCliente());
            $contrato->setCliente($cliente);
            if(is_null($agenda->getCiudadCliente())){
                $contrato->setCiudad(' ');
            }else{
                $contrato->setCiudad($agenda->getCiudadCliente());
            }
            $contrato->setCuotas(1);
            $contrato->setMontoNivelDeuda($agenda->getMonto()); 
            $contrato->setPagoActual($agenda->getPagoActual()); 
        }
        //$contrato->setCiudad($agenda->getCiudadCliente());
        $contrato->setFechaPrimeraCuota(new \DateTime(date('Y-m-d')));
        $contrato->setVigencia($agenda->getCuenta()->getVigenciaContratos());
        $form = $this->createForm(ContratoType::class, $contrato, [
            'action' =>$this->generateUrl('panel_abogado_contrata',['id'=>$agenda->getId()]),
            'cliente' => $contrato->getCliente(),
        ]);
        $form->add('fechaPrimeraCuota',DateType::class,array('widget'=>'single_text','html5'=>false));
        $form->add('vigencia');
        $form->add('pagoActual');
        if($agenda->getTipoCliente()->getId()==1){
            $form->add('estadoCivil');

        } else {
            // Convenio/Empresa: datos del representante legal (se guardan en el Cliente).
            $form->add('repLegalRut', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['mapped' => false, 'required' => false]);
            $form->add('repLegalNombre', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['mapped' => false, 'required' => false]);
            $form->add('repLegalEstadoCivil', EntityType::class, [
                'class' => \App\Entity\EstadoCivil::class,
                'mapped' => false,
                'required' => false,
            ]);
            $form->add('repLegalProfesion', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['mapped' => false, 'required' => false]);
        }
        // Pais es un catálogo global (mismo listado para todas las empresas), sin
        // filtrar por tenant.
        $form->add('pais', EntityType::class,[
            'class' => Pais::class,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('p')->orderBy('p.orden', 'ASC');
            },
        ]);

        // Plantillas de contrato (ver ContratoTemplateController): solo las
        // disponibles para el tipo de cliente de esta agenda. La elegida se usa
        // luego en ContratoController::pdf() para generar el PDF.
        $tipoClienteAgenda = $agenda->getTipoCliente() ?? $tipoClienteRepository->findOneByNombre('Persona');
        $form->add('contratoTemplate', EntityType::class, [
            'class' => ContratoTemplate::class,
            'choices' => $tipoClienteAgenda ? $contratoTemplateRepository->findDisponibles($agenda->getEmpresa()->getId(), $tipoClienteAgenda->getId()) : [],
            'choice_label' => 'nombre',
            'required' => false,
            'placeholder' => 'Plantilla predeterminada',
            'label' => 'Plantilla de Contrato',
        ]);

        $form->add('cuotas', ChoiceType::class,[
            'choices'=>[
                0,
                1,
                2,
                3,
                4,
                5,
                6,
                7,
                8,
                9,
                10,
                11,
                12,
                13,
                14,
                15,
                16,
                17,
                18,
                19,
                20,
                21,
                22,
                23,
                24,
            ]
            ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            //$this->entityManager->flush();
            $entityManager = $this->entityManager;

            // Convenio/Empresa no tienen sexo (el campo ni siquiera se muestra en el
            // formulario) y sus causas se agregan después, por cliente, desde la
            // línea de tiempo del contrato (ver ContratoController::nuevoClienteConvenio).
            $tipoClienteNombre = $agenda->getTipoClienteNombre();
            $esClientePersona = $tipoClienteNombre === null || $tipoClienteNombre === 'Persona';

            $cliente = $contrato->getCliente() ?? new Cliente();
            $cliente->setNombre($form->get('nombre')->getData());
            $cliente->setCorreo($form->get('email')->getData());
            $cliente->setTelefono($form->get('telefono')->getData());
            $cliente->setRut($form->get('rut')->getData());
            $cliente->setClaveUnica($form->get('claveUnica')->getData());
            $cliente->setDireccion($form->get('direccion')->getData());
            $cliente->setTelefonoRecado($form->get('telefonoRecado')->getData());
            if ($esClientePersona) {
                $cliente->setSexo($request->request->get('cboSexo'));
            } else {
                $cliente->setRepLegalRut($form->get('repLegalRut')->getData());
                $cliente->setRepLegalNombre($form->get('repLegalNombre')->getData());
                $cliente->setRepLegalEstadoCivil($form->get('repLegalEstadoCivil')->getData());
                $cliente->setRepLegalProfesion($form->get('repLegalProfesion')->getData());
            }
            $contrato->setCliente($cliente);
            $entityManager->persist($cliente);


            $agenda->setStatus($agendaStatusRepository->find('7'));
            $contrato->setDiaPago($request->request->get('chkDiasPago'));
            $contrato->setFechaCreacion(new \DateTime(date("Y-m-d H:i:s")));


            foreach ($agenda->getCuenta()->getSucursals() as $sucursal) {
                $contrato->setSucursal($sucursal);
            }
            
            //$contrato->setTramitador($usuarioRepository->find($request->request->get('cboTramitador')));
            //$contrato->setFechaPrimeraCuota(new \DateTime(date("Y-m-d 00:00:00")));
            $contrato->setFechaPrimerPago(new \DateTime(date($request->request->get('txtFechaPago')."-1 00:00:00")));
            

            

            
            $contrato->setCregion($regionRepository->find($request->request->get('cboRegion')));
            $contrato->setCciudad($ciudadRepository->find($request->request->get('cboCiudad')));
            $contrato->setCcomuna($comunaRepository->find($request->request->get('cboComuna')));
           
            
            $entityManager->persist($contrato);
            $entityManager->flush();

           

            $agenda->setNombreCliente($contrato->getCliente()->getNombre());
            $agenda->setTelefonoCliente($contrato->getCliente()->getTelefono());
            $agenda->setEmailCliente($contrato->getCliente()->getCorreo());
            $agenda->setFechaContrato($contrato->getFechaCreacion());


            $entityManager->persist($agenda);
            $entityManager->flush();
           
            $observacion=new AgendaObservacion();
            $observacion->setAgenda($agenda);
            $observacion->setUsuarioRegistro($usuarioRepository->find($user->getId()));
            $observacion->setStatus($agendaStatusRepository->find(7));
            $observacion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
            $observacion->setObservacion('Creacion de Contrato ');
           
            $entityManager->persist($observacion);
            $entityManager->flush();
            //Extraer ultimo Folio:::

            $ultContrato=$contratoRepository->findLoteMax($user->getEmpresaActual(),'c.folio');

            //sumamos el folio:::
            if($ultContrato){
                $nuevoFolio= $ultContrato->getFolio()+1;
            }else{
                $nuevoFolio=1;
            }
            $contrato->setFolio($nuevoFolio);

            // Materia es un catálogo global (mismo listado para todas las empresas):
            // cada causa debe pertenecer a una de ellas.
            $materiasHabilitadas=[];
            foreach ($materiaRepository->findAll() as $m) {
                $materiasHabilitadas[$m->getId()]=true;
            }

            // Convenio/Empresa no ingresan causas en este paso (el formulario ni
            // siquiera muestra la sección "Causa"): sus causas se agregan después,
            // por cliente, desde la línea de tiempo del contrato.
            if ($esClientePersona) {
            $materiasCausa=$request->request->all('hdMateria');
            $submaterias=$request->request->all('hdSubMateria');
            $letra = $request->request->all('hdLetraCausa');
            $rol = $request->request->all('hdRolCausa');
            $anio = $request->request->all('hdAnioCausa');
            $caratulados=$request->request->all('hdCaratulado');
            $hdjuzgados=$request->request->all('hdJuzgado');
            $countCausa=count($submaterias);
            for ($i=0; $i < $countCausa ; $i++) {

                $causa=new Causa();
                $causa->setEstado(1);
                $causa->setAgenda($contrato->getAgenda());

                // Materia de la causa (obligatoria). Se toma de hdMateria[]; si viene
                // vacía se deriva de la servicio elegido.
                $materia=null;
                if(isset($materiasCausa[$i]) && $materiasCausa[$i]!=="" && isset($materiasHabilitadas[(int)$materiasCausa[$i]])){
                    $materia=$materiaRepository->find($materiasCausa[$i]);
                }
                $servicio=null;
                if(null !== $submaterias[$i] && $submaterias[$i]!==""){
                    $servicio=$servicioRepository->find($submaterias[$i]);
                    if($materia===null && $servicio!==null){
                        $materia=$servicio->getMateria();
                    }
                }
                if($materia===null){
                    // Sin materia válida no se puede crear la causa.
                    continue;
                }
                $causa->setMateria($materia);
            // echo $contratoAnexo->getId();
                //$causa->setAnexo($contratoAnexoRepository->find($contratoAnexo->getId()));
                if(null !== $letra[$i]){
                    $causa->setLetra($letra[$i]);
                }
                if(null !== $rol[$i]){
                    $causa->setRol($rol[$i]);
                }
                if(null !== $anio[$i] && $anio[$i]!=="" ){
                    $causa->setAnio($anio[$i]);
                }
                if(null !== $caratulados[$i]){
                    $causa->setCausaNombre($caratulados[$i]);
                }
                if($servicio!==null && $servicio->getMateria()->getId()===$materia->getId()){
                    $causa->setServicio($servicio);
                }
                if(null !== $hdjuzgados[$i]){
                    $juzgado=$juzgadoRepository->find($hdjuzgados[$i]);
                    $causa->setJuzgado($juzgado);
                    if($juzgado){
                        if($juzgado->getCorte()!=null){
                            $causa->setCorte($juzgado->getCorte());
                        }
                    }
                }


                $entityManager->persist($causa);
                $entityManager->flush();
                //if($etapa_pendiente){
                //    $causa->setEtapaPendiente($etapa_pendiente->getNombre());
                //}
                //$entityManager->persist($causa);
                //$entityManager->flush();
            }
            }

            $contrato->setTramitador($user);

            $entityManager->persist($contrato);
            $entityManager->flush();

            
           
           

            // Correo de bienvenida (template HTML de la empresa); un fallo no corta la creación.
            $motivoCorreo = $correoBienvenida->enviar($contrato);
            if ($motivoCorreo === null) {
                $this->addFlash('success', 'Se envió el correo de bienvenida al cliente.');
            } else {
                $this->addFlash('error', 'No se envió el correo de bienvenida: ' . $motivoCorreo);
            }

            return $this->redirectToRoute('contrato_pdf',['id'=>$contrato->getId()]);
        }
        return $this->render('panel_abogado/contrata.html.twig',[
            'agenda'=>$agenda,
            'contrato'=>$contrato,
            'juzgados'=>$juzgados,
            'tramitadores'=>$usuarioRepository->findByCuenta($agenda->getCuenta()->getId(),['usuarioTipo'=>7,'estado'=>1]),
            'form'=>$form->createView(),
            'diasPagos'=>$diasPagoRepository->findAll(),
            'sucursales'=>$sucursalRepository->findBy(['cuenta'=>$agenda->getCuenta()->getId()]),
            'regiones'=>$regionRepository->findAll(),
            'materias'=>$materiaRepository->findBy([],['nombre'=>'ASC']),
        ] );
    }
    #[Route("/{id}/no_contrata", name: "panel_abogado_no_contrata", methods: ["GET","POST"])]
    public function noContrata(Agenda $agenda,Request $request,
                    AgendaStatusRepository  $agendaStatusRepository,
                    JuzgadoRepository $juzgadoRepository,
                    ContratoRolRepository $contratoRolRepository,
                    UsuarioRepository $usuarioRepository,
                    SucursalRepository $sucursalRepository): Response
    {
        $this->denyAccessUnlessGranted('create','panel_abogado');

        $user=$this->getUser();
        
        if(null !== $request->request->get('status')){
            $agenda->setStatus($agendaStatusRepository->find($request->request->get('status')));
           
        }
        if(null !==$request->request->get('hdNoContrata')){
            $agenda->setStatus($agendaStatusRepository->find($request->request->get('hdNoContrata')));
           // $agenda->setObservacion($agenda->getObservacion()."<hr>".$request->request->get('txtObservacion'));
            $entityManager = $this->entityManager;
            $observacion=new AgendaObservacion();
            $observacion->setAgenda($agenda);
            $observacion->setUsuarioRegistro($usuarioRepository->find($user->getId()));
            $observacion->setStatus($agendaStatusRepository->find($request->request->get('hdNoContrata')));
            $observacion->setFechaRegistro(new \DateTime(date("Y-m-d H:i:s")));
            $observacion->setObservacion($request->request->get('txtObservacion'));
           // $agenda->setObservacion("");
            $entityManager->persist($observacion);
            $entityManager->flush();

            switch($request->request->get('hdNoContrata')){
                case 6:
                    switch($user->getUsuarioTipo()->getId()){
                        case 3:
                        case 1:
                        case 4:
                        case 8:
                            $agenda->setAbogado(null);
                            break;
                    }
                break;
            }

            
            $entityManager->persist($agenda);
            $entityManager->flush();
            return $this->redirectToRoute('panel_abogado_index');
        }

        return $this->render('panel_abogado/no_contrata.html.twig', [
            'agenda'=>$agenda,
            'status'=>$_GET['status']
        ]);
    }
    #[Route("/{id}/compania", name: "panel_abogado_compania", methods: ["GET","POST"])]
    public function compania(Agenda $agenda,Request $request,
                   CuentaRepository  $cuentaRepository)
    {
        $entityManager = $this->entityManager;
        
        $compania=$request->query->get('compania');
        $agenda->setCuenta($cuentaRepository->find($compania));
        $entityManager->persist($agenda);
        $entityManager->flush();

        // Antes se borraban todas las causas al cambiar de Cuenta porque la materia
        // dependía de la cuenta. Ahora la materia vive en cada causa y la Cuenta es
        // solo una unidad de negocio, así que las causas se conservan.

        return $this->render('panel_abogado/ok.html.twig');

    }
     
   

}
