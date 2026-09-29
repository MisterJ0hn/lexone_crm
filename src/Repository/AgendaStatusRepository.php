<?php

namespace App\Repository;

use App\Entity\AgendaStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AgendaStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgendaStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgendaStatus[]    findAll()
 * @method AgendaStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendaStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgendaStatus::class);
    }

    // /**
    //  * @return AgendaStatus[] Returns an array of AgendaStatus objects
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
    public function findOneBySomeField($value): ?AgendaStatus
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
