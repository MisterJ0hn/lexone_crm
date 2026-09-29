<?php

namespace App\Repository;

use App\Entity\InfComisionCobradores;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InfComisionCobradores>
 *
 * @method InfComisionCobradores|null find($id, $lockMode = null, $lockVersion = null)
 * @method InfComisionCobradores|null findOneBy(array $criteria, array $orderBy = null)
 * @method InfComisionCobradores[]    findAll()
 * @method InfComisionCobradores[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InfComisionCobradoresRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InfComisionCobradores::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(InfComisionCobradores $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(InfComisionCobradores $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function removeBySesion(int $sesion): void
    {
        $q = $this->_em->createQuery('delete from App:InfComisionCobradores tb where tb.sesion = '.$sesion);
       
        $numDeleted = $q->execute();
    }

    //Retorna totales de cobranza
    public function totales($usuario=null,$empresa=null,$compania=null,$filtro=null,$otros=null,$sesion)
    {
        $query=$this->createQueryBuilder('i');
        $query->select(array('count(i.id)','sum(i.monto)'))
        ->join("i.cobranza",'c')
        ->join("i.pago","p")
        ->join("i.contrato",'co')
        ->join('co.agenda','a')
        ->join('a.cuenta','cu')
        ->join('c.usuarioRegistro','u');

        if(!is_null($empresa)){
            
            $query->andWhere('cu.empresa = '.$empresa);
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
        $query->andWhere('i.sesion='.$sesion);
    
        return $query->getQuery()
        ->getResult();

    }

    //Retorna totales de cobranza
    public function cantidadTotal($usuario=null,$empresa=null,$compania=null,$filtro=null,$otros=null)
    {
        $query=$this->createQueryBuilder('i');
        $query->select(array('count(i.id)'))
        ->join("i.cobranza",'c')
        ->join("i.pago","p")
        ->join("i.contrato",'co')
        ->join('co.agenda','a')
        ->join('a.cuenta','cu')
        ->join('c.usuarioRegistro','u');

        if(!is_null($empresa)){
            
            $query->andWhere('cu.empresa = '.$empresa);
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
        ->getResult();

    }

    //Retorna totales de cobranza agrupados por usuario cobrador
    public function totalesUsuario($sesion)
    {
        $query=$this->createQueryBuilder('i');
        $query->select(array('i','count(i.id)','sum(i.monto)'))
        ->join("i.cobranza",'c')
        ->join("i.pago","p")
        ->join("i.contrato",'co')
        ->join('co.agenda','a')
        ->join('a.cuenta','cu')
        ->join('c.usuarioRegistro','u')
        ->where('i.sesion='.$sesion);


       

        $query->groupBy('c.usuarioRegistro')
        ;

        return $query->getQuery()
            ->getResult()
        ;

    }

    public function findByPers($sesion, int $usuario=null){
        $query=$this->createQueryBuilder('i');
        $query->join('i.cobranza','c')
        ->join('c.usuarioRegistro','u')
        ->where("i.sesion='$sesion'");

        if($usuario != null){
            $query->where('u.id='.$usuario);
        }

        return $query->getQuery()->getResult();

    }
    // /**
    //  * @return InfComisionCobradores[] Returns an array of InfComisionCobradores objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?InfComisionCobradores
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
