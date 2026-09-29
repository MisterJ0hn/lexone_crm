<?php

namespace App\Command;

use App\Entity\Contrato;
use App\Entity\CuentaCorriente;
use App\Entity\Cuota;
use App\Entity\Pago;
use App\Entity\PagoCanal;
use App\Entity\PagoCuotas;
use App\Entity\PagoTipo;
use App\Entity\Usuario;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;


ini_set('memory_limit', '-1');

class CargarPagosCommand extends Command
{
    protected static $defaultName = 'app:cargar-pagos';
    private $container;
    public function __construct(ContainerInterface $container){
        $this->container=$container;   
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Carga pagos asociando a las cuotas')
            ->addArgument('url', InputArgument::REQUIRED, 'Fichero que contiene los pagos')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $entityManager = $this->container->get('doctrine')->getManager();
        $em=$this->container->get('doctrine');


        if ($input->getArgument('url')) {
            $fp = fopen($input->getArgument('url'), "r");
            $i=0;
            $paso=true;
            $mensajeError="";
            
    
    
            while (!feof($fp)){
                $linea = fgets($fp);
                $datos=explode(";",$linea);
                if ($i==0){
                    $i++;
                    continue;
                }
                $i++;
                
                if($datos[0]=="") break;

                $contrato=$em->getRepository(Contrato::class)->findOneBy(['folio'=>$datos[0]]);
                if(null != $contrato){
                    $fechaPago=str_replace(" : ","00:00", $datos[6]);
                    $fechaRegistro=str_replace(" : ","00:00", $datos[8]);
                    $pago=new Pago();
                    $pago->setPagoTipo($em->getRepository(PagoTipo::class)->find($datos[1]));
                    $pago->setPagoCanal($em->getRepository(PagoCanal::class)->find($datos[2]));
                    $pago->setMonto($datos[3]);
                    $pago->setBoleta($datos[4]);
                    $pago->setObservacion($datos[5]);
                    $pago->setFechaPago(new \DateTime(date('Y-m-d H:i',strtotime($fechaPago))));
                    $pago->setHoraPago(new \DateTime(date('H:i',strtotime($fechaPago))));
                    $pago->setFechaRegistro(new \DateTime(date('Y-m-d H:i',strtotime($fechaRegistro))));
                    $pago->setCuentaCorriente($em->getRepository(CuentaCorriente::class)->find($datos[9]));
                    $pago->setNcomprobante($datos[10]);
                    $pago->setComprobante($datos[11]);
                    $pago->setUsuarioRegistro($em->getRepository(Usuario::class)->find($datos[12]));
                    $entityManager->persist($pago);
                    $entityManager->flush();

                    $em->getRepository(PagoCuotas::class)->asociarPagos($em->getRepository(Contrato::class)->findOneBy(['folio'=>$datos[0]]),$em->getRepository(Cuota::class),$em->getRepository(PagoCuotas::class),$pago);
                }
            }
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return 0;
    }
}
