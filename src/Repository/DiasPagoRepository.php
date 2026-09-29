<?php

namespace App\Repository;

use App\Entity\DiasPago;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DiasPago|null find($id, $lockMode = null, $lockVersion = null)
 * @method DiasPago|null findOneBy(array $criteria, array $orderBy = null)
 * @method DiasPago[]    findAll()
 * @method DiasPago[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DiasPagoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiasPago::class);
    }

    // /**
    //  * @return DiasPago[] Returns an array of DiasPago objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DiasPago
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
