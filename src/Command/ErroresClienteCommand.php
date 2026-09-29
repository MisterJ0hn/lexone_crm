<?php

namespace App\Command;

use App\Entity\Agenda;
use App\Entity\Contrato;
use App\Entity\Usuario;
use App\Service\Toku;
use Exception;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface as DependencyInjectionContainerInterface;


ini_set('memory_limit', '-1');

class ErroresClienteCommand extends Command
{
    //protected static $defaultName = 'ActualizarTokuCommand';
    protected static $defaultName = 'app:errores-cliente';
    protected static $defaultDescription = 'Actualiza los ids de toku en crm';
    private $container;

    public function __construct(DependencyInjectionContainerInterface $container){
        $this->container=$container;   
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
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
        error_log("<br><br> INidio : ",3,"/home/micrm.cl/test/TokuWebhook_log");
        $toku=new Toku();
        error_log("<br><br> Toku : ",3,"/home/micrm.cl/test/TokuWebhook_log");
        $entityManager = $this->container->get('doctrine')->getManager();
        $em=$this->container->get('doctrine');
        error_log("<br><br> Doctrine : ",3,"/home/micrm.cl/test/TokuWebhook_log");
        //$agendas=$em->getRepository(Agenda::class)->findBy(['status'=>[7,11,12,13,14,15]],['id'=>'Asc']);
        $agendas=$em->getRepository(Agenda::class)->findByPersFull(null,null,null,'7,11,12,13,14,15',null,null,' a.id< 89483');
        error_log("<br><br> agenda repository : ",3,"/home/micrm.cl/test/TokuWebhook_log");
        $i=0;
        foreach ($agendas as $agenda) {

            $i++;
            error_log("<br><br> foreach : ".$i,3,"/home/micrm.cl/test/TokuWebhook_log");

            if($agenda->getContrato()!= null && $agenda->getContrato()->getCliente() != null ){ 
                $contrato=$agenda->getContrato();
                $usuario=$contrato->getCliente();
                error_log("\n Agenda: ".$agenda->getId(),3,"/home/micrm.cl/test/TokuWebhook_log");
                
                error_log("\n Usuario : ".$usuario->getNombre()." agenda: ".$agenda->getId(),3,"/home/micrm.cl/test/TokuWebhook_log");
                error_log("\n Usuario : ".$contrato->getEmail()." ".$contrato->getRut()." agenda: ".$agenda->getId(),3,"/home/micrm.cl/test/TokuWebhook_log");
               try{
                   if ($contrato->getAgenda()->getAbogado()->getTelefono() == null) {
                        $telefono= " ";
                   }else{
                       $telefono=$contrato->getAgenda()->getAbogado()->getTelefono();
                   }
                 
                $resultToku=$toku->crearCustomer(true,
                                                $contrato->getEmail(),
                                                $contrato->getRut(),
                                                $contrato->getNombre(),
                                                $contrato->getTelefono(),
                                                $contrato->getAgenda()->getAbogado()->getCorreo(),
                                                $telefono,
                                                $contrato->getFolio());
                $cliente=json_decode($resultToku);
               } catch (Exception $e){
                    error_log("\n Antes de entrar Usuario : ".$contrato->getEmail()." ".$contrato->getRut()." agenda: ".$agenda->getId(),3,"/home/micrm.cl/test/customer_error_log");
               
               }
                error_log("\n Usuario : ".print_r($resultToku,true),3,"/home/micrm.cl/test/TokuWebhook_log");
                if($resultToku!=false){
                    $usuario->setTokuId($cliente->id);

                    $entityManager->persist($usuario);
                    $entityManager->flush();

                    // foreach ($contrato->getDetalleCuotas() as $cuota) {
                    //     if($cuota->getPagado()==null){

                    //         $cuotaResultToku=$toku->crearInvoice($usuario->getTokuId(),strval($contrato->getFolio()),$cuota->getMonto(),$cuota->getFechaPago()->format('Y-m-d'));
                    //         error_log("\n<br><br> cuota : ".print_r($cuotaResultToku,true),3,"/home/micrm.cl/test/TokuWebhook_log");
                    //         if($cuotaResultToku==false){
                                
                    //         }else{
                                
                                
                    //             $cuotaToku=json_decode($cuotaResultToku);
                                
                    //             $cuota->setInvoiceId(strval($cuotaToku->id));
                    //             $entityManager->persist($cuota);
                    //             $entityManager->flush();
                    //         }
                            
                        
                        
                    //     }
                    // }
                }else{
                    error_log(" Usuario : ".$contrato->getEmail()." ".$contrato->getRut()." agenda: ".$agenda->getId(),3,"/home/micrm.cl/test/customer_error_log");
               
                }


            }

        }


        $io->success('Finalizado');

        return 0;
    }
}
