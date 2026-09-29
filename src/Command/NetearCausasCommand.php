<?php

namespace App\Command;

use App\Entity\Contrato;
use App\Entity\Importacion;
use App\Entity\Pago;
use App\Repository\ImportacionRepository;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class NetearCausasCommand extends Command
{
    protected static $defaultName = 'app:netear-causas';
    protected static $defaultDescription = 'Add a short description for your command';

    private $container;
    public function __construct(ContainerInterface $container){
        $this->container=$container;   
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
        ->setDescription('Cargar causas para ser neteadas con contratos')
        ->addArgument('importacion',  InputArgument::REQUIRED,  'Id de importacion')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $entityManager = $this->container->get('doctrine')->getManager();
        $em=$this->container->get('doctrine');

        
        if ($input->getArgument('importacion')) {

            $id=$input->getArgument('importacion');
            $importacion = $em->getRepository(Importacion::class)->find($id);



            $lineas = count(file($importacion->getUrl())) - 1;

            $fp = fopen($importacion->getUrl(), "r");


            $i=0;
            $paso=true;
            $mensajeError="";
            $resultado="";
            
            $spreadSheet=new Spreadsheet();


            /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
            $sheet = $spreadSheet->getActiveSheet();
            $sheet->setCellValue('A1', 'N°');
            $sheet->setCellValue('B1', 'Codigo');
            $sheet->setCellValue('C1', 'Cliente');
            $sheet->setCellValue('D1', 'Folio Interno');
    
            while (!feof($fp)){
                $linea = fgets($fp);
                $datos=explode(";",$linea);
                if ($i==0){
                    $i++;
                    continue;
                }
                $i++;

                
                
                $sheet = $spreadSheet->getActiveSheet();


                try{
                    $anular=false;
                    $fechaVencimiento="";

                    $contrato=$em->getRepository(Contrato::class)->findOneBy(['folio' => $datos[3]]);
           
                    if($contrato != null){
                        
                        if($contrato->getIsFinalizado()){
                            $anular=true;
                        }else{

                            $ultimoPago=$em->getRepository(Pago::class)->findUPByContrato($contrato);

                            if($ultimoPago!=null){

                                //encontramos el ultimo pago..

                                $cuotaPagos=$ultimoPago->getPagoCuotas();

                                foreach ($cuotaPagos as $cuotaPago) {
                                    $fechaVencimiento=$cuotaPago->getCuota()->getFechaPago();
                                    $io->note(sprintf('fecha Vencimiento %s',$fechaVencimiento->format('Y-m-d')));
                                    $dias = strtotime(date('Y-m-d'))-strtotime($fechaVencimiento->format('Y-m-d'));
                                    $io->note(sprintf('dias %s',$dias));
                                    if($dias>=61){
                                        $anular=true;
                                    }
                                }
                                

                                
                            }else{
                                //No existen pagos, por lo que debemos buscar la primera cuota.

                                $cuota=$em->getRepository(Cuota::class)->findOneByPrimeraVigente($contrato);
                                
                                if($cuota != null){
                                    $fechaVencimiento=$cuota->getFechaPago();
                                    $io->note(sprintf('fecha Vencimiento %s',$fechaVencimiento->format('Y-m-d')));
                                    $dias = strtotime(date('Y-m-d'))-strtotime($fechaVencimiento->format('Y-m-d'));
                                    $io->note(sprintf('dias %s',$dias));
                                    if($dias>=61){
                                        $anular=true;
                                    }
                                }
                                
                            }

                        }



                    }else{
                        $anular=true;
                    }

                    if($anular){
                        $sheet->setCellValue("A$i",$datos[0]);
                        $sheet->setCellValue("B$i", $datos[1]);
                        $sheet->setCellValue("C$i", $datos[2]);
                        $sheet->setCellValue("D$i", $datos[3]);
                        $io->note(sprintf('Linea %s contrato %s',$i,$datos[3]));
                        
                        $porcentaje=$i/$lineas*100;
                        
                        $importacion->setEstado($porcentaje);
                        $entityManager->persist($importacion);
                        $entityManager->flush();
                    }
                 
                }catch(Exception $e){
                    $io->note(sprintf($e->getMessage()));
                }
              


            }

            
            $sheet->setTitle("Clientes Morosos");
 
            // Create your Office 2007 Excel (XLSX Format)
            $writer = new Csv($spreadSheet);
            
            $writer->setUseBOM(true);
            $writer->setDelimiter(';');
            $writer->setEnclosure('');
            $writer->setLineEnding("\r\n");
            $writer->setSheetIndex(0);
            // Create a Temporary file in the system
            $fileName = 'Neteo_contrato'.date('dmy_Hs').'.csv';
            
            //$temp_file = tempnam($this->container->getParameter('csv_importacion'), $fileName);
            $temp_file = $this->container->getParameter('csv_importacion').$fileName;
            // Create the excel file in the tmp directory of the system
            $writer->save($temp_file);

            $importacion->setUrl($this->container->getParameter('url_web')."/build/csv/".$fileName);
            $entityManager->persist($importacion);
            $entityManager->flush();
            $io->note(sprintf('guardado de archivo'));
            // Return the excel file as an attachment
            //return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
            
        }


        $io->success('Comando terminado');
        return 0;
    }
}
