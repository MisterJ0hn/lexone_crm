<?php

namespace App\Repository;

use App\Entity\AgendaObservacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AgendaObservacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgendaObservacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgendaObservacion[]    findAll()
 * @method AgendaObservacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendaObservacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgendaObservacion::class);
    }

    // /**
    //  * @return AgendaObservacion[] Returns an array of AgendaObservacion objects
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
    public function findOneBySomeField($value): ?AgendaObservacion
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
