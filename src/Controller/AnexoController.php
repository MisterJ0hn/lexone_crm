<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Causa;
use App\Entity\Contrato;
use App\Entity\ContratoAnexo;
use App\Entity\Cuota;
use App\Form\ContratoAnexoType;
use App\Repository\CausaRepository;
use App\Repository\ContratoAnexoRepository;
use App\Repository\CuotaRepository;
use App\Repository\DiasPagoRepository;
use App\Repository\JuzgadoCuentaRepository;
use App\Repository\JuzgadoRepository;
use App\Repository\MateriaEstrategiaRepository;
use App\Repository\MateriaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route("/anexo")]
class AnexoController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }
    #[Route("/{id}", name: "anexo_index", methods: ["GET","POST"])]
    public function index(Contrato $contrato,ContratoAnexoRepository $contratoAnexoRepository ): Response
    {
        $this->denyAccessUnlessGranted('view','anexo');

        return $this->render('anexo/index.html.twig', [
            'pagina' => 'Anexos',
            'contrato'=>$contrato,
            'contratoAnexos'=>$contratoAnexoRepository->findBy(['contrato'=>$contrato, 'estado'=>null]),
            'contratoAnexosNulo'=>$contratoAnexoRepository->findBy(['contrato'=>$contrato, 'estado'=>0])
        ]);
    }

    #[Route("/{id}/new", name: "anexo_new", methods: ["GET","POST"])]
    public function crear(Contrato $contrato): Response
    {
        $this->denyAccessUnlessGranted('create','anexo');
        return $this->render('anexo/crearAnexo.html.twig', [
            'pagina' => 'Nuevo Anexo',
            'contrato'=>$contrato
        ]);
    }

    #[Route("/{id}/causas", name: "anexo_causas", methods: ["GET","POST"])]
    public function causas(Contrato $contrato,
                            CuotaRepository $cuotaRepository,
                            JuzgadoRepository $juzgadoRepository,
                            DiasPagoRepository $diasPagoRepository,
                            ContratoAnexoRepository $contratoAnexoRepository,
                            MateriaEstrategiaRepository $materiaEstrategiaRepository,
                            MateriaRepository $materiaRepository,
                            JuzgadoCuentaRepository $juzgadoCuentaRepository,
                            CausaRepository $causaRepository,
                            Request $request): Response
    {
        $this->denyAccessUnlessGranted('create','anexo');

        $juzgados=$juzgadoRepository->findAll();
        $cuotas=$cuotaRepository->findBy(['contrato'=>$contrato, 'anular'=>null]);
        $contratoAnexo=new ContratoAnexo();
        //$contratoAnexo->setVigencia();
        $contratoAnexo->setIsDesiste(false);
        $contratoAnexo->setFechaCreacion(new \DateTime(date('Y-m-d')));
        $contratoAnexo->setFechaPrimerPago(new \DateTime(date('Y-m-d')));
        $contratoAnexo->setContrato($contrato);
        $contratoAnexo->setTipoAnexo(1);
        $form = $this->createForm(ContratoAnexoType::class, $contratoAnexo);

        $ultAnexo=$contratoAnexoRepository->findOneBy(['contrato'=>$contrato,'isDesiste'=>false],['folio'=>'desc']);
            
        $folio=1;
        $ultFolio=null;
        if($ultAnexo){
            $folio=$ultAnexo->getFolio()+1;
            $ultFolio=$ultAnexo->getFolio();

        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contratoAnexo->setDiasPago($request->request->get('chkDiasPago'));
            
            $contratoAnexo->setFolio($folio);

            $this->entityManager->flush();
            
            
            $entityManager = $this->entityManager;

            $entityManager->persist($contratoAnexo);
            $entityManager->flush();

           $materiasCausa=$request->request->get('hdMateria');
            $submaterias=$request->request->get('hdSubMateria');
            $letra = $request->request->get('hdLetraCausa');
            $rol = $request->request->get('hdRolCausa');
            $anio = $request->request->get('hdAnioCausa');
            $caratulados=$request->request->get('hdCaratulado');
            $hdjuzgados=$request->request->get('hdJuzgado');

            // Materia es un catálogo global: mismo listado para todas las empresas.
            $materiasHabilitadas=[];
            foreach ($materiaRepository->findAll() as $m) {
                $materiasHabilitadas[$m->getId()]=true;
            }

            $this->calularCuotas($contratoAnexo);

            $countCausa=count($submaterias);
            for ($i=0; $i < $countCausa ; $i++) {

                $causa=new Causa();
                $causa->setEstado(1);
                $causa->setAgenda($contrato->getAgenda());
               // echo $contratoAnexo->getId();
                $causa->setAnexo($contratoAnexoRepository->find($contratoAnexo->getId()));

                $materia=null;
                if(isset($materiasCausa[$i]) && $materiasCausa[$i]!=="" && isset($materiasHabilitadas[(int)$materiasCausa[$i]])){
                    $materia=$materiaRepository->find($materiasCausa[$i]);
                }
                $materiaEstrategia=null;
                if(null !== $submaterias[$i] && $submaterias[$i]!==""){
                    $materiaEstrategia=$materiaEstrategiaRepository->find($submaterias[$i]);
                    if($materia===null && $materiaEstrategia!==null){
                        $materia=$materiaEstrategia->getMateria();
                    }
                }
                if($materia===null){
                    continue;
                }
                $causa->setMateria($materia);
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
                if($materiaEstrategia!==null && $materiaEstrategia->getMateria()->getId()===$materia->getId()){
                    $causa->setMateriaEstrategia($materiaEstrategia);
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

               /* //$contrato->setVigenciaUltAnexo($contratoAnexo->getVigencia());
                //$contrato->setFechaCreacionUltAnexo($contratoAnexo->getFechaCreacion());
                $entityManager->persist($contrato);
                $entityManager->flush();
                
                
                //$etapa_pendiente = $lineaTiempoEtapasRepository->obtenerEtapaPendiente($causa->getId(),$causa->getMateriaEstrategia()->getEstrategiaJuridica()->getLineaTiempo()->getId());
                //if($etapa_pendiente){
                //    $causa->setEtapaPendiente($etapa_pendiente->getNombre());
                //}*/
                 $entityManager->persist($causa);
                $entityManager->flush();
            }


            return $this->redirectToRoute('anexo_pdf',['id'=>$contratoAnexo->getId()]);

            
        }

        return $this->render('anexo/_anexoCausas.html.twig', [
            'form'=> $form->createView(),
            'pagina' => 'Agregar causa',
            'ultFolio'=>$ultFolio,
            'ultAnexo'=>$ultAnexo,
            'contrato'=>$contrato,
            'cuotas'=>$cuotas,
            'juzgados'=>$juzgados,
            'diasPagos'=>$diasPagoRepository->findAll(),
            'contrato_causas'=>$causaRepository->findBy(['agenda'=>$contrato->getAgenda(),'estado'=>1]),
            'materias'=>$materiaRepository->findBy([],['nombre'=>'ASC']),
        ]);

    }

  

    #[Route("/{id}/extender", name: "anexo_extender", methods: ["GET","POST"])]
    public function extender(Contrato $contrato, 
                            CuotaRepository $cuotaRepository, 
                            Request $request,
                            DiasPagoRepository $diasPagoRepository,
                            ContratoAnexoRepository $contratoAnexoRepository
                            ): Response
    {  
        $this->denyAccessUnlessGranted('create','anexo');
        $cuotas=$cuotaRepository->findBy(['contrato'=>$contrato, 'anular'=>null]);
        $contratoAnexo=new ContratoAnexo();
        //$contratoAnexo->setVigencia(24);
        $contratoAnexo->setIsDesiste(false);
        $contratoAnexo->setFechaCreacion(new \DateTime(date('Y-m-d')));
        $contratoAnexo->setFechaPrimerPago(new \DateTime(date('Y-m-d')));

        $contratoAnexo->setContrato($contrato);
        $contratoAnexo->setTipoAnexo(2);
        $form = $this->createForm(ContratoAnexoType::class, $contratoAnexo);
        
        $ultAnexo=$contratoAnexoRepository->findOneBy(['contrato'=>$contrato,'isDesiste'=>false],['folio'=>'desc']);

        $folio=1;
        $ultFolio=null;
        if($ultAnexo){
            $folio=$ultAnexo->getFolio()+1;
            $ultFolio=$ultAnexo->getFolio();

        }
        $contratoAnexo->setFolio($folio);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contratoAnexo->setDiasPago($request->request->get('chkDiasPago'));

            $entityManager = $this->entityManager;
            $entityManager->persist($contratoAnexo);
            $entityManager->flush();

            

            //Anulando cuotas anteriores
            $detalleCuotas=$contrato->getDetalleCuotas();
            foreach($detalleCuotas as $detalleCuota){
                $detalleCuota->setAnular(1);
                $entityManager->persist($detalleCuota);
                $entityManager->flush();
            }

            $this->calularCuotas($contratoAnexo);
            

            return $this->redirectToRoute('anexo_pdf',['id'=>$contratoAnexo->getId()]);
        }

        return $this->render('anexo/_anexoExtender.html.twig', [
            'pagina' => 'Extensión del plazo',
            'form'=> $form->createView(),
            'contrato'=>$contrato,
            'cuotas'=>$cuotas,
            'ultFolio'=>$ultFolio,
            'ultAnexo'=>$ultAnexo,
            'diasPagos'=>$diasPagoRepository->findAll(),
            
        ]);

    }

    #[Route("/{id}/renegociar", name: "anexo_renegociar", methods: ["GET","POST"])]
    public function renegociar(Contrato $contrato, 
                                CuotaRepository $cuotaRepository, 
                                Request $request,
                                DiasPagoRepository $diasPagoRepository,
                                ContratoAnexoRepository $contratoAnexoRepository): Response
                                {

        $this->denyAccessUnlessGranted('create','anexo');
       
        $cuotas=$cuotaRepository->findBy(['contrato'=>$contrato, 'anular'=>null]);
        $contratoAnexo=new ContratoAnexo();
        //$contratoAnexo->setVigencia(24);
        $contratoAnexo->setIsDesiste(false);
        $contratoAnexo->setFechaCreacion(new \DateTime(date('Y-m-d')));
        $contratoAnexo->setFechaPrimerPago(new \DateTime(date('Y-m-d')));
        $contratoAnexo->setContrato($contrato);
        $contratoAnexo->setTipoAnexo(3);
        $form = $this->createForm(ContratoAnexoType::class, $contratoAnexo);
        
        $ultAnexo=$contratoAnexoRepository->findOneBy(['contrato'=>$contrato,'isDesiste'=>false],['folio'=>'desc']);

        $folio=1;
        $ultFolio=null;
        if($ultAnexo){
            $folio=$ultAnexo->getFolio()+1;
            $ultFolio=$ultAnexo->getFolio();

        }
        $contratoAnexo->setFolio($folio);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->entityManager;
            
            $contratoAnexo->setDiasPago($request->request->get('chkDiasPago'));

            $entityManager->persist($contratoAnexo);
            $entityManager->flush();

            //Anulando cuotas anteriores
            $detalleCuotas=$contrato->getDetalleCuotas();
            foreach($detalleCuotas as $detalleCuota){
                $detalleCuota->setAnular(1);
                $entityManager->persist($detalleCuota);
                $entityManager->flush();
            }
            $this->calularCuotas($contratoAnexo);

            

            return $this->redirectToRoute('anexo_pdf',['id'=>$contratoAnexo->getId()]);
        }


        return $this->render('anexo/_anexoRenegociar.html.twig', [
            'pagina' => 'Renegociación',
            'form'=> $form->createView(),
            'contrato'=>$contrato,
            'cuotas'=>$cuotas,
            'ultFolio'=>$ultFolio,
            'ultAnexo'=>$ultAnexo,

            'diasPagos'=>$diasPagoRepository->findAll(),
        ]);

    }

    #[Route("/{id}/eliminar", name: "anexo_eliminar", methods: ["GET","POST"])]
    public function eliminar(ContratoAnexo $contratoAnexo,
                            ContratoAnexoRepository $contratoAnexoRepository): Response
    {
        $this->denyAccessUnlessGranted('create','anexo');

        $entityManager = $this->entityManager;


        //Eliminando un datos de anexo:::
        $contrato=$contratoAnexo->getContrato();
        if($contratoAnexo->getCausas()){
            $causas=$contratoAnexo->getCausas();

            foreach ($causas as $causa) {
                
                $entityManager->remove($causa);
                $entityManager->flush();
            }
        }

        $cuotas=$contratoAnexo->getCuotas();

        foreach ($cuotas as $cuota) {
            $entityManager->remove($cuota);
            $entityManager->flush();
        }

        $contratoAnexo->setEstado(false);
        $entityManager->persist($contratoAnexo);
        $entityManager->flush();

        //Activamos el ultimo anexo
        $ultAnexo=$contratoAnexoRepository->findOneBy(['contrato'=>$contrato,'isDesiste'=>false,'estado'=>null],['folio'=>'desc']);

        if($ultAnexo){
            $cuotas=$ultAnexo->getCuotas();

            foreach ($cuotas as $cuota) {

                $cuota->setAnular(null);
                $entityManager->persist($cuota);
                $entityManager->flush();
            }
        }else{
            $cuotas=$contrato->getDetalleCuotas();

            foreach ($cuotas as $cuota) {
                $cuota->setAnular(null);
                $entityManager->persist($cuota);
                $entityManager->flush();
            }
        }



        return $this->redirectToRoute('anexo_index',['id'=>$contratoAnexo->getContrato()->getId()]);
       
    }
    #[Route("/{id}/causas_anteriores", name: "anexo_causas_anteriores", methods: ["GET","POST"])]
    function listarCausasAnteriores(Contrato $contrato, CausaRepository $causaRepository): Response
    {

        return $this->render('anexo/_causasAnteriores.html.twig', [
            'causas_anteriores'=>$causaRepository->findBy(['agenda'=>$contrato->getAgenda(),'estado'=>true])
        ]);
    }

    #[Route("/{id}/causas_anteriores_eliminar", name: "anexo_causas_anteriores_eliminar", methods: ["GET","POST"])]
    function eliminarCausasAnteriores(Causa $causa, CausaRepository $causaRepository): Response
    {
        $entityManager = $this->entityManager;

        $causa->setEstado(false);

        $entityManager->persist($causa);
        $entityManager->flush();


        return $this->render('anexo/_causasAnteriores.html.twig', [
            'causas_anteriores'=>$causaRepository->findBy(['agenda'=>$causa->getAgenda(),'estado'=>true])
        ]);
    }


    #[Route("/{id}/pdf", name: "anexo_pdf", methods: ["GET","POST"])]
    public function pdf(ContratoAnexo $contratoAnexo)
    {
        $this->denyAccessUnlessGranted('view','contrato');
        $filename = sprintf('Anexo-'.$contratoAnexo->getId().'-%s.pdf',rand(0,9000));
       
        switch($contratoAnexo->getTipoAnexo()){
            case 1:
                $tipoAnexo="Agregar causa";
                break;
            case 2: 
                $tipoAnexo="Extensión del plazo";
                break;
            case 3:
                $tipoAnexo="Renegociación";
                break;
            default:
                $tipoAnexo="";
                break;
        }
        $html = $this->renderView('anexo/print.html.twig', array(
            'anexo' => $contratoAnexo,
            'Titulo'=>"Contrato",
            "tipoAnexo"=>$tipoAnexo,
            
        ));
    
        $entityManager = $this->entityManager;
        $contratoAnexo->setPdf($filename);
        $entityManager->persist($contratoAnexo);
        $entityManager->flush();

       


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

        return $this->redirectToRoute('anexo_index',['id'=>$contratoAnexo->getContrato()->getId()]);
    }


    public function calularCuotas(ContratoAnexo $contratoAnexo){

        $entityManager = $this->entityManager;

        //carga de Cuotas
        $countCuotas=$contratoAnexo->getNCuotas();
        $contrato=$contratoAnexo->getContrato();

        $fechaPrimerPago=$contratoAnexo->getFechaPrimerPago();
        $contrato->setFechaPrimerPago($contratoAnexo->getFechaPrimerPago());
        $contrato->setDiaPago($contratoAnexo->getDiasPago());
        $contrato->setCuotas($contratoAnexo->getNCuotas());


        $diaPago=$contratoAnexo->getDiasPago();
        $sumames=0;
        $numeroCuota=1;
        $isAbono=$contratoAnexo->getIsAbono();
        $isTotal=$contratoAnexo->getIsTotal();
        

        //Anulando cuotas anteriores
        $detalleCuotas=$contrato->getDetalleCuotas();
        foreach($detalleCuotas as $detalleCuota){
            $detalleCuota->setAnular(1);
            $entityManager->persist($detalleCuota);
            $entityManager->flush();
        }



        if($isAbono==true || $isTotal==true){
            $cuota=new Cuota();
            $cuota->setContrato($contrato);
            $cuota->setAnexo($contratoAnexo);
            $cuota->setNumero($numeroCuota);
            $cuota->setFechaPago($contratoAnexo->getFechaCreacion());
            $cuota->setMonto($contratoAnexo->getAbono());
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
        }


        if($contratoAnexo->getValorCuota()!=0){
            for($i=0;$i<$countCuotas;$i++){
                $cuota=new Cuota();
        
                $i_aux=$i;
            
                $cuota->setContrato($contrato);
                $cuota->setAnexo($contratoAnexo);
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
                $cuota->setMonto($contratoAnexo->getValorCuota());

                $entityManager->persist($cuota);
                $entityManager->flush();
                $numeroCuota++;
            }
        }


    }
}
