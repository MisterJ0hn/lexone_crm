<?php

namespace App\Repository;

use App\Entity\Comuna;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Comuna|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comuna|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comuna[]    findAll()
 * @method Comuna[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ComunaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comuna::class);
    }

    // /**
    //  * @return Comuna[] Returns an array of Comuna objects
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
    public function findOneBySomeField($value): ?Comuna
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
