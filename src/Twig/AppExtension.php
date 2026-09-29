<?php

namespace App\Twig;

use App\Entity\Contrato;
use App\Entity\ContratoAnexo;
use App\Entity\Vencimiento;
use App\Entity\Pago;
use App\Entity\Cuota;
use App\Entity\PagoCuotas;
use App\Entity\Usuario;
use App\Entity\VwCausasActivasFinal;
use DateTime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppExtension extends AbstractExtension
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('special_chars', [$this, 'decode_utf8']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('special_chars_func', [$this, 'decode_utf8']),
            new TwigFunction('suma_mes',[$this,'suma_mes']),
            new TwigFunction('semaforo',[$this,'semaforo']),
            new TwigFunction('semaforoContrato',[$this,'semaforoContrato']),
            new TwigFunction('ultimoPago',[$this,'ultimoPago']),
            new TwigFunction('proximoPago',[$this,'proximoPago']),
            new TwigFunction('montoDeuda',[$this,'montoDeuda']),
            new TwigFunction('diasmora',[$this,'diasmora']),
            new TwigFunction('totalesCobranza',[$this,'totalesCobranza']),
            new TwigFunction('ultimaGestion',[$this,'ultimaGestion']),
            new TwigFunction('avisoCumpleanios',[$this,'avisoCumpleanios']),
            new TwigFunction('isAnexo',[$this,'isAnexo']),
            new TwigFunction('getVip',[$this,'getVip']),
            new TwigFunction('fechaPagoPorCuota',[$this,'fechaPagoPorCuota']),
        ];
    }

    public function decode_utf8($value)
    {
       
        return  htmlentities($value);
    }
    public function suma_mes($dia,$mes,$anio,$suma){


        $ts = mktime(0, 0, 0, $mes + $suma, 1,$anio);

        if(date("n",$ts)==2){
            if($dia==30){
                $dia=date("d",mktime(0,0,0,$mes+$suma+1,1,$anio)-24);
            }
        }
        
        return date("d-m-Y", mktime(0,0,0,$mes+$suma,$dia,$anio));
    }

    /**
     * @fecha: Fecha de la cuota proxima a vencer
     * @difPago: es el (valor cuota - pagado)
     */
    public function semaforo($fecha, $difPago=1){

        $color="";
        $icono="";
        $inicio=strtotime($fecha);
        $fin=strtotime(date("Y-m-d"));
        $dif=($fin-$inicio);

        if($dif<0){
            $dif=0;
        }else{
            $dif = round($dif/60/60/24);
        }
        
        if($difPago<=0){
            $dif=0;

        }
        $em=$this->container->get('doctrine');
        $vencimientos=$em->getRepository(Vencimiento::class)->findAll();

        
        foreach($vencimientos as $vencimiento){
            $statusMin=false;
            $statusMax=false;

            if(null != $vencimiento->getValMin()){
                if($vencimiento->getValMin() <= $dif){
                    $statusMin=true;
                }
            }else{
                $statusMin=true;
            }
            if(null != $vencimiento->getValMax()){
                if($vencimiento->getValMax()>=$dif){
                    $statusMax=true;
                }
            }else{
                $statusMax=true;
            }
            if($statusMax && $statusMin){
                $color = $vencimiento->getColor();
                $icono= $vencimiento->getIcono();
            }
        }
    
        
        return "<p class='text-$color' ><i class='$icono' ></i></p>";
    }

    /**
     * @fecha: Fecha de la cuota proxima a vencer
     * @difPago: es el (valor cuota - pagado)
     */
    public function semaforoContrato($fecha, $difPago=1){

        $color="text-success";
        $icono="fas fa-circle";
        $inicio=strtotime($fecha);
        $fin=strtotime(date("Y-m-d"));
        $dif=($fin-$inicio);

        if($dif<0){
            $dif=0;
        }else{
            $dif = round($dif/60/60/24);
        }
        
        if($difPago<=0){
            $dif=0;

        }
        $em=$this->container->get('doctrine');
        $vencimientos=$em->getRepository(Vencimiento::class)->findAll();

        
        foreach($vencimientos as $vencimiento){
            $statusMin=false;
            $statusMax=false;

            if(null != $vencimiento->getValMin()){
                if($vencimiento->getValMin() <= $dif){
                    $statusMin=true;
                }
            }else{
                $statusMin=true;
            }
            if(null != $vencimiento->getValMax()){
                if($vencimiento->getValMax()>=$dif){
                    $statusMax=true;
                }
            }else{
                $statusMax=true;
            }
            if($statusMax && $statusMin){
                $color = $vencimiento->getColor();
                $icono= $vencimiento->getIcono();
            }
        }
    
        
        return "<p class='text-$color' ><i class='$icono' ></i></p>";
    }
    public function ultimoPago($contrato){
        $em=$this->container->get('doctrine');
        $pago=$em->getRepository(Pago::class)->findUPByContrato($contrato);
        $ultimoPago=false;
        if($pago){
            $ultimoPago=$pago->getFechaPago()->format('Y-m-d')." ".$pago->getHoraPago()->format('H:i');
        }
        return $ultimoPago;
    }
    public function proximoPago($contrato){
        $em=$this->container->get('doctrine');
        $fechapago=$em->getRepository(Cuota::class)->findProximaFechaPago($contrato);
        
        return $fechapago ? $fechapago['fechaPago'] : date('Y-m-d H:i:s') ;
    }
    public function montoDeuda($contratoId){
        $em=$this->container->get('doctrine');
       
        $contrato=$em->getRepository(Contrato::class)->find($contratoId);

        $vencimiento=$em->getRepository(Vencimiento::class)->findOneMaxNotNull($contrato->getAgenda()->getCuenta()->getEmpresa()->getId(),'v.valMax','ASC');

        $otros=' c.fechaPago<=now() and DATEDIFF(now(),co.proximoVencimiento)>'.$vencimiento->getValMax();
        //$otros="";
        $cuota=$em->getRepository(Cuota::class)->deudaTotal($contrato,$otros);
       
        if(count($cuota)>0){
            return $cuota[0][1]-$cuota[0][2];
        }else{
            return 0;
        }
        
        
    }
    /**
     * @fecha: Fecha de la cuota proxima a vencer
     */
    public function diasmora($fecha){
        $inicio=strtotime($fecha);
        $fin=strtotime(date("Y-m-d"));
        
        
        $dif=($fin-$inicio)/60/60/24;


        return  round($dif);
    }
    public function ultimaGestion($fecha){
        $inicio=strtotime($fecha);
        $fin=strtotime(date("Y-m-d"));
        $dif=($fin-$inicio);


        return  round($dif/60/60/24);
    }
     public function totalesCobranza($idContrato){
        $em=$this->container->get('doctrine');
        $ultimaCuota=$em->getRepository(Cuota::class)->findCuotasTotales($idContrato);
        $ultimaCuotaPagada=$em->getRepository(Cuota::class)->findUltimaPagada($idContrato);
        $pagado=0;
        if($ultimaCuotaPagada){
            $pagado=$ultimaCuotaPagada->getNumero();
        }
        return $pagado."/".$ultimaCuota->getNumero();
     }
    
     public function isAnexo($contratoId){
        $em=$this->container->get('doctrine');
        $entityManager=$em->getManager();
        $anexo=$em->getRepository(ContratoAnexo::class)->findOneBy(['isDesiste'=>0,'contrato'=>$contratoId],['folio'=>'desc']);
     
        if($anexo){
            return "A".$anexo->getFolio();
        }
        return "C0";
    }
    
    public function avisoCumpleanios($idUsuario){
        $em=$this->container->get('doctrine');
        $entityManager=$em->getManager();
        $usuario=$em->getRepository(Usuario::class)->find($idUsuario);
        $hoy=date("now");
        $estado=false;
        $html="";
        if($usuario->getFechaAviso() != null){
            if($usuario->getFechaAviso()->format("Y-m-d")!=date('Y-m-d')){
                
              
                $usuarios=$em->getRepository(Usuario::class)->findBy(["usuarioTipo"=>[1,2,3,4,5,6,7,8,10,11,12,13],"estado"=>1]);
                $html='<ul>';
                foreach ($usuarios as $user) {
                    if ($user->getDiaCumpleanio()==date("d") && $user->getMesCumpleanio()==date("m")) {
                       
                        $html.="<li>".$user->getNombre()."</li>";
                        $estado=true;
                        # code...
                    }
                }
                $html.='</ul>';

                $usuario->setFechaAviso(new DateTime(date('Y-m-d')));
                $entityManager->persist($usuario);
                $entityManager->flush();
            }
            
        }else{

            $usuarios=$em->getRepository(Usuario::class)->findBy(["usuarioTipo"=>[1,2,3,4,5,6,7,8,10,11,12,13],"estado"=>1]);
            $html='<ul>';
            foreach ($usuarios as $user) {
                if ($user->getDiaCumpleanio()==date("d") && $user->getMesCumpleanio()==date("m")) {
                       
                    $html.="<li>".$user->getNombre()."</li>";
                    $estado=true;
                    # code...
                }
            }
            $html.='</ul>';

            $usuario->setFechaAviso(new DateTime(date('Y-m-d')));
            $entityManager->persist($usuario);
            $entityManager->flush();
           
        }

        if($estado){

            return $html;
        }
        return false;
    }

    public function getVip($esVip){
        
        if($esVip==1){
            return "<i class='fas fa-star text-warning' title='Cliente VIP'></i>";
        }
        return "<i class='fas fa-star text-secondary' title='Cliente VIP'></i>";
    }

    public function fechaPagoPorCuota($cuotaId){
        $em=$this->container->get('doctrine');
        $cuota=$em->getRepository(Cuota::class)->find($cuotaId);
        if($cuota){
            $pagoCuotas = $em->getRepository(PagoCuotas::class)->findOneBy(['cuota'=>$cuotaId]);

            if($pagoCuotas){
                $pago = $pagoCuotas->getPago();
                return $pago->getFechaPago()->format('d-m-Y');
            }
           
        }
        return "";
    }
}
