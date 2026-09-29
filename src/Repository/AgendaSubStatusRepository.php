<?php

namespace App\Repository;

use App\Entity\AgendaSubStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AgendaSubStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgendaSubStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgendaSubStatus[]    findAll()
 * @method AgendaSubStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendaSubStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgendaSubStatus::class);
    }

    // /**
    //  * @return AgendaSubStatus[] Returns an array of AgendaSubStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AgendaSubStatus
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
