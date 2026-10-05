<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Agenda;
use App\Entity\Causa;
use App\Repository\CausaRepository;
use App\Repository\ClienteRepository;
use App\Repository\CuentaRepository;
use App\Repository\JuzgadoCuentaRepository;
use App\Repository\JuzgadoRepository;
use App\Repository\ServicioRepository;
use App\Repository\MateriaRepository;
use Doctrine\ORM\EntityManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route("/causa")]
class CausaController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }
    #[Route("/", name: "causa_index")]
    public function index(): Response
    {
        return $this->render('causa/index.html.twig', [
            'controller_name' => 'CausaController',
        ]);
    }

    #[Route("/{id}/new", name: "causa_new", methods: ["GET"])]
    public function agregar(Agenda $agenda,
                            Request $request,
                            MateriaRepository $materiaRepository,
                            ServicioRepository $servicioRepository,
                            
                            JuzgadoRepository $juzgadoRepository,
                            \App\Repository\CorteRepository $corteRepository,
                            ClienteRepository $clienteRepository){
        $entityManager = $this->entityManager;
        $causa=new Causa();
        $causa->setEstado(1);
        $causa->setAgenda($agenda);

        // Agendas de tipo Convenio/Empresa: la causa se crea asociada a uno de los
        // clientes propios del contrato (ver ContratoController::nuevoClienteConvenio).
        // Para el flujo Persona no viene este parámetro y no cambia nada.
        if (null !== $request->query->get('cliente')) {
            $causa->setCliente($clienteRepository->find($request->query->get('cliente')));
        }
        // Letra/Rol/Año (línea de tiempo, contrata): reemplaza al viejo "Id Causa"
        // de un solo campo. Se conserva txtNombreCausa por compatibilidad con las
        // vistas que todavía no se migraron a Letra/Rol/Año.
        if ($request->query->get('txtLetra') || $request->query->get('txtRol') || $request->query->get('txtAnio')) {
            if ($request->query->get('txtLetra')) {
                $causa->setLetra($request->query->get('txtLetra'));
            }
            if ($request->query->get('txtRol')) {
                $causa->setRol($request->query->get('txtRol'));
            }
            if ($request->query->get('txtAnio')) {
                $causa->setAnio($request->query->get('txtAnio'));
            }
        } elseif (null !== $request->query->get('txtNombreCausa')) {
            $causa->setIdCausa($request->query->get('txtNombreCausa'));
        }
        if(null !== $request->query->get('txtCaratulado')){
            $causa->setCausaNombre($request->query->get('txtCaratulado'));
        }

        $servicio=null;
        if($request->query->get('cboSubMateria')){
            $servicio=$servicioRepository->find($request->query->get('cboSubMateria'));
        }

        // Materia obligatoria: se toma de cboMateria; si no viene, de la
        // servicio elegido.
        $materia=null;
        if($request->query->get('cboMateria')){
            $materia=$materiaRepository->find($request->query->get('cboMateria'));
        }
        if($materia===null && $servicio!==null){
            $materia=$servicio->getMateria();
        }
        if($materia===null){
            return new Response('Falta la materia de la causa',400);
        }
        $causa->setMateria($materia);

        if($servicio!==null && $servicio->getMateria()->getId()===$materia->getId()){
            $causa->setServicio($servicio);
        }

        // La corte elegida en el formulario manda; si no viene, se toma la del juzgado (como en contrata).
        if($request->query->get('corte')){
            $causa->setCorte($corteRepository->find($request->query->get('corte')));
        }
        if(null !== $request->query->get('juzgado') && $request->query->get('juzgado') !== ''){
            $juzgado=$juzgadoRepository->find($request->query->get('juzgado'));
            $causa->setJuzgado($juzgado);
            if($juzgado && $causa->getCorte()===null && $juzgado->getCorte()!==null){
                $causa->setCorte($juzgado->getCorte());
            }
        }

        $entityManager->persist($causa);
        $entityManager->flush();

        return $this->render('causa/index.html.twig');

    }
    #[Route("/{id}/list", name: "causa_list", methods: ["GET"])]
    public function list(Agenda $agenda,
                            Request $request, 
                            CausaRepository $causaRepository,
                            ServicioRepository $servicioRepository,
                            JuzgadoCuentaRepository $juzgadoCuentaRepository){
        
        $causas=$causaRepository->findBy(['agenda'=>$agenda->getId(),'estado'=>true]);

        return $this->render('causa/list.html.twig', [
            'causas' => $causas,
            
        ]);

    }

    #[Route("/{id}/delete", name: "causa_delete", methods: ["GET"])]
    public function delete(Causa $causa,
                            Request $request, 
                            CausaRepository $causaRepository,
                            ServicioRepository $servicioRepository,
                            JuzgadoCuentaRepository $juzgadoCuentaRepository){
        $entityManager = $this->entityManager;

        $causa->setEstado(false);

        $entityManager->persist($causa);
        $entityManager->flush();
        
       
        return $this->render('causa/index.html.twig');

    }
}
