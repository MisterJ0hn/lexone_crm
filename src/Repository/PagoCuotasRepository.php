<?php

namespace App\Repository;

use App\Entity\PagoCuotas;
use App\Service\Toku;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PagoCuotas|null find($id, $lockMode = null, $lockVersion = null)
 * @method PagoCuotas|null findOneBy(array $criteria, array $orderBy = null)
 * @method PagoCuotas[]    findAll()
 * @method PagoCuotas[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PagoCuotasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PagoCuotas::class);
    }

    public function findByContrato($value)
    {
        return $this->createQueryBuilder('p')
            ->join('p.cuota','c')
            ->andWhere('c.contrato = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    public function findByPago($pago)
    {
        return $this->createQueryBuilder('p')
            ->addSelect('sum(p.monto) as total')
            ->andWhere('p.pago = :val')
            ->setParameter('val', $pago)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByPers($usuario=null,$empresa=null,$compania=null,$filtro=null,$otros=null)
    {
        $query=$this->createQueryBuilder('p')
        ->join('p.pago','pa')
        ->join('p.cuota','c')
        ->join('c.contrato','co')
        ->join('co.agenda','a')
        ->join('a.cuenta','cu')
        ->join('pa.usuarioRegistro','u');
        

        if(!is_null($usuario)){
            $query->andWhere('u.id = '.$usuario);
        }
        if(!is_null($empresa)){
            $query->andWhere('cu.empresa = '.$empresa);
        }
        
        if(!is_null($filtro)){ 
            $query->andWhere("(co.nombre like '%$filtro%' or co.rut like '%$filtro%')");

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        
        if(!is_null($otros)){ 
            $query->andWhere($otros);
        }
        $query->orderBy('co.id desc, pa.fechaPago', 'Desc')
        ;
     
        return $query->getQuery()
        ->getResult()
        ;

    }

    public function asociarPagos($contrato,$cuotaRepository,$pagoCuotasRepository,$pago,$esMulta=false){

        $entityManager = $this->_em;
        $contrato->setIsFinalizado(false);
        $toku = new Toku();

        if($esMulta){
            $productId=$contrato->getFolio()."_M";
        }else{
            $productId=$contrato->getFolio();
        }
        do{
            $pagostatus=false;
            $cuota=$cuotaRepository->findOneByPrimeraVigente($contrato->getId(),$esMulta);
            $pagoCuotas=$pagoCuotasRepository->findByPago($pago->getId());

            if(null == $pagoCuotas["total"]){
                $total=0;
            }else{
                $total=$pagoCuotas["total"];
            }
            if($cuota){
                //cuando el pago es menor o igual a la deuda.
                if(($pago->getMonto()-$total)<=($cuota->getMonto()-$cuota->getPagado())){
                    
                    $cuota->setPagado($cuota->getPagado()+($pago->getMonto()-$total));

                    $entityManager->persist($cuota);
                    $entityManager->flush();

                    $pagoCuota=new PagoCuotas();
                    $pagoCuota->setCuota($cuota);
                    $pagoCuota->setPago($pago);
                    $pagoCuota->setMonto($pago->getMonto()-$total);
                    $entityManager->persist($pagoCuota);
                    $entityManager->flush();

                   /* if($contrato->getCliente()->getTokuId()!=null){
                        if($cuota->getPagado()<$cuota->getMonto()){
                            $cuotaResultToku=$toku->crearInvoice($contrato->getCliente()->getTokuId(),strval($productId."_".$pago->getBoleta()),($pago->getMonto()-$total),$cuota->getFechaPago()->format('Y-m-d'),true);
                            error_log("<br><br> Pago Cuota crm".$pago->getBoleta()." : ".$cuota->getNumero()." Invoice id ".$cuota->getInvoiceId()." monto ".($pago->getMonto()-$total),3,"/home/micrm.cl/test/TokuWebhook_log");
                            
                            $cuotaResultToku=$toku->crearInvoice($contrato->getCliente()->getTokuId(),strval($productId),($cuota->getMonto()-$cuota->getPagado()),$cuota->getFechaPago()->format('Y-m-d'));
                            error_log("<br><br> Pago Cuota  : ".$cuota->getNumero()." Invoice id ".$cuota->getInvoiceId()." monto ".($cuota->getMonto()-$cuota->getPagado()),3,"/home/micrm.cl/test/TokuWebhook_log");
                        }else{
                            $cuotaResultToku=$toku->crearInvoice($contrato->getCliente()->getTokuId(),strval($productId),$pago->getMonto(),$cuota->getFechaPago()->format('Y-m-d'),true);
                            error_log("<br><br> Pago Cuota  : ".$cuota->getNumero()." Invoice id ".$cuota->getInvoiceId()." monto ".$pago->getMonto(),3,"/home/micrm.cl/test/TokuWebhook_log");
                            
                        }
                    }*/
                }else{
                    
                    //si pago es mayo o igual al monto de la cuota
                    if(($pago->getMonto()-$total)>=($cuota->getMonto()-$cuota->getPagado())){
                        
                        
                        $pagoCuota=new PagoCuotas();
                        $pagoCuota->setCuota($cuota);
                        $pagoCuota->setPago($pago);
                        $pagoCuota->setMonto($cuota->getMonto()-$cuota->getPagado());
                        $entityManager->persist($pagoCuota);
                        $entityManager->flush();

                        $cuota->setPagado($cuota->getMonto());
                        $entityManager->persist($cuota);
                        $entityManager->flush();
                        $pagostatus=true;
                       /* if($contrato->getCliente()->getTokuId()!=null){
                           
                                $cuotaResultToku=$toku->crearInvoice($contrato->getCliente()->getTokuId(),strval($productId),($cuota->getMonto()-$cuota->getPagado()),$cuota->getFechaPago()->format('Y-m-d'),true);
                                error_log("<br><br>  Cuota Pagada : ".$cuota->getNumero()." Invoice id ".$cuota->getInvoiceId()." monto ".($cuota->getMonto()-$cuota->getPagado()),3,"/home/micrm.cl/test/TokuWebhook_log");
         
                        }*/
                    }else if(($pago->getMonto()-$total)<($cuota->getMonto()-$cuota->getPagado())){
                        
                        
                        $pagoCuota=new PagoCuotas();
                        $pagoCuota->setCuota($cuota);
                        $pagoCuota->setPago($pago);
                        $pagoCuota->setMonto(($pago->getMonto()-$total));
                        $entityManager->persist($pagoCuota);
                        $entityManager->flush();

                        $cuota->setPagado(($pago->getMonto()-$total)+$cuota->getPagado());

                        $entityManager->persist($cuota);
                        $entityManager->flush();

                       /* if($contrato->getCliente()->getTokuId()!=null){
                           
                            $cuotaResultToku=$toku->crearInvoice($contrato->getCliente()->getTokuId(),strval($productId."_".$pago->getBoleta()),($pago->getMonto()-$total),$cuota->getFechaPago()->format('Y-m-d'),true);
                            error_log("<br><br> Pago Cuota crm".$pago->getBoleta()." : ".$cuota->getNumero()." Invoice id ".$cuota->getInvoiceId()." monto ".$pago->getMonto()-$total,3,"/home/micrm.cl/test/TokuWebhook_log");
                            
                            
                            $cuotaResultToku=$toku->crearInvoice($contrato->getCliente()->getTokuId(),strval($productId),($cuota->getMonto()-$cuota->getPagado()),$cuota->getFechaPago()->format('Y-m-d'));
                            error_log("<br><br> Pago Cuota  : ".$cuota->getNumero()." Invoice id ".$cuota->getInvoiceId()." monto ".($cuota->getMonto()-$cuota->getPagado()),3,"/home/micrm.cl/test/TokuWebhook_log");
                            
                        }*/
                    }
                }

                //tomamos las cuotas y reseteamos el q_mov de las cobranzas
                $cobranzas = $contrato->getCobranzas();
                $qMov=0;
                foreach($cobranzas as $cobranza){
                    $qMov++;
                }
                if($contrato->getQMov()-$qMov>0){
                    $contrato->setQMov($contrato->getQMov()-$qMov);
                    $entityManager->persist($contrato);
                    $entityManager->flush();
                }
            }else{
                //si estan todas las cuotas pagadas, buscamos la ultima cuota pagada y agregamos el monto sobrante a esa cuota
                if($pago->getMonto()-$total>0){
                    $cuota=$cuotaRepository->findOneByUltimaPagada($contrato->getId());
                    if($cuota){

                        $pagoCuota=new PagoCuotas();
                        $pagoCuota->setCuota($cuota);
                        $pagoCuota->setPago($pago);
                        $pagoCuota->setMonto(($pago->getMonto()-$total));
                        $entityManager->persist($pagoCuota);
                        $entityManager->flush();

                        $cuota->setPagado(($pago->getMonto()-$total)+$cuota->getPagado());

                        $entityManager->persist($cuota);
                        $entityManager->flush();
                        //tomamos las cuotas y reseteamos el q_mov de las cobranzas
                        $cobranzas = $contrato->getCobranzas();
                        $qMov=0;
                        foreach($cobranzas as $cobranza){
                            $qMov++;
                        }
                        if($contrato->getQMov()-$qMov>0){
                            $contrato->setQMov($contrato->getQMov()-$qMov);
                            $entityManager->persist($contrato);
                            $entityManager->flush();
                        }
                    }
                }
            }

        }while($pagostatus);

        $cuota=$cuotaRepository->findOneByPrimeraVigente($contrato->getId(),$esMulta);

    
        $entityManager->persist($contrato);
        $entityManager->flush();
        return true;
    }


    public function findPagos(){
        $query=$this->createQueryBuilder('p')
        ->join('p.cuota','cu')
        ->join('p.pago','pa')
        ->where("pa.fechaRegistro>='2022-08-10'")
        ->andWhere('cu.invoiceId is not null')
        ->andWhere("pa.pagoCanal<4");

        return $query->getQuery()
                ->getResult();
    }
    // /**
    //  * @return PagoCuotas[] Returns an array of PagoCuotas objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PagoCuotas
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
