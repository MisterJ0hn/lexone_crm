<?php

namespace App\Repository;

use App\Entity\Privilegio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Privilegio|null find($id, $lockMode = null, $lockVersion = null)
 * @method Privilegio|null findOneBy(array $criteria, array $orderBy = null)
 * @method Privilegio[]    findAll()
 * @method Privilegio[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrivilegioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Privilegio::class);
    }

    public function findByEmpresa($empresa_id){
        $query=$this->createQueryBuilder('p')
        ->join('p.moduloPer','m')
        ->join('m.empresa','e')
        ->andWhere('e.id='.$empresa_id);


        return $query->getQuery()
        ->getResult();

    }

    // /**
    //  * @return Privilegio[] Returns an array of Privilegio objects
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
    public function findOneBySomeField($value): ?Privilegio
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
