<?php

namespace App\Command;

use App\Entity\Pago;
use App\Entity\PagoCuotas;
use App\Entity\Usuario;
use App\Service\Toku;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface as DependencyInjectionContainerInterface;


class ActualizaPagoTokuCommand extends Command
{
    protected static $defaultName = 'ActualizaPagoToku';
    protected static $defaultDescription = 'Add a short description for your command';

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    private $container;

    public function __construct(DependencyInjectionContainerInterface $container){
        $this->container=$container;   
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }


        error_log("\n INidio : ".date("Y-m-d H:i"),3,"/home/micrm.cl/test/TokuWebhook_log");
        $toku=new Toku();
        error_log("\n Toku : ",3,"/home/micrm.cl/test/TokuWebhook_log");
        $entityManager = $this->container->get('doctrine')->getManager();
        $em=$this->container->get('doctrine');
        error_log("\n Doctrine : ",3,"/home/micrm.cl/test/TokuWebhook_log");
        //$agendas=$em->getRepository(Agenda::class)->findBy(['status'=>[7,11,12,13,14,15]],['id'=>'Asc']);
        //$agendas=$em->getRepository(Agenda::class)->findByPers(null,null,null,'7,13,14',null,null,'a.id=99973');
        $pagoCuotas=$em->getRepository(PagoCuotas::class)->findPagos();
        error_log("\n agenda repository : ",3,"/home/micrm.cl/test/TokuWebhook_log");
        $i=0;
        foreach ($pagoCuotas as $pagoCuota) {
            $contrato=$pagoCuota->getCuota()->getContrato();
            $cuota=$pagoCuota->getCuota();
            $pago=$pagoCuota->getPago();
            $io->note(sprintf('Contrato N°: %s', $contrato->getFolio()));

            if($cuota->getIsMulta()!=null){
                $productId=$contrato->getFolio()."_M";
                $io->note(sprintf('product id : %s',$productId));
            }else{
                $productId=$contrato->getFolio();
            }
            
            $tokuId=$contrato->getCliente()->getTokuId();

            if($contrato->getCliente()->getTokuId()!= null){
                /*$resultToku=$toku->crearCustomer(true,
                                                    $contrato->getEmail(),
                                                    $contrato->getRut(),
                                                    $contrato->getNombre(),
                                                    $contrato->getTelefono(),
                                                    $contrato->getFolio());
                $cliente=json_decode($resultToku);
                if($resultToku!=false){
                    $usuario=$contrato->getCliente();
                    $usuario->setTokuId($cliente->id);

                    $entityManager->persist($usuario);
                    $entityManager->flush();
                    $tokuId=$usuario->getTokuId();
                }*/
                
          
                if($cuota->getMonto()>$cuota->getPagado()){

                    $cuotaResultToku=$toku->crearInvoice($tokuId,strval($productId."_".$pago->getBoleta()),($cuota->getPagado()),$cuota->getFechaPago()->format('Y-m-d'),true);

                    error_log("<br><br> Pago Cuota crm".$pago->getBoleta()." : ".$cuota->getNumero()." Invoice id ".$cuota->getInvoiceId()." monto ".($cuota->getPagado()),3,"/home/micrm.cl/test/TokuWebhook_log");
                                
                    
                    $cuotaResultToku=$toku->crearInvoice($tokuId,strval($productId),($cuota->getMonto()-$cuota->getPagado()),$cuota->getFechaPago()->format('Y-m-d'),false);
                    error_log("<br><br> Pago Cuota  : ".$cuota->getNumero()." Invoice id ".$cuota->getInvoiceId()." monto ".($cuota->getMonto()-$cuota->getPagado()),3,"/home/micrm.cl/test/TokuWebhook_log");
                }else{
                    $cuotaResultToku=$toku->crearInvoice($tokuId,strval($productId),($cuota->getMonto()),$cuota->getFechaPago()->format('Y-m-d'),true);
                    error_log("<br><br> Pago Cuota  : ".$cuota->getNumero()." Invoice id ".$cuota->getInvoiceId()." monto ".$pago->getMonto(),3,"/home/micrm.cl/test/TokuWebhook_log");
                    
                }
            }
            

        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return 0;
    }
}
