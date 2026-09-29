<?php

namespace App\Repository;

use App\Entity\Pago;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Pago|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pago|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pago[]    findAll()
 * @method Pago[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PagoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pago::class);
    }

    public function findByContrato($value, $esMulta=false)
    {
        $query=$this->createQueryBuilder('p')
        ->join('p.pagoCuotas','pc')
        ->join('pc.cuota','c')
        ->andWhere('c.contrato = :val');
        if($esMulta){
            $query->andWhere('c.isMulta = true');
        }

        $query->setParameter('val', $value)
        ->orderBy('p.id', 'ASC');

        return $query
            ->getQuery()
            ->getResult()
        ;
    }

    public function findUPByContrato($value)
    {
        return $this->createQueryBuilder('p')
            ->join('p.pagoCuotas','pc')
            ->join('pc.cuota','c')
            ->andWhere('c.contrato = :val')
            ->setParameter('val',$value)
            ->orderBy('p.fechaPago', 'Desc')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByPers($usuario=null,$empresa=null,$compania=null,$filtro=null,$otros=null){
        $query=$this->createQueryBuilder('p');
        $query->join('p.pagoCuotas','pc');
        $query->join('pc.cuota','c');
        $query->join('c.contrato','co');
        $query->join('co.agenda','a');
        $query->join('a.cuenta','cu');
        if(!is_null($empresa)){
            
            $query->andWhere('a.empresa = '.$empresa);
        }
        if(!is_null($usuario)){
            
            $query->andWhere('p.usuarioRegistro = '.$usuario);
        }
        if(!is_null($filtro)){ 
            $query->andWhere("(co.nombre like '%$filtro%' or co.rut like '%$filtro%')")
         ;

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }


        return $query->getQuery()
            ->getResult()
        ;

    }
    
    public function findByPersCount($usuario=null,$empresa=null,$compania=null,$filtro=null,$otros=null)
    {
        $query=$this->createQueryBuilder('p');
        $query->select(array('p','sum(pc.monto)'));
        $query->join('p.pagoCuotas','pc');
        $query->join('pc.cuota','c');
        $query->join('c.contrato','co');
        $query->join('co.agenda','a');
        $query->join('a.cuenta','cu');
        if(!is_null($empresa)){
            
            $query->andWhere('a.empresa = '.$empresa);
        }
        if(!is_null($usuario)){
            
            $query->andWhere('p.usuarioRegistro = '.$usuario);
        }
        if(!is_null($filtro)){ 
            $query->andWhere("(co.nombre like '%$filtro%' or co.rut like '%$filtro%')")
         ;

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        

        return $query->getQuery()
        
            ->getOneOrNullResult()
        ;

    }

    //Grafico Pagos
    public function findByPersCountPeriodoPagos($usuario=null,$empresa=null,$compania=null,$filtro=null,$otros=null)
    {
        $query=$this->createQueryBuilder('p');
        $query->select(array('p','sum(pc.monto) as valor'));
        $query->join('p.pagoCuotas','pc');
        $query->join('pc.cuota','c');
        $query->join('c.contrato','co');
        $query->join('co.agenda','a');
        $query->join('a.cuenta','cu');
        if(!is_null($empresa)){
            
            $query->andWhere('a.empresa = '.$empresa);
        }
        if(!is_null($usuario)){
            
            $query->andWhere('p.usuarioRegistro = '.$usuario);
        }
        if(!is_null($filtro)){ 
            $query->andWhere("(co.nombre like '%$filtro%' or co.rut like '%$filtro%')")
         ;

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        

        return $query->getQuery()
        
            ->getOneOrNullResult()
        ;

    }
    //Grafico Pagos
    
    // /**
    //  * @return Pago[] Returns an array of Pago objects
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
    public function findOneBySomeField($value): ?Pago
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
