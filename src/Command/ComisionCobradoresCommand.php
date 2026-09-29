<?php

namespace App\Command;

use App\Entity\Cobranza;
use App\Entity\Configuracion;
use App\Entity\InfComisionCobradores;
use App\Entity\PagoCuotas;
use App\Entity\Usuario;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;

ini_set('memory_limit', '-1');

#[AsCommand(name: 'app:comision-cobradores', description: 'Add a short description for your command')]
class ComisionCobradoresCommand extends Command
{
    protected static $defaultName = 'app:comision-cobradores';
    protected static $defaultDescription = 'Add a short description for your command';

    private $container;
    public function __construct(ContainerInterface $container){
        $this->container=$container;   
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Genera informe de cobradores')
            ->addArgument('sesion', InputArgument::OPTIONAL, 'id usuario')
            ->addOption('usuario', null, InputOption::VALUE_OPTIONAL, 'id usuario si quiere filtrar por este campo')
            ->addOption('fechaInicio', null, InputOption::VALUE_OPTIONAL, 'Fecha Inicio')
            ->addOption('fechaFin', null, InputOption::VALUE_OPTIONAL, 'Fecha Fin')
            ->addOption('folio', null, InputOption::VALUE_OPTIONAL, 'Folio')
            ->addOption('compania', null, InputOption::VALUE_OPTIONAL, 'Compañia ')
            ->addOption('otros', null, InputOption::VALUE_OPTIONAL, 'Query en general')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $entityManager = $this->container->get('doctrine')->getManager();
        $em=$this->container->get('doctrine');

        $user=$em->getRepository(Usuario::class)->find($input->getArgument('sesion'));
        
        $folio=null;
        $compania=null;
        $statuesgroup='7,14';
        $status=null;
        $usuario=null;

        if($input->getOption('usuario') && $input->getOption('usuario') != ''){
            $usuario=$input->getOption('usuario');
        }
        if($input->getOption('fechaInicio') && $input->getOption('fechaInicio') != ''){
            $fechaInicio=$input->getOption('fechaInicio');
        }

        if($input->getOption('fechaFin') && $input->getOption('fechaFin') != ''){
            $fechaFin=$input->getOption('fechaFin');
        }

        if($input->getOption('folio') && $input->getOption('folio') != ''){
            $folio=$input->getOption('folio');
            $fecha="co.folio = ".$folio;
            $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*1);
            $dateFin=date('Y-m-d');
        }else{
            if($input->getOption('compania') && $input->getOption('compania')!=0){
                $compania=$input->getOption('compania');
            }
            if($fechaInicio!='' && $fechaFin!=''){
                
                $dateInicio=$fechaInicio;
                $dateFin=$fechaFin;
            }else{
                $dateInicio=date('Y-m-d',mktime(0,0,0,date('m'),date('d'),date('Y'))-60*60*24*30);
                $dateFin=date('Y-m-d');
            }
            $fecha="pa.fechaPago between '$dateInicio' and '$dateFin 23:59:59' and datediff(pa.fechaPago,c.fechaPago)>=0" ;

            
        }
        //$fecha.=" and u.usuarioTipo=12";
        switch($user->getUsuarioTipo()->getId()){
            case 1:// Admin
                //$cobranzas=$cobranzaRepository->findByUltimaGestion(null,$user->getEmpresaActual(),$compania,null,$fecha);
                $pagoCuotas=$em->getRepository(PagoCuotas::class)->findByPers(null,$user->getEmpresaActual(),$compania,null,$fecha);
                
                break;

            case 12:// Cobradores
                //$query=$agendaRepository->findByPers(null,$user->getEmpresaActual(),$compania,$statuesgroup,null,null,$fecha);
                //$query=$cobranzaRepository->findByUltimaGestion($user->getId(),$user->getEmpresaActual(),$compania,null,$fecha);
                $pagoCuotas=$em->getRepository(PagoCuotas::class)->findByPers(null,$user->getEmpresaActual(),$compania,null,$fecha);
               
                break;
            default:
                //$cobranzas=$cobranzaRepository->findByPers(null,$user->getEmpresaActual(),$compania,null,$fecha);
                $pagoCuotas=$em->getRepository(PagoCuotas::class)->findByPers(null,$user->getEmpresaActual(),$compania,null,$fecha);
            
                break;

        }
        
        $queryresumen="";

        
        $em->getRepository(InfComisionCobradores::class)->removeBySesion($user->getId());
        $configuracion=$em->getRepository(Configuracion::class)->find(1);
        $pago_aux=0;
        foreach ($pagoCuotas as $pagoCuota) {
            if($pago_aux!=$pagoCuota->getPago()->getId()){
                $pago_aux=$pagoCuota->getPago()->getId();

                $pago=$pagoCuota->getPago();
                $cuota=$pagoCuota->getCuota();
                $fecha=" u.usuarioTipo=12 and (timestampdiff(second,c.fechaHora,'".$pago->getFechaPago()->format('Y-m-d H:i')."')/60/60/24)>=0 and datediff('".$pago->getFechaPago()->format('Y-m-d H:i')."',c.fechaHora)<=".$configuracion->getMaxDiasComision()." and co.id=".$cuota->getContrato()->getId();
                $cobranza=$em->getRepository(Cobranza::class)->findByUltimaGestionObj($usuario,$user->getEmpresaActual(),$compania,null,$fecha);


                if($cobranza!=null){
                    $timecobranza=strtotime($cobranza->getFechaHora()->format("Y-m-d h:i"));
                    $timepago=strtotime($pago->getFechaPago()->format("Y-m-d h:i"));
                    $timegestion=$timepago-$timecobranza;
                    
                    $moracuota=strtotime($cuota->getFechaPago()->format("Y-m-d h:i"));
                    $morapago=strtotime($pago->getFechaPago()->format("Y-m-d h:i"));
                    $timemora=$morapago-$moracuota;
                    if(intval(round($timemora/60/60/24))>=0){
                        $informe=new InfComisionCobradores();
                        $informe->setSesion($user->getId());
                        $informe->setCobranza($cobranza);
                        $informe->setContrato($cobranza->getContrato());
                        $informe->setPago($pago);
                        $informe->setCuota($cuota);
                        $informe->setMonto($pago->getMonto());
                        $informe->setDiasMora(intval(round($timemora/60/60/24)));
                        $informe->setTiempoGestion(intval(round($timegestion/60/60/24)));

                        $entityManager->persist($informe);
                        $entityManager->flush();
                    }
                }
            }

        }

        return 0;
    }
}
