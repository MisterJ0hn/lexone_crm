<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Contrato;
use App\Entity\ContratoRol;
use App\Entity\Usuario;
use App\Entity\Cuota;
use App\Entity\Region;
use App\Entity\Ciudad;
use App\Entity\Comuna;
use App\Form\ContratoType;
use App\Entity\AgendaObservacion;
use App\Entity\Causa;
use App\Entity\CausaObservacion;
use App\Entity\Cliente;
use App\Entity\ClienteHistorial;
use App\Entity\ContratoAudios;
use App\Entity\ContratoObservacion;
use App\Entity\LineaTiempoObservacion;
use App\Entity\LineaTiempoTerminada;
use App\Form\ContratoRolType;
use App\Form\ClienteConvenioType;
use App\Repository\ContratoRepository;
use App\Repository\ContratoRolRepository;
use App\Repository\JuzgadoRepository;
use App\Repository\SucursalRepository;
use App\Repository\CuentaRepository;
use App\Repository\DiasPagoRepository;
use App\Repository\UsuarioRepository;
use App\Repository\UsuarioTipoRepository;
use App\Repository\AgendaStatusRepository;
use App\Repository\CausaObservacionRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\CuotaRepository;
use App\Repository\ConfiguracionRepository;
use App\Repository\LotesRepository;
use App\Repository\RegionRepository;
use App\Repository\CiudadRepository;
use App\Repository\ClienteHistorialRepository;
use App\Repository\ClienteRepository;
use App\Repository\ComunaRepository;
use App\Repository\ContratoAudiosRepository;
use App\Repository\ContratoObservacionRepository;
use App\Repository\CorteRepository;
use App\Repository\JuzgadoCuentaRepository;
use App\Repository\LineaTiempoTerminadaRepository;

