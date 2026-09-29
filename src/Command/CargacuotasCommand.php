<?php

namespace App\Command;

use App\Entity\Usuario;
use App\Entity\Agenda;
use App\Entity\Contrato;
use App\Entity\Cuenta;
use App\Entity\Cuota;
use App\Entity\UsuarioUsuariocategoria;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;
use Symfony\Component\DependencyInjection\ContainerInterface;

ini_set('memory_limit', '-1');

class CargacuotasCommand extends Command
{
    protected static $defaultName = 'app:carga-cuotas';
    private $container;

    public function __construct(ContainerInterface $container){
        $this->container=$container;   
        parent::__construct();
    }
    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
        
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $entityManager = $this->container->get('doctrine')->getManager();
        $em=$this->container->get('doctrine');


        $contratos=$em->getRepository(Contrato::class)->findAll();


        
        foreach($contratos as $contrato){
            $cuota=$em->getRepository(Cuota::class)->findOneByUltimaPagada($contrato->getId());
           
            if(null == $cuota){
                
                
               

             

                $countCuotas=$contrato->getCuotas();
                $fechaPrimerPago=$contrato->getFechaPrimerPago();
                if($fechaPrimerPago){
                    
                    $diaPago=$contrato->getDiaPago();
                    $sumames=0;
                    $numeroCuota=1;
                    $isAbono=$contrato->getIsAbono();
                    if($isAbono){
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
                    //fechaActual debe ser fecha_creacion:::
                    $timeFechaActual=strtotime($contrato->getFechaCreacion()->format('Y-m-d'));
                
                
                    if($timeFechaActual>=$timePrimrePago){

                        $sumames=1;
                    }
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
        }


    }




}







