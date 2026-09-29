<?php

namespace App\Repository;

use App\Entity\Reportes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Reportes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reportes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reportes[]    findAll()
 * @method Reportes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReportesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reportes::class);
    }

    // /**
    //  * @return Reportes[] Returns an array of Reportes objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Reportes
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