use App\Repository\LineaTiempoEtapasRepository;
use App\Repository\LineaTiempoObservacionRepository;
use App\Repository\MateriaEstrategiaRepository;
use App\Repository\MateriaRepository;
use App\Repository\PaisRepository;
use App\Repository\ContratoTemplateRepository;
use App\Repository\TipoClienteRepository;
use App\Service\ContratoTemplateRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Service\ContratoFunciones;
use App\Service\Toku;
use DateTime;
use Exception;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[Route("/contrato")]
class ContratoController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }
    #[Route("/", name: "contrato_index", methods: ["GET","POST"])]
    public function index(ContratoRepository $contratoRepository,PaginatorInterface $paginator,ModuloPerRepository $moduloPerRepository,Request $request,CuentaRepository $cuentaRepository): Response
    {
        $this->denyAccessUnlessGranted('view','contrato');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('contrato',$user->getEmpresaActual());
        $filtro=null;
        $error='';
        $error_toast="";
        $otros="";
        $folio="";
        if(null !== $request->query->get('error_toast')){
            $error_toast=$request->query->get('error_toast');
        }
        $compania=null;
        if(null !== $request->query->get('bFolio') && $request->query->get('bFolio')!=''){
            $folio=$request->query->get('bFolio');
            $otros=" c.folio= $folio";

            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30*24);//2 años atrás
            $dateFin=date('Y-m-d');
            $fecha=$otros. " and a.status in (7,14)";

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
                $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30*24);//2 años atrás
                //$dateInicio=date('Y-m-d');
                
                $dateFin=date('Y-m-d');
            }
            $fecha="c.fechaCreacion between '$dateInicio' and '$dateFin 23:59:59' and a.status in (7,14)" ;
        }
      
        switch($user->getUsuarioTipo()->getId()){
            case 3:
            case 4:
            case 1:
            case 8:
            case 13:
            case 7:
                $query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha);
                $companias=$cuentaRepository->findByPers($user->getId(),1);
                break;
            case 12://Cobradores
                $lotes;
                foreach($user->getUsuarioLotes() as $usuarioLote){
                    $lotes[]=$usuarioLote->getLote()->getId();
                }
                if(count($lotes)>0){
                    $fecha.=" and c.idLote in (".implode(",",$lotes).") ";
                }else{
                    $fecha.=" and c.idLote is null ";
                }
                //$fecha.=" and c.idLote in (".implode(",",$lotes).") ";
                $query=$contratoRepository->findByPers(null,$user->getEmpresaActual(),$compania,$filtro,null,$fecha);
                $companias=$cuentaRepository->findByPers($user->getId(),1);
                break;
            default:
                $query=$contratoRepository->findByPers($user->getId(),null,$compania,$filtro,null,$fecha);
                $companias=$cuentaRepository->findByPers($user->getId());
                
            break;
        }
        //$companias=$cuentaRepository->findByPers($user->getId());
        //$query=$contratoRepository->findAll();
        $contratos=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            20 /*limit per page*/,
            array('defaultSortFieldName' => 'id', 'defaultSortDirection' => 'desc'));
        return $this->render('contrato/index.html.twig', [
            'contratos' => $contratos,
            'bFiltro'=>$filtro,
            'bFolio'=>$folio,
            'companias'=>$companias,
            'bCompania'=>$compania,
            'dateInicio'=>$dateInicio,
            'dateFin'=>$dateFin,
            'pagina'=>$pagina->getNombre(),
            'error'=>$error,
            'error_toast'=>$error_toast,
        ]);
    }

    #[Route("/actualizafecha", name: "contrato_actualizaFecha", methods: ["GET","POST"])]
    public function actualizafecha(Request $request,ContratoRepository $contratoRepository): Response
    {
        $entityManager = $this->entityManager;
        
        $contratos=$contratoRepository->findAll();
        foreach($contratos as $contrato){
            $agenda=$contrato->getAgenda();
            $agenda->setFechaContrato($contrato->getFechaCreacion());
            $entityManager->persist($agenda);
            $entityManager->flush();
        }
        return $this->redirectToRoute('contrato_index');
    }
    #[Route("/new", name: "contrato_new", methods: ["GET","POST"])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('create','contrato');
        $contrato = new Contrato();
        $form = $this->createForm(ContratoType::class, $contrato);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;
            $entityManager->persist($contrato);
            $entityManager->flush();

            return $this->redirectToRoute('contrato_index');
        }

        return $this->render('contrato/new.html.twig', [
            'contrato' => $contrato,
            'form' => $form->createView(),
        ]);
    }
    #[Route("/regenerapdfs", name: "contrato_regenerapdfs", methods: ["GET","POST"])]
    public function regenerapdfs(\Knp\Snappy\Pdf $snappy,ContratoRepository $contratoRepository): Response
    {
        $this->denyAccessUnlessGranted('edit','contrato');

        $contratos=$contratoRepository->findBy(['pdf'=>null]);

        foreach($contratos as $contrato){
            $filename = sprintf('Contrato-'.$contrato->getId().'-%s.pdf',rand(0,9000));
        
            $html = $this->renderView('contrato/print.html.twig', array(
                'contrato' => $contrato,
                'Titulo'=>"Contrato"
            ));

            $entityManager = $this->entityManager;
            $contrato->setPdf($filename);
            $entityManager->persist($contrato);
            $entityManager->flush();

            $snappy->generateFromHtml(
            $html,
            $this->getParameter('url_root'). $this->getParameter('pdf_contratos').$filename
            );    
        }
        return $this->redirectToRoute('contrato_index');
    }
    #[Route("/{id}", name: "contrato_show", methods: ["GET"])]
    public function show(Contrato $contrato,
                        DiasPagoRepository $diasPagoRepository,
                        ModuloPerRepository $moduloPerRepository,
                        CausaObservacionRepository $causaObservacionRepository): Response
    {
        $this->denyAccessUnlessGranted('view','contrato');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('contrato',$user->getEmpresaActual());
        return $this->render('contrato/show.html.twig', [
            'contrato' => $contrato,
            'agenda'=>$contrato->getAgenda(),
            'pagina'=>$pagina->getNombre(),
            'diasPagos'=>$diasPagoRepository->findAll(),
            'observaciones'=>$causaObservacionRepository->findBy(['contrato'=>$contrato],['fechaRegistro'=>'Desc'])
        ]);
    }

    #[Route("/{id}/new_rol", name: "contrato_new_rol", methods: ["GET","POST"])]
    public function newRol(Contrato $contrato,Request $request,JuzgadoRepository $juzgadoRepository,ContratoRolRepository $contratoRolRepository): Response
    {
        
        $user=$this->getUser();
        if (null==$request->query->get('mode')){
            $mode='edit';
        }else{
            $mode=$request->query->get('mode');
        }
        
        $contrato_rol = new ContratoRol();
        $contrato_rol->setContrato($contrato);
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
        return $this->render('contrato/contratoRoles.html.twig', [
            'contrato_rols' => $contratoRolRepository->findBy(['contrato'=>$contrato->getId()]),
            'mode'=>$mode,
           
        ]);
    }
    
    #[Route("/{id}/del_rol", name: "contrato_del_rol",  methods: ["DELETE"])]
    public function delRol(ContratoRol $contratoRol,Request $request,JuzgadoRepository $juzgadoRepository,ContratoRolRepository $contratoRolRepository): Response
    {
        
        $user=$this->getUser();

        $contrato=$contratoRol->getContrato();
        $entityManager = $this->entityManager;
        $entityManager->remove($contratoRol);
        $entityManager->flush();

        
        return $this->render('contrato/contratoRoles.html.twig', [
            'contrato_rols' => $contratoRolRepository->findBy(['contrato'=>$contrato->getId()]),
           
        ]);
    }
    #[Route("/{id}/edit", name: "contrato_edit", methods: ["GET","POST"])]
    public function edit(Request $request, 
                    Contrato $contrato,
                    JuzgadoRepository $juzgadoRepository,
                    SucursalRepository $sucursalRepository,
                    DiasPagoRepository $diasPagoRepository,
                    UsuarioRepository $usuarioRepository,
                    CuentaRepository $cuentaRepository,
                    ModuloPerRepository $moduloPerRepository,
                    CuotaRepository $cuotaRepository,
                    RegionRepository $regionRepository,
                    ComunaRepository $comunaRepository,
                    CiudadRepository $ciudadRepository,
                    MateriaRepository $materiaRepository): Response
    {
        $this->denyAccessUnlessGranted('edit','contrato');
        $user=$this->getUser();

        $pagina=$moduloPerRepository->findOneByName('contrato',$user->getEmpresaActual());
        $juzgados=$juzgadoRepository->findAll();
        $form = $this->createForm(ContratoType::class, $contrato,['cliente' => $contrato->getCliente()]);
        $form->add('fechaPrimeraCuota',DateType::class,array('widget'=>'single_text','html5'=>false));
        $form->add('vigencia');
        $form->add('pagoActual');
        $form->add('isIncorporacion');
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
        //buscamos la primera cuota para sabes si tiene algun pago asociado:::
        $cuota=$cuotaRepository->findOneByUltimaPagada($contrato->getId());
        $companias=$cuentaRepository->findByPers($user->getId(),1);
        
        $tienePago=false;
        if(null != $cuota){
            foreach( $cuota->getPagoCuotas() as $pago){
                $tienePago=true;
            }
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            
           

            $compania=$_POST['cboCompanias'];
            $contrato->setSucursal($sucursalRepository->find($request->request->get('cboSucursal')));
            $contrato->setDiaPago($request->request->get('chkDiasPago'));
            //$contrato->setTramitador($usuarioRepository->find($request->request->get('cboTramitador')));
            $contrato->setFechaPrimerPago(new \DateTime(date($request->request->get('txtFechaPago')."-1 00:00:00")));

            $contrato->setCregion($regionRepository->find($request->request->get('cboRegion')));
            $contrato->setCciudad($ciudadRepository->find($request->request->get('cboCiudad')));
            $contrato->setCcomuna($comunaRepository->find($request->request->get('cboComuna')));
            $entityManager = $this->entityManager;
            $contrato->setPdf(null);

            $agenda=$contrato->getAgenda();

            // Convenio/Empresa: el contrato no lleva un único cliente (los clientes
            // propios se crean después desde la línea de tiempo, ver
            // ContratoController::nuevoClienteConvenio). Persona conserva el
            // comportamiento histórico exacto.
            if ($agenda->getTipoClienteNombre() === null || $agenda->getTipoClienteNombre() === 'Persona') {
                 $cliente = $contrato->getCliente() ?? new Cliente();
                $cliente->setNombre($form->get('nombre')->getData());
                $cliente->setCorreo($form->get('email')->getData());
                $cliente->setTelefono($form->get('telefono')->getData());
                $cliente->setRut($form->get('rut')->getData());
                $cliente->setClaveUnica($form->get('claveUnica')->getData());
                $cliente->setDireccion($form->get('direccion')->getData());
                $cliente->setTelefonoRecado($form->get('telefonoRecado')->getData());
                $cliente->setSexo($request->request->get('cboSexo'));
                $contrato->setCliente($cliente);
                $entityManager->persist($cliente);

                $entityManager->persist($contrato);
                $entityManager->flush();

                $agenda->setNombreCliente($contrato->getCliente()->getNombre());
                $agenda->setTelefonoCliente($contrato->getCliente()->getTelefono());
                $agenda->setEmailCliente($contrato->getCliente()->getCorreo());
                $agenda->setReunion($contrato->getReunion());
                $agenda->setCuenta($cuentaRepository->find($compania));
                $entityManager->persist($agenda);
                $entityManager->flush();
            } else {
                // Igual persistimos los cambios propios del contrato (región/comuna,
                // sucursal, día de pago, etc. seteados más arriba) aunque no haya
                // cliente que asociar.
                $entityManager->persist($contrato);
                $entityManager->flush();
            }


            /*$contratoMees=$contrato->getContratoMees();

            foreach($contratoMees as $contratoMee){
                $entityManager->remove($contratoMee);
                $entityManager->flush();
            }
            
            */


            if(!$tienePago){
                $detalleCuotas=$contrato->getDetalleCuotas();
                foreach($detalleCuotas as $detalleCuota){
                // $contrato->removeDetalleCuota($detalleCuota);
                    $entityManager->remove($detalleCuota);
                    $entityManager->flush();
                }
                

                $countCuotas=$contrato->getCuotas();
                $fechaPrimerPago=$contrato->getFechaPrimerPago();
                $diaPago=$contrato->getDiaPago();
                $sumames=0;
                $numeroCuota=1;
                $isAbono=$contrato->getIsAbono();
                $isTotal=$contrato->getIsTotal();
                $isIncorporacion=$contrato->getIsIncorporacion();
                if($isAbono==true || $isTotal==true){
                    $cuota=new Cuota();
                    $cuota->setContrato($contrato);
                    $cuota->setNumero($numeroCuota);
                    $cuota->setFechaPago($contrato->getFechaPrimeraCuota());
                    $cuota->setMonto($contrato->getPrimeraCuota());
                    $entityManager->persist($cuota);
                    $entityManager->flush();
                    $numeroCuota++;
                }
                if($isIncorporacion==true){

                
                    $contrato->setFechaPrimeraCuota(new \DateTime($request->request->get('txtFechaIncorporacion')));
                   
                    $cuota=new Cuota();
    
                    $cuota->setContrato($contrato);
                    $cuota->setNumero($numeroCuota);
                    $cuota->setFechaPago($contrato->getFechaPrimeraCuota());
                    $cuota->setMonto($contrato->getPrimeraCuota());
                    
                   
                    $entityManager->persist($cuota);
                    $entityManager->flush();
                    $numeroCuota++;
                   
                }

                $primerPago=date("Y-m-".$diaPago,strtotime($fechaPrimerPago->format('Y-m-d')));
                if(date("n",strtotime($fechaPrimerPago->format('Y-m-d')))==2){
                    if($diaPago==30)
                        $primerPago=date("Y-m-28",strtotime($fechaPrimerPago->format('Y-m-d')));

                }
                $timePrimrePago=strtotime($primerPago);
                $timeFechaActual=strtotime(date("Y-m-d"));
                if($timeFechaActual>=$timePrimrePago){
                    $sumames=1;
                }else{
                    if($isIncorporacion==true){
                        if(date("m",strtotime($fechaPrimerPago->format('Y-m-d')))==date("m")){
                            $sumames=1;
                        }
                    }
                }

                if($contrato->getValorCuota()!=0){
                    for($i=0;$i<$countCuotas;$i++){
                        $cuota=new Cuota();
                
                        $i_aux=$i;
                    
                        $cuota->setContrato($contrato);
                        $cuota->setNumero($numeroCuota);

                        $ts = mktime(0, 0, 0, date('m',$timePrimrePago) + $sumames+$i_aux, 1,date('Y',$timePrimrePago));
                        
                        $dia=$diaPago;
                        if(date("n",$ts)==2){
                            if($diaPago==30){
                                $dia=date("d",mktime(0,0,0,date('m',$timePrimrePago)+ $sumames+$i_aux+1,1,date('Y',$timePrimrePago))-24);
                            }
                        }
                        $fechaCuota=date("Y-m-d", mktime(0,0,0,date('m',$timePrimrePago) + $sumames+$i_aux,$dia,date('Y',$timePrimrePago)));
                        $cuota->setFechaPago(new \DateTime($fechaCuota));
                        $cuota->setMonto($contrato->getValorCuota());

                        $entityManager->persist($cuota);
                        $entityManager->flush();
                        $numeroCuota++;
                    }
                }
            }
            
            
            
           
            
            return $this->redirectToRoute('contrato_pdf',['id'=>$contrato->getId()]);
        }
               

        return $this->render('contrato/edit.html.twig', [
            'contrato' => $contrato,
            'agenda' => $contrato->getAgenda(),
            'companias'=>$companias,
            'tienePago'=>$tienePago,
            'agenda'=>$contrato->getAgenda(),
            'form' => $form->createView(),
            'juzgados'=>$juzgados,
            'pagina'=>$pagina->getNombre()." N° ".$contrato->getFolio(),
            'tramitadores'=>$usuarioRepository->findByCuenta($contrato->getAgenda()->getCuenta()->getId(),['usuarioTipo'=>7,'estado'=>1]),
            'diasPagos'=>$diasPagoRepository->findAll(),
            'sucursales'=>$sucursalRepository->findBy(['cuenta'=>$contrato->getAgenda()->getCuenta()->getId()]),
            'regiones'=>$regionRepository->findAll(),
            'materias'=>$materiaRepository->findBy([],['nombre'=>'ASC']),

        ]);
    }


    #[Route("/{id}/finalizar", name: "contrato_finalizar", methods: ["GET","POST"])]
    public function finalizar(Request $request, 
                            Contrato $contrato,
                            JuzgadoRepository $juzgadoRepository,
                            SucursalRepository $sucursalRepository,
                            DiasPagoRepository $diasPagoRepository,
                            UsuarioRepository $usuarioRepository,
                            UserPasswordHasherInterface $encoder,
                            UsuarioTipoRepository $usuarioTipoRepository,
                            ConfiguracionRepository $configuracionRepository,
                            ContratoRepository $contratoRepository,
                            ContratoFunciones $contratoFunciones,
                            CuentaRepository $cuentaRepository,
                            LotesRepository $lotesRepository,
                            RegionRepository $regionRepository,
                            ComunaRepository $comunaRepository,
                            CiudadRepository $ciudadRepository,
                            MateriaRepository $materiaRepository
                            ): Response
    {
        $this->denyAccessUnlessGranted('create','panel_abogado');
        $user=$this->getUser();
        $juzgados=$juzgadoRepository->findAll();
        $companias=$cuentaRepository->findByPers($user->getId(),1);
        $toku = new Toku();
        
        $form = $this->createForm(ContratoType::class, $contrato, ['cliente' => $contrato->getCliente()]);
        $form->add('fechaPrimeraCuota',DateType::class,array('widget'=>'single_text','html5'=>false));
        $form->add('vigencia');
        $form->add('pagoActual');

        $form->add('isIncorporacion');
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
            $this->entityManager->flush();
            $configuracion=$configuracionRepository->find(1);
            $entityManager = $this->entityManager;


            
            $contrato->setCregion($regionRepository->find($request->request->get('cboRegion')));
            $contrato->setCciudad($ciudadRepository->find($request->request->get('cboCiudad')));
            $contrato->setCcomuna($comunaRepository->find($request->request->get('cboComuna')));
            

            //configuramos el Lote al cual caera::
            //$ult_contrato=$contratoRepository->findLoteMax($user->getEmpresaActual());
            /*$lote=$lotesRepository->findPrimerDisponible();
            if(null == $lote){
                //si no hay lotes para utilizar, se setean en false todos para poder utilizar...
                $lotes=$lotesRepository->findBy(['empresa'=>$user->getEmpresaActual()]);
                foreach($lotes as $lote){
                    $lote->setIsUtilizado(false);
                    $entityManager->persist($lote);
                    $entityManager->flush();
                }
                $lote=$lotesRepository->findPrimerDisponible();

            }else{
                $lote->setIsUtilizado(true);
                $entityManager->persist($lote);
                $entityManager->flush();
            }
*/

            $contrato->setDiaPago($request->request->get('chkDiasPago'));
            $contrato->setFechaCreacion(new \DateTime(date("Y-m-d H:i:s")));
            $contrato->setSucursal($sucursalRepository->find($request->request->get('cboSucursal')));
           // $contrato->setTramitador($usuarioRepository->find($request->request->get('cboTramitador')));
            //$contrato->setIdLote($lote);
            
            $agenda=$contrato->getAgenda();

            // Convenio/Empresa: el contrato no lleva un único cliente (los clientes
            // propios se crean después desde la línea de tiempo, ver
            // ContratoController::nuevoClienteConvenio). Persona conserva el
            // comportamiento histórico exacto.
            if ($agenda->getTipoClienteNombre() === null || $agenda->getTipoClienteNombre() === 'Persona') {
                $cliente = $contrato->getCliente() ?? new Cliente();
                $cliente->setNombre($form->get('nombre')->getData());
                $cliente->setTelefono($form->get('telefono')->getData());
                $cliente->setCorreo($form->get('email')->getData());
                $cliente->setRut($form->get('rut')->getData());
                $cliente->setDireccion($form->get('direccion')->getData());
                $cliente->setTelefonoRecado($form->get('telefonoRecado')->getData());
                $entityManager->persist($cliente);

                $entityManager->persist($contrato);
                $entityManager->flush();
                $agenda->setNombreCliente($contrato->getCliente()->getNombre());
                $agenda->setTelefonoCliente($contrato->getCliente()->getTelefono());
                $agenda->setEmailCliente($contrato->getCliente()->getCorreo());
                $agenda->setReunion($contrato->getReunion());
                $entityManager->persist($agenda);
                $entityManager->flush();
            } else {
                // Igual persistimos los cambios propios del contrato (región/comuna,
                // sucursal, fecha de creación, etc. seteados más arriba) aunque no
                // haya cliente que asociar.
                $entityManager->persist($contrato);
                $entityManager->flush();
            }

            $countCuotas=$contrato->getCuotas();
            $fechaPrimerPago=$contrato->getFechaPrimerPago();
            $diaPago=$contrato->getDiaPago();
            $sumames=0;
            $numeroCuota=1;
            $isAbono=$contrato->getIsAbono();
            $isTotal=$contrato->getIsTotal();
            $isIncorporacion=$contrato->getIsIncorporacion();
            if($isAbono==true || $isTotal==true){
                $cuota=new Cuota();

                $cuota->setContrato($contrato);
                $cuota->setNumero($numeroCuota);
                $cuota->setFechaPago($contrato->getFechaPrimeraCuota());
                $cuota->setMonto($contrato->getPrimeraCuota());

                $entityManager->persist($cuota);
                $entityManager->flush();
                $numeroCuota++;
            }
            
            if($isIncorporacion==true){

                
                $contrato->setFechaPrimeraCuota(new \DateTime($request->request->get('txtFechaIncorporacion')));
               
                $cuota=new Cuota();

                $cuota->setContrato($contrato);
                $cuota->setNumero($numeroCuota);
                $cuota->setFechaPago($contrato->getFechaPrimeraCuota());
                $cuota->setMonto($contrato->getPrimeraCuota());
                
               
                $entityManager->persist($cuota);
                $entityManager->flush();
                $numeroCuota++;
               
            }

            $primerPago=date("Y-m-".$diaPago,strtotime($fechaPrimerPago->format('Y-m-d')));
            if(date("n",strtotime($fechaPrimerPago->format('Y-m-d')))==2){
                if($diaPago==30)
                    $primerPago=date("Y-m-28",strtotime($fechaPrimerPago->format('Y-m-d')));

            }
         
            $timePrimerPago=strtotime($primerPago);

            $timeFechaActual=strtotime(date("Y-m-d"));

            if($timeFechaActual>=$timePrimerPago){

                $sumames=1;
            }else{
                if($isIncorporacion==true){
                    if(date("m",strtotime($fechaPrimerPago->format('Y-m-d')))==date("m")){
                        $sumames=1;
                    }
                }
            }

            if($contrato->getValorCuota()!=0){
                for($i=0;$i<$countCuotas;$i++){
                    $cuota=new Cuota();
                    $i_aux=$i;
                    
                    

                    $cuota->setContrato($contrato);
                    $cuota->setNumero($numeroCuota);

                    $ts = mktime(0, 0, 0, date('m',$timePrimerPago) + $sumames+$i_aux, 1,date('Y',$timePrimerPago));
                    $dia=$diaPago;
                    if(date("n",$ts)==2){
                        if($dia==30){
                            $dia=date("d",mktime(0,0,0,date('m',$timePrimerPago)+ $sumames+$i_aux+1,1,date('Y',$timePrimerPago))-24);
                        }
                    }
                    $fechaCuota=date("Y-m-d", mktime(0,0,0,date('m',$timePrimerPago) + $sumames+$i_aux,$dia,date('Y',$timePrimerPago)));
                    $cuota->setFechaPago(new \DateTime($fechaCuota));
                    $cuota->setMonto($contrato->getValorCuota());

                    
                    
                    if($i==0){
                        $contrato->setProximoVencimiento(new \DateTime($fechaCuota));
                        $entityManager->persist($contrato);
                        $entityManager->flush();
                    }
                    /*$cuotaResultToku=$toku->crearInvoice($usuario->getTokuId(),strval($contrato->getFolio()),$cuota->getMonto(),$cuota->getFechaPago()->format('Y-m-d'));
                    error_log("<br><br> Usuario : ".print_r($cuotaResultToku,true),3,"/home/micrm.cl/test/TokuWebhook_log");
                    if($cuotaResultToku==false){
                        
                    }else{
                        
                        
                        $cuotaToku=json_decode($cuotaResultToku);
                        
                        $cuota->setInvoiceId(strval($cuotaToku->id));

                    }*/
                
                
                    $entityManager->persist($cuota);
                    $entityManager->flush();
                    
                


                    $numeroCuota++;
                }
            }
    
          
            return $this->redirectToRoute('contrato_pdf',['id'=>$contrato->getId()]);
        }

        return $this->render('contrato/finalizar.html.twig', [
            'contrato' => $contrato,
            'agenda'=> $contrato->getAgenda(),
            'companias'=>$companias,
            'form' => $form->createView(),
            'tienePago'=>false,
            'juzgados'=>$juzgados,
            'pagina'=>"Revise los datos para finalizar",
            'tramitadores'=>$usuarioRepository->findByCuenta($contrato->getAgenda()->getCuenta()->getId(),['usuarioTipo'=>7,'estado'=>1]),
            'diasPagos'=>$diasPagoRepository->findAll(),
            'sucursales'=>$sucursalRepository->findBy(['cuenta'=>$contrato->getAgenda()->getCuenta()->getId()]),
            'regiones'=>$regionRepository->findAll(),
            'materias'=>$materiaRepository->findBy([],['nombre'=>'ASC']),

        ]);
    }

   
    #[Route("/{id}/pdf", name: "contrato_pdf", methods: ["GET","POST"])]
    public function pdf(Contrato $contrato,
                        ContratoTemplateRepository $contratoTemplateRepository,
                        TipoClienteRepository $tipoClienteRepository,
                        ContratoTemplateRenderer $contratoTemplateRenderer)
    {
        $this->denyAccessUnlessGranted('view','contrato');

        $entityManager = $this->entityManager;

        // Generación de cuotas: antes vivía en finalizar() (que ya no se usa al
        // contratar, ver PanelAbogadoController::contrata(), que redirige directo
        // acá). Se guarda contra recargas de esta misma página: si el contrato ya
        // tiene cuotas generadas, no se vuelven a crear.
        if ($contrato->getDetalleCuotas()->isEmpty()) {
            $countCuotas = $contrato->getCuotas();
            $fechaPrimerPago = $contrato->getFechaPrimerPago();
            $diaPago = $contrato->getDiaPago();
            $sumames = 0;
            $numeroCuota = 1;
            $isAbono = $contrato->getIsAbono();
            $isTotal = $contrato->getIsTotal();
            $isIncorporacion = $contrato->getIsIncorporacion();

            if ($isAbono == true || $isTotal == true) {
                $cuota = new Cuota();
                $cuota->setContrato($contrato);
                $cuota->setNumero($numeroCuota);
                $cuota->setFechaPago($contrato->getFechaPrimeraCuota());
                $cuota->setMonto($contrato->getPrimeraCuota());
                $entityManager->persist($cuota);
                $entityManager->flush();
                $numeroCuota++;
            }

            if ($isIncorporacion == true) {
                // fechaPrimeraCuota ya viene seteada desde la creación del contrato
                // (PanelAbogadoController::contrata(), a partir de txtFechaIncorporacion).
                $cuota = new Cuota();
                $cuota->setContrato($contrato);
                $cuota->setNumero($numeroCuota);
                $cuota->setFechaPago($contrato->getFechaPrimeraCuota());
                $cuota->setMonto($contrato->getPrimeraCuota());
                $entityManager->persist($cuota);
                $entityManager->flush();
                $numeroCuota++;
            }

            $primerPago = date("Y-m-".$diaPago, strtotime($fechaPrimerPago->format('Y-m-d')));
            if (date("n", strtotime($fechaPrimerPago->format('Y-m-d'))) == 2) {
                if ($diaPago == 30) {
                    $primerPago = date("Y-m-28", strtotime($fechaPrimerPago->format('Y-m-d')));
                }
            }

            $timePrimerPago = strtotime($primerPago);
            $timeFechaActual = strtotime(date("Y-m-d"));

            if ($timeFechaActual >= $timePrimerPago) {
                $sumames = 1;
            } else {
                if ($isIncorporacion == true) {
                    if (date("m", strtotime($fechaPrimerPago->format('Y-m-d'))) == date("m")) {
                        $sumames = 1;
                    }
                }
            }

            if ($contrato->getValorCuota() != 0) {
                for ($i = 0; $i < $countCuotas; $i++) {
                    $cuota = new Cuota();
                    $i_aux = $i;

                    $cuota->setContrato($contrato);
                    $cuota->setNumero($numeroCuota);

                    $ts = mktime(0, 0, 0, date('m', $timePrimerPago) + $sumames + $i_aux, 1, date('Y', $timePrimerPago));
                    $dia = $diaPago;
                    if (date("n", $ts) == 2) {
                        if ($dia == 30) {
                            $dia = date("d", mktime(0, 0, 0, date('m', $timePrimerPago) + $sumames + $i_aux + 1, 1, date('Y', $timePrimerPago)) - 24);
                        }
                    }
                    $fechaCuota = date("Y-m-d", mktime(0, 0, 0, date('m', $timePrimerPago) + $sumames + $i_aux, $dia, date('Y', $timePrimerPago)));
                    $cuota->setFechaPago(new \DateTime($fechaCuota));
                    $cuota->setMonto($contrato->getValorCuota());

                    if ($i == 0) {
                        $contrato->setProximoVencimiento(new \DateTime($fechaCuota));
                        $entityManager->persist($contrato);
                        $entityManager->flush();
                    }

                    $entityManager->persist($cuota);
                    $entityManager->flush();

                    $numeroCuota++;
                }
            }
        }

        $filename = sprintf('Contrato-'.$contrato->getId().'-%s.pdf',rand(0,9000));

        // Plantilla elegida por el usuario al crear el contrato (ver
        // PanelAbogadoController::contrata()). Si no se eligió ninguna (contratos
        // creados antes de esto, o sin plantillas disponibles), se usa como
        // respaldo la primera disponible de la empresa/tipoCliente; si tampoco
        // hay, se usa el twig fijo de siempre.
        $contratoTemplate = $contrato->getContratoTemplate();
        if (null === $contratoTemplate) {
            $agenda = $contrato->getAgenda();
            $tipoCliente = ($agenda && $agenda->getTipoCliente()) ? $agenda->getTipoCliente() : $tipoClienteRepository->findOneByNombre('Persona');
            $contratoTemplate = ($agenda && $tipoCliente)
                ? $contratoTemplateRepository->findActiva($agenda->getEmpresa()->getId(), $tipoCliente->getId())
                : null;
        }

        if (null !== $contratoTemplate) {
            $html = $contratoTemplateRenderer->render($contratoTemplate, $contrato);
        } else {
            $html = $this->renderView('contrato/print.html.twig', array(
                'contrato' => $contrato,
                'Titulo'=>"Contrato",

            ));
        }

        $contrato->setPdf($filename);
        $entityManager->persist($contrato);
        $entityManager->flush();



        try{
        // Configure Dompdf según sus necesidades
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'helvetica');
    
        // Crea una instancia de Dompdf con nuestras opciones
        $dompdf = new Dompdf($pdfOptions);

        $dompdf->getOptions()->setChroot(array($this->getParameter('url_raiz')));
        
        // Cargar HTML en Dompdf
        $dompdf->loadHtml($html);
        
        // (Opcional) Configure el tamaño del papel y la orientación 'vertical' o 'vertical'
        $dompdf->setPaper('letter', 'portrait');

        // Renderiza el HTML como PDF
        $dompdf->render();

        $file=$dompdf->output();
        file_put_contents($this->getParameter('url_root'). $this->getParameter('pdf_contratos').$filename,$file);
        // Envíe el PDF generado al navegador (descarga forzada)
        /*$dompdf->stream($filename, [
            "Attachment" => true
        ]);*/
        } catch (Exception $e) {
            error_log("Error generando PDF: " . $e->getMessage());
            $this->addFlash('error', "Error al generar el PDF:" . $e->getMessage());
           
        }

        return $this->redirectToRoute('contrato_index');
    }
    #[Route("/{id}/terminar", name: "contrato_terminar", methods: ["GET","POST"])]
    function terminar(Contrato $contrato,
                    DiasPagoRepository $diasPagoRepository,
                    ModuloPerRepository $moduloPerRepository,
                    Request $request,
                    ContratoFunciones $contratoFunciones): Response
    {
        $this->denyAccessUnlessGranted('create','terminos');
        $user=$this->getUser();
        
        $pagina=$moduloPerRepository->findOneByName('contrato',$user->getEmpresaActual());

        if(null !== $request->query->get('status')){
            $error_toast=$contratoFunciones->terminarContrato($contrato,$request->query->get('status'),$request->request->get('txtObservacion'));
           
            return $this->redirectToRoute('desconoce_index',['error_toast'=>$error_toast]);

        }
        
        return $this->render('contrato/show.html.twig', [
            'contrato' => $contrato,
            'agenda'=>$contrato->getAgenda(),
            'pagina'=>$pagina->getNombre(),
            'diasPagos'=>$diasPagoRepository->findAll(),
            'metodo'=>"T",
            
        ]);
    }

    #[Route("/{id}/ciudad", name: "contrato_ciudad", methods: ["GET","POST"])]
    function ciudad(Region $region, CiudadRepository $ciudadRepository,Request $request): Response
    {
        $ciudad_def=null;
       
        if(null != $request->query->get('ciudad')){
            $ciudad_def=$request->query->get('ciudad');
        }
        $ciudades=$ciudadRepository->findBy(['region'=>$region->getId()]);
        return $this->render('contrato/_ciudades.html.twig', [
            'ciudades' => $ciudades,
            'ciudad_def'=>$ciudad_def,
        ]);
    }

    #[Route("/{id}/comuna", name: "contrato_comuna", methods: ["GET","POST"])]
    function comuna(Ciudad $ciudad, ComunaRepository $comunaRepository,Request $request): Response
    {
        
        $comuna_def=null;
        if(null != $request->query->get('comuna')){
            $comuna_def=$request->query->get('comuna');
        }
        $comunas=$comunaRepository->findBy(['ciudad'=>$ciudad->getId()]);
        return $this->render('contrato/_comunas.html.twig', [
            'comunas' => $comunas,
            'comuna_def'=>$comuna_def,
        ]);
    }

    /**
     * Combo de ciudades (solo <option>, sin envolver en <select>) para actualizar
     * en vivo el <select> de ciudad del cliente propio de Convenio/Empresa al
     * cambiar de región (ver contrato/lineaTiempoConvenio.html.twig). A diferencia
     * de ciudad(), que devuelve un <select> completo para reemplazar un <div>
     * contenedor en el flujo Persona.
     */
    #[Route("/{id}/ciudad_combo", name: "contrato_ciudad_combo", methods: ["GET","POST"])]
    function ciudadCombo(Region $region, CiudadRepository $ciudadRepository): Response
    {
        return $this->render('contrato/_ciudad_combo.html.twig', [
            'ciudades' => $ciudadRepository->findBy(['region' => $region->getId()], ['nombre' => 'ASC']),
        ]);
    }

    /**
     * Combo de comunas (solo <option>) análogo a ciudadCombo(), para el <select>
     * de comuna del cliente propio de Convenio/Empresa al cambiar de ciudad.
     */
    #[Route("/{id}/comuna_combo", name: "contrato_comuna_combo", methods: ["GET","POST"])]
    function comunaCombo(Ciudad $ciudad, ComunaRepository $comunaRepository): Response
    {
        return $this->render('contrato/_comuna_combo.html.twig', [
            'comunas' => $comunaRepository->findBy(['ciudad' => $ciudad->getId()], ['nombre' => 'ASC']),
        ]);
    }
    #[Route("/{id}/linea_tiempo", name: "contrato_linea_tiempo", methods: ["GET","POST"])]
    function lineaTiempo(Contrato $contrato,
                        ModuloPerRepository $moduloPerRepository,
                        MateriaRepository $materiaRepository,
                        PaisRepository $paisRepository): Response
    {
        $this->denyAccessUnlessGranted('create','linea_tiempo');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('linea_tiempo',$user->getEmpresaActual());

        $cuenta=$contrato->getAgenda()->getCuenta();

        $tipoCliente = $contrato->getAgenda()->getTipoClienteNombre();

        // Cada causa lleva su propia materia; los combos de servicio/corte/letras del
        // modal de edición se cargan por AJAX según la materia de la causa editada.
        if ($tipoCliente === 'Convenio' || $tipoCliente === 'Empresa') {
            // Convenio/Empresa: el contrato agrupa varios clientes propios, cada uno
            // con sus propias causas (Causa::$cliente). Se listan agrupando las
            // causas de la agenda por cliente.
            $clientes = [];
            foreach ($contrato->getAgenda()->getCausas() as $causa) {
                $cliente = $causa->getCliente();
                if ($cliente === null) {
                    continue;
                }
                if (!isset($clientes[$cliente->getId()])) {
                    $clientes[$cliente->getId()] = [
                        'cliente' => $cliente,
                        'causas' => [],
                    ];
                }
                $clientes[$cliente->getId()]['causas'][] = $causa;
            }

            $formNuevoCliente = $this->createForm(ClienteConvenioType::class, new Cliente());
            // Nacionalidad: Cliente::$nacionalidad es un string, así que se arma un
            // combo con los nombres de Pais en vez de una relación. Pais es un
            // catálogo global (mismo listado para todas las empresas), sin filtrar
            // por tenant.
            $paisChoices = [];
            foreach ($paisRepository->findBy([], ['orden' => 'ASC']) as $pais) {
                $paisChoices[$pais->getNombre()] = $pais->getNombre();
            }
            $formNuevoCliente->add('nacionalidad', ChoiceType::class, [
                'choices' => $paisChoices,
                'required' => false,
            ]);
            if ($tipoCliente === 'Convenio') {
                // El sexo y el estado civil solo aplican a Convenio, no a Empresa
                // (ver diseño en nuevoClienteConvenio()).
                $formNuevoCliente->add('sexo', ChoiceType::class, [
                    'choices' => [
                        'Masculino' => 'Masculino',
                        'Femenino' => 'Femenino',
                    ],
                    'required' => false,
                ]);
                $formNuevoCliente->add('estadoCivil', null, ['required' => false]);
            }

            return $this->render('contrato/lineaTiempoConvenio.html.twig', [
                'contrato' => $contrato,
                'pagina'=>$pagina->getNombre(),
                'juzgados' => $cuenta->getJuzgadoCuentas(),
                'clientes' => $clientes,
                'tipoCliente' => $tipoCliente,
                'formNuevoCliente' => $formNuevoCliente->createView(),
                // Materia es un catálogo global: mismo listado para todas las empresas.
                'materias' => $materiaRepository->findBy([], ['nombre' => 'ASC']),
            ]);
        }

        return $this->render('contrato/lineaTiempo.html.twig', [
            'contrato' => $contrato,
            'pagina'=>$pagina->getNombre(),
            'juzgados' => $cuenta->getJuzgadoCuentas(),
            'materias' => $materiaRepository->findBy([], ['nombre' => 'ASC']),
        ]);
    }

    #[Route("/{id}/convenio/cliente", name: "contrato_convenio_nuevo_cliente", methods: ["GET","POST"])]
    function nuevoClienteConvenio(Contrato $contrato,
                                Request $request,
                                ClienteRepository $clienteRepository,
                                MateriaRepository $materiaRepository,
                                MateriaEstrategiaRepository $materiaEstrategiaRepository,
                                JuzgadoRepository $juzgadoRepository,
                                PaisRepository $paisRepository): Response
    {
        $this->denyAccessUnlessGranted('create','linea_tiempo');
        $tipoCliente = $contrato->getAgenda()->getTipoClienteNombre();

        $cliente = new Cliente();
        $form = $this->createForm(ClienteConvenioType::class, $cliente);

        // Nacionalidad: Cliente::$nacionalidad es un string, así que se arma un
        // combo con los nombres de Pais en vez de una relación. Pais es un catálogo
        // global (mismo listado para todas las empresas), sin filtrar por tenant.
        $paisChoices = [];
        foreach ($paisRepository->findBy([], ['orden' => 'ASC']) as $pais) {
            $paisChoices[$pais->getNombre()] = $pais->getNombre();
        }
        $form->add('nacionalidad', ChoiceType::class, [
            'choices' => $paisChoices,
            'required' => false,
        ]);

        // El sexo y el estado civil solo aplican a clientes de tipo Convenio (no a
        // Empresa), igual que ya se agrega dinámicamente el sexo de Usuario en
        // AdministradorCuentasController/AdministradoresController.
        if ($tipoCliente === 'Convenio') {
            $form->add('sexo', ChoiceType::class, [
                'choices' => [
                    'Masculino' => 'Masculino',
                    'Femenino' => 'Femenino',
                ],
                'required' => false,
            ]);
            $form->add('estadoCivil', null, ['required' => false]);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // La causa es obligatoria al crear el cliente: sin una materia válida no
            // se crea ni el cliente ni la causa (mismos campos que ya usa
            // CausaController::agregar()).
            $materiaEstrategia = null;
            if ($request->request->get('cboSubMateria')) {
                $materiaEstrategia = $materiaEstrategiaRepository->find($request->request->get('cboSubMateria'));
            }

            $materia = null;
            if ($request->request->get('cboMateria')) {
                $materia = $materiaRepository->find($request->request->get('cboMateria'));
            }
            if ($materia === null && $materiaEstrategia !== null) {
                $materia = $materiaEstrategia->getMateria();
            }

            if ($materia === null) {
                $this->addFlash('error', 'Debe ingresar la materia de la causa: es obligatoria para crear el cliente.');
                return $this->redirectToRoute('contrato_linea_tiempo', ['id' => $contrato->getId()]);
            }

            $entityManager = $this->entityManager;

            // Reutilizamos un cliente ya existente por RUT en vez de duplicarlo. El
            // cliente del convenio NO se asocia a Contrato::$cliente (ese campo sigue
            // siendo exclusivo del caso Persona).
            $clienteExistente = $clienteRepository->findOneByRut($cliente->getRut());
            if ($clienteExistente !== null) {
                $clienteExistente->setNombre($cliente->getNombre());
                $clienteExistente->setCorreo($cliente->getCorreo());
                $clienteExistente->setTelefono($cliente->getTelefono());
                $clienteExistente->setDireccion($cliente->getDireccion());
                $clienteExistente->setClaveUnica($cliente->getClaveUnica());
                $clienteExistente->setNacionalidad($cliente->getNacionalidad());
                $clienteExistente->setRegion($cliente->getRegion());
                $clienteExistente->setCiudad($cliente->getCiudad());
                $clienteExistente->setComuna($cliente->getComuna());
                $clienteExistente->setEstadoCivil($cliente->getEstadoCivil());
                $clienteExistente->setReunion($cliente->getReunion());
                if ($tipoCliente === 'Convenio') {
                    $clienteExistente->setSexo($form->has('sexo') ? $form->get('sexo')->getData() : null);
                }
                $cliente = $clienteExistente;
            } elseif ($tipoCliente === 'Convenio') {
                $cliente->setSexo($form->has('sexo') ? $form->get('sexo')->getData() : null);
            }

            $entityManager->persist($cliente);
            $entityManager->flush();

            $causa = new Causa();
            $causa->setEstado(1);
            $causa->setAgenda($contrato->getAgenda());
            $causa->setCliente($cliente);
            $causa->setMateria($materia);

            if ($materiaEstrategia !== null && $materiaEstrategia->getMateria()->getId() === $materia->getId()) {
                $causa->setMateriaEstrategia($materiaEstrategia);
            }
            if ($request->request->get('txtLetra')) {
                $causa->setLetra($request->request->get('txtLetra'));
            }
            if ($request->request->get('txtRol')) {
                $causa->setRol($request->request->get('txtRol'));
            }
            if ($request->request->get('txtAnio')) {
                $causa->setAnio($request->request->get('txtAnio'));
            }
            // causaNombre no admite null en la base de datos: igual que en
            // CausaController::agregar(), se setea aunque venga vacío.
            $causa->setCausaNombre((string) $request->request->get('txtCaratulado'));
            if ($request->request->get('juzgado')) {
                $causa->setJuzgado($juzgadoRepository->find( $request->request->get('juzgado')));
            }

            $entityManager->persist($causa);
            $entityManager->flush();

            return $this->redirectToRoute('contrato_linea_tiempo', ['id' => $contrato->getId()]);
        }

        // El formulario vive embebido en el modal de contrato/lineaTiempoConvenio.html.twig
        // (ver lineaTiempo()); ante un GET directo o una validación fallida, volvemos
        // ahí en vez de mantener una plantilla aparte solo para mostrar errores.
        if ($form->isSubmitted()) {
            $this->addFlash('error', 'No se pudo crear el cliente: revise los datos ingresados.');
        }

        return $this->redirectToRoute('contrato_linea_tiempo', ['id' => $contrato->getId()]);
    }

    #[Route("/{id}/linea_tiempo_detalle", name: "contrato_linea_tiempo_detalle", methods: ["GET","POST"])]
    function lineaTiempoDetalle(Causa $causa, 
    LineaTiempoTerminadaRepository $lineaTiempoTerminadaRepository,
    ModuloPerRepository $moduloPerRepository,
    CausaObservacionRepository $causaObservacionRepository): Response
    {
        $this->denyAccessUnlessGranted('create','linea_tiempo');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('linea_tiempo',$user->getEmpresaActual());
        $fechaUltimoIngreso=$causa->getFechaUltimoIngreso();
        
        
        $causa->setFechaUltimoIngreso(new DateTime(date('Y-m-d h:i:s')));
        $em=$this->entityManager;
        $em->persist($causa);
        $em->flush();

        $contrato=$causa->getAgenda()->getContrato();
        $terminados=$lineaTiempoTerminadaRepository->findBy(['causa'=>$causa->getId(),'estado'=>1]);
        $LineaTiempoTerminados=$lineaTiempoTerminadaRepository->findBy(['causa'=>$causa->getId()]);
        return $this->render('contrato/lineaTiempoDetalle.html.twig', [
            'causa' => $causa,
            'contrato'=>$contrato,
            'terminados'=>$terminados,
            'lineaTiempoTerminados'=>$LineaTiempoTerminados,
            'pagina'=>$pagina->getNombre(),
            'observaciones'=>$causaObservacionRepository->findBy(['contrato'=>$contrato,'causa'=>$causa],['fechaRegistro'=>'Desc']),
            'fechaUltimoIngreso'=>$fechaUltimoIngreso
            
            
        ]);
    }

    #[Route("/{id}/linea_tiempo_observacion", name: "contrato_linea_tiempo_observacion", methods: ["GET","POST"])]
    function lineaTiempoObservacion(Causa $causa, 
                                    LineaTiempoEtapasRepository $lineaTiempoEtapaRepository, 
                                    LineaTiempoTerminadaRepository $lineaTiempoTerminadaRepository ,
                                    Request $request): Response
    {
        //$this->denyAccessUnlessGranted('create','terminos');
        $user=$this->getUser();
        $contrato=$causa->getAgenda()->getContrato();
        
        $terminada=$lineaTiempoTerminadaRepository->findOneBy(['causa'=>$causa->getId(),'estado'=>1],array('id'=>'desc'));

        if($request->request->get('hdEtapa')!=null){
            $etapa1=$lineaTiempoEtapaRepository->find($request->request->get('hdEtapa'));
            
        }

        $terminada!=null?$etapaInicio=$terminada->getLineaTiempoEtapas()->getId():$etapaInicio=0;

        // La causa necesita un servicio (materiaEstrategia) con línea de tiempo para
        // avanzar etapas.
        $materiaEstrategia=$causa->getMateriaEstrategia();
        $lineaTiempo=$materiaEstrategia ? $materiaEstrategia->getEstrategiaJuridica()->getLineaTiempo() : null;
        if($lineaTiempo===null){
            $this->addFlash('error','La causa no tiene un servicio con línea de tiempo asignada.');
            return $this->redirectToRoute('contrato_linea_tiempo_detalle',['id'=>$causa->getId()]);
        }

        $etapas=$lineaTiempoEtapaRepository->findByRango($etapaInicio,$etapa1->getId(), $lineaTiempo->getId());
        

        foreach ($etapas as $etapa) {
            $terminado=new LineaTiempoTerminada();
            $terminado->setLineaTiempoEtapas($etapa);
            $terminado->setFecha(new \DateTime(date('Y-m-d H:i:s')));
            $terminado->setUsuarioRegistro($user);
            $terminado->setCausa($causa);

            if($request->request->get('txtObservacion')!=null){
                $terminado->setObservacion($request->request->get('txtObservacion'));
            }
            if($request->request->get('hdEstado')!=null){
                $terminado->setEstado($request->request->get('hdEstado'));
            }
            
            
            $em=$this->entityManager;
            $em->persist($terminado);
            $em->flush();
        }

            
        
        
        return $this->redirectToRoute('contrato_linea_tiempo_detalle',['id'=>$causa->getId()]);
    }

    #[Route("/{id}/observacion", name: "contrato_observacion", methods: ["GET","POST"])]
    function observacion(Causa $causa, 
                        LineaTiempoEtapasRepository $lineaTiempoEtapaRepository, 
                        LineaTiempoTerminadaRepository $lineaTiempoTerminadaRepository ,
                        Request $request): Response
    {
        //$this->denyAccessUnlessGranted('create','terminos');
        $user=$this->getUser();
        $contrato=$causa->getAgenda()->getContrato();

        if($request->request->get('txtObservacion')!=null){
            $observacion=new CausaObservacion();
            $observacion->setContrato($contrato);
            $observacion->setObservacion($request->request->get('txtObservacion'));
            $observacion->setFechaRegistro(new DateTime(date('Y-m-d H:i:s')));
            $observacion->setUsuarioRegistro($user);
            $observacion->setCausa($causa);
            
            $em=$this->entityManager;
            $em->persist($observacion);
            $em->flush();

        }

        return $this->redirectToRoute('contrato_linea_tiempo',['id'=>$contrato->getId()]);
    }

    #[Route("/{id}/historial_campo/{campo}", name: "contrato_historial_campo", methods: ["GET"])]
    public function historialCampo(Contrato $contrato,
                                    string $campo,
                                    ClienteHistorialRepository $clienteHistorialRepository): Response
    {
        $this->denyAccessUnlessGranted('edit','modificar_contrato');

        if (!isset(ClienteHistorialRepository::CAMPOS_CONSULTABLES[$campo])) {
            throw $this->createNotFoundException('Campo de historial no válido.');
        }

        $cliente = $contrato->getCliente();

        return $this->render('contrato/_historial_campo.html.twig', [
            'etiqueta' => ClienteHistorialRepository::CAMPOS_CONSULTABLES[$campo],
            'campo' => $campo,
            'historial' => $cliente ? $clienteHistorialRepository->findHistorialCampo($cliente, $campo) : [],
        ]);
    }
    #[Route("/{id}/observacion_modal", name: "contrato_observacion_modal", methods: ["GET"])]
    public function observacionModal(Contrato $contrato,
                                    ContratoObservacionRepository $contratoObservacionRepository,
                                    CausaObservacionRepository $causaObservacionRepository,
                                    ClienteHistorialRepository $clienteHistorialRepository): Response
    {
        $this->denyAccessUnlessGranted('edit','modificar_contrato');

        $tieneObservaciones = $contratoObservacionRepository->count(['contrato'=>$contrato]) > 0
            || $causaObservacionRepository->count(['contrato'=>$contrato]) > 0;

        $cliente = $contrato->getCliente();

        return $this->render('contrato/_observacion.html.twig', [
            'contrato' => $contrato,
            'tieneObservaciones' => $tieneObservaciones,
            // Valores ya usados antes en cada campo, para avisar mientras se escribe.
            'valoresHistorial' => $cliente ? $clienteHistorialRepository->valoresPorCampo($cliente) : [],
        ]);
    }

    #[Route("/{id}/editar_observacion", name: "contrato_observacion_editar", methods: ["POST"])]
    public function editarObservacion(Request $request,
                                    Contrato $contrato,
                                    ContratoObservacionRepository $contratoObservacionRepository,
                                    CausaObservacionRepository $causaObservacionRepository): Response
    {
         $this->denyAccessUnlessGranted('edit','modificar_contrato');
        $user = $this->getUser();

        $tieneObservaciones = $contratoObservacionRepository->count(['contrato'=>$contrato]) > 0
            || $causaObservacionRepository->count(['contrato'=>$contrato]) > 0;

        $entityManager = $this->entityManager;

        $cliente = $contrato->getCliente();

        if (!$cliente) {
            return new JsonResponse(['success' => false, 'message' => 'El contrato no tiene un cliente asociado.'], 400);
        }

        $txtNombre = trim((string) $request->request->get('txtNombre'));
        $txtEmail = trim((string) $request->request->get('txtEmail'));
        $txtTelefono = trim((string) $request->request->get('txtTelefono'));
        $txtDireccion = trim((string) $request->request->get('txtDireccion'));

        if ($txtNombre === '' || $txtEmail === '' || $txtTelefono === '' || $txtDireccion === '') {
            return new JsonResponse(['success' => false, 'message' => 'Debe completar nombre, correo, teléfono y dirección.'], 400);
        }

        // En el historial se guarda solo el dato que cambia: los campos que no se
        // modificaron quedan en null en la fila de cliente_historial, y si no cambió
        // nada no se genera fila.
        $clienteHistorial = new ClienteHistorial();
        $huboCambios = false;

        if ($txtNombre !== (string) $cliente->getNombre()) {
            $clienteHistorial->setNombre($cliente->getNombre());
            $cliente->setNombre($txtNombre);
            $huboCambios = true;
        }

        if ($txtEmail !== (string) $cliente->getCorreo()) {
            $clienteHistorial->setCorreo($cliente->getCorreo());
            $cliente->setCorreo($txtEmail);
            $huboCambios = true;
        }

        if ($txtTelefono !== (string) $cliente->getTelefono()) {
            $clienteHistorial->setTelefono($cliente->getTelefono());
            $cliente->setTelefono($txtTelefono);
            $huboCambios = true;
        }

        if ($txtDireccion !== (string) $cliente->getDireccion()) {
            $clienteHistorial->setDireccion($cliente->getDireccion());
            $cliente->setDireccion($txtDireccion);
            $huboCambios = true;
        }

        if ($huboCambios) {
            $clienteHistorial->setCliente($cliente);
            $clienteHistorial->setFechaModificacion(new \DateTime());
            $clienteHistorial->setUsuarioModificacion($user->getNombre());
            $entityManager->persist($clienteHistorial);
            $entityManager->persist($cliente);
        }

        if (!$tieneObservaciones) {

            $contrato->setObservacion($request->request->get('txtObservacion'));
            
        }

        
        $entityManager->persist($contrato);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }


    #[Route("/{id}/modificar_servicio", name: "contrato_modificar_servicio", methods: ["GET","POST"])]
    function modificarServicio(Causa $causa,Request $request,
                                MateriaEstrategiaRepository $materiaEstrategiaRepository,
                                JuzgadoRepository $juzgadoRepository,
                                CorteRepository $corteRepository,
                                JuzgadoCuentaRepository $juzgadoCuentaRepository)
    {

        
        if($request->request->get('juzgado')!=null){            
            $causa->setJuzgado($juzgadoRepository->find($request->request->get('juzgado')));
           
        }
        if($request->request->get('corte')!=null){            
            $causa->setCorte($corteRepository->find($request->request->get('corte')));           
        }
        if($request->request->get('txtNombreCausa')!=null){
            $causa->setIdCausa($request->request->get('txtNombreCausa'));
        }
        
        if($request->request->get('txtCaratulado')!=null){
            $causa->setCausaNombre($request->request->get('txtCaratulado'));
        }

        if($request->request->get('txtLetra')!=null){
            $causa->setLetra($request->request->get('txtLetra'));
        }
        if($request->request->get('txtRol')!=null){
            $causa->setRol($request->request->get('txtRol'));
        }
        if($request->request->get('txtAnio')!=null){
            $causa->setAnio($request->request->get('txtAnio'));
        }

        $entity=$this->entityManager;
        $entity->persist($causa);
        $entity->flush();
        
        return $this->redirectToRoute('contrato_linea_tiempo',['id'=>$causa->getAgenda()->getContrato()->getId()]);
    }

    #[Route("/{id}/anexos", name: "contrato_anexos", methods: ["GET","POST"])]
    public function anexos(Contrato $contrato, Request $request): Response
    {

        return $this->render('contrato/_anexos.html.twig',[
            'contrato'=>$contrato
        ]);
    }

    #[Route("/{id}", name: "contrato_delete", methods: ["DELETE"])]
    public function delete(Request $request, Contrato $contrato,AgendaStatusRepository $agendaStatusRepository): Response
    {
        $this->denyAccessUnlessGranted('full','contrato');
        if ($this->isCsrfTokenValid('delete'.$contrato->getId(), $request->request->get('_token'))) {
            $entityManager = $this->entityManager;
            $agenda=$contrato->getAgenda();
            $agenda->setStatus($agendaStatusRepository->find('5'));

            $entityManager->persist($agenda);
            $entityManager->flush();

            $contrato->setAgenda(null);
            $entityManager->persist($contrato);
            $entityManager->flush();
            $contratoRoles=$contrato->getContratoRols();
            foreach($contratoRoles as $contratoRol){
                $contrato->removeContratoRol($contratoRol);
            }
            $entityManager->remove($contrato);
            $entityManager->flush();
        }

        return $this->redirectToRoute('contrato_index');
    }


    
    public function pdf2(Contrato $contrato)
    {
        $this->denyAccessUnlessGranted('view','contrato');
        $filename = sprintf('Contrato-'.$contrato->getId().'-%s.pdf',rand(0,9000));
       
        $html = $this->renderView('contrato/print.html.twig', array(
            'contrato' => $contrato,
            'Titulo'=>"Contrato"
        ));

        $entityManager = $this->entityManager;
        $contrato->setPdf($filename);
        $entityManager->persist($contrato);
        $entityManager->flush();

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
        /*$dompdf->stream($filename, [
            "Attachment" => true
        //]);*/
    }
    #[Route("/audio_upload", name: "contrato_audio_upload", methods: ["GET","POST"])]
    public function upload(Request $request, ContratoRepository $contratoRepository){
        $user=$this->getUser();
        
        print_r($_FILES);
        $brochureFile = $_FILES['file']['name'];
        $contratoId=$_POST['hdContrato'];
        echo "Comienzo a cargar";
        // this condition is needed because the 'brochure' field is not required
        // so the PDF file must be processed only when a file is uploaded
        if ($brochureFile) {
            
            $contrato=$contratoRepository->find($contratoId);

            //$nombre=$contrato->getFolio().rand(0,1000).".mp3";

            $arrayNombre=explode(".",$brochureFile);
            $nombre=$contrato->getFolio()."-".$arrayNombre[0].".mp3";
            $nombre_original=$contrato->getFolio()."-".$brochureFile;


            $contratoAudio=new ContratoAudios();
            $contratoAudio->setUsuarioRegistro($user);
            $contratoAudio->setContrato($contrato);
            $contratoAudio->setUrl($nombre);
            $entityManager = $this->entityManager;

            $entityManager->persist($contratoAudio);
            $entityManager->flush();
           
            $fichero_subido = $this->getParameter('url_root').
            $this->getParameter('audio_contratos') . $nombre_original;
            
           /* if (move_uploaded_file($_FILES['file']['tmp_name'][0], $fichero_subido)) {
                echo "El fichero es válido y se subió con éxito.\n";
            } else {
                echo "¡Posible ataque de subida de ficheros!\n";
            }*/

           
            $source=$_FILES['file']['tmp_name'];
            try{
                if(move_uploaded_file($source, $fichero_subido))
                {
                   // $message ='Audio cagado con exito';

                    $message="Toast.fire({
                        icon: 'success',
                        title: 'Audio Cargado con exito!!'
                      })";
                }
                else
                {
                    //$message = 'Ha Ocurrido un problema, el audio no ha sido cargado';
                    $message="Toast.fire({
                        icon: 'danger',
                        title: 'Ocurrio un error, el Audio no ha sido cargado!!'
                      })";
                }
                if($arrayNombre[1] != "mp3"){

               
                    $process=new Process([
                                "ffmpeg","-i",$this->getParameter('url_root').
                    $this->getParameter('audio_contratos').$nombre_original,$this->getParameter('url_root').
                    $this->getParameter('audio_contratos').$nombre
                    
                                ]);

                    $process->run();


                    
                    // executes after the command finishes
                    if (!$process->isSuccessful()) {
                        //throw new ProcessFailedException($process);

                        echo "error convertir ";
                    }
    
                    echo $process->getOutput();

                    $process=new Process([
                        "rm","-rf",$this->getParameter('url_root').
                    $this->getParameter('audio_contratos').$nombre_original
                        ]);

                    $process->run();

                    echo $process->getOutput();
                }
                
                echo $message;
                
            }catch(Exception $e){
                echo $e->getMessage();
            }
                        /*
            $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',$originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

            // Move the file to the directory where brochures are stored
            echo $this->getParameter('url_root').
            $this->getParameter('pagos');
            $brochureFile->move($this->getParameter('url_root').
                $this->getParameter('pagos'),
                $newFilename
            );
            */
        }

        
        return $this->redirectToRoute('contrato_index',['error_toast'=>$message]);
    }
    #[Route("/{id}/audio_delete", name: "contrato_audio_delete", methods: ["GET","POST"])]
    public function audiodelete(Contrato $contrato, Request $request, ContratoAudiosRepository $contratoAudiosRepository){
        $contratoAudios=$contratoAudiosRepository->findBy(['contrato'=>$contrato]);
        $entityManager=$this->entityManager;
        $message="";
        foreach ($contratoAudios as $contratoAudio) {
            

            $message="Toast.fire({
                icon: 'success',
                title: 'Audio eliminado'
              })";

              $process=new Process([
                "rm","-rf",$this->getParameter('url_root').
            $this->getParameter('audio_contratos').$contratoAudio->getUrl()
                ]);

            $process->run();

            echo $process->getOutput();

            $entityManager->remove($contratoAudio);
            $entityManager->flush();
        }

        return $this->redirectToRoute('contrato_index',['error_toast'=>$message]);
    }



}
