<?php

namespace App\Repository;

use App\Entity\Cobranza;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Cobranza|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cobranza|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cobranza[]    findAll()
 * @method Cobranza[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CobranzaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cobranza::class);
    }
    public function findByContrato($value)
    {
        $query=$this->createQueryBuilder('c')
      
        ->andWhere('c.contrato = :val');
        $query->setParameter('val', $value)
        ->orderBy('c.id', 'ASC');

        return $query
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByPers($usuario=null,$empresa=null,$compania=null,$filtro=null,$otros=null){
        $query=$this->createQueryBuilder('c')
        ->join('c.contrato','co')
        ->join('co.agenda','a')
        ->join('a.cuenta','cu')
        ->join('c.usuarioRegistro','u');
        

        if(!is_null($usuario)){
            $query->andWhere('u.id = '.$usuario);
        }
        if(!is_null($empresa)){
            $query->andWhere('a.empresa = '.$empresa);
        }
        if(!is_null($usuario)){
            
            $query->andWhere('p.usuarioRegistro = '.$usuario);
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
        $query->orderBy('c.fechaHora','desc');
        return $query->getQuery()
            ->getResult()
        ;

    }

    public function findByUltimaGestion($usuario=null,$empresa=null,$compania=null,$filtro=null,$otros=null){
        $query=$this->createQueryBuilder('c')
        ->join('c.contrato','co')
        ->join('co.agenda','a')
        ->join('a.cuenta','cu')
        ->join('c.usuarioRegistro','u');
        

        if(!is_null($usuario)){
            $query->andWhere('u.id = '.$usuario);
        }
        if(!is_null($empresa)){
            $query->andWhere('a.empresa = '.$empresa);
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
        $query->orderBy('co.id desc, c.fechaHora', 'Desc')
        ;
        return $query->getQuery()
            ->getResult()
        ;

    }
   
    public function findByUltimaGestionObj($usuario=null,$empresa=null,$compania=null,$filtro=null,$otros=null): ?Cobranza
    {
        $query=$this->createQueryBuilder('c')
        ->join('c.contrato','co')
        ->join('co.agenda','a')
        ->join('a.cuenta','cu')
        ->join('c.usuarioRegistro','u');
        if(!is_null($usuario)){
            $query->andWhere('u.id = '.$usuario);
        }
        if(!is_null($empresa)){
            $query->andWhere('a.empresa = '.$empresa);
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
        $query->orderBy('co.id, c.fechaHora', 'Desc')
        ->setMaxResults(1);
        return $query->getQuery()
            ->getOneOrNullResult();


    }
    public function findByContratoGroup($usuario=null,$empresa=null,$compania=null,$filtro=null,$otros=null){
        $query=$this->createQueryBuilder('c');
        $query->select(array('c','count(c.id)'));
        $query->join('c.contrato','co');
        $query->join('co.agenda','a');
        $query->join('a.cuenta','cu');
        $query->join('c.usuarioRegistro','u');
        if(!is_null($empresa)){
            
            $query->andWhere('a.empresa = '.$empresa);
        }
        if(!is_null($usuario)){
            
            $query->andWhere('c.usuarioRegistro = '.$usuario);
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

      
        $query->groupBy('c.usuarioRegistro,c.fecha ')
        ->orderBy('u.nombre,c.fecha', 'ASC')
        ;

        return $query->getQuery()
            ->getResult()
        ;

    }

    public function findByContratoGroupCount($usuario=null,$empresa=null,$compania=null,$filtro=null,$otros=null){
        $query=$this->createQueryBuilder('c ');
        $query->select(array('c','count(c.id)'));
        $query->join('c.contrato','co');
        $query->join('co.agenda','a');
        $query->join('a.cuenta','cu');
        $query->join('c.usuarioRegistro','u');
        if(!is_null($empresa)){
            
            $query->andWhere('a.empresa = '.$empresa);
        }
        if(!is_null($usuario)){
            
            $query->andWhere('c.usuarioRegistro = '.$usuario);
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


    // /**
    //  * @return Cobranza[] Returns an array of Cobranza objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Cobranza
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
