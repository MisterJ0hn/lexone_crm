<?php

namespace App\Repository;

use App\Entity\ContratoObservacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContratoObservacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContratoObservacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContratoObservacion[]    findAll()
 * @method ContratoObservacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContratoObservacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContratoObservacion::class);
    }

    // /**
    //  * @return ContratoObservacion[] Returns an array of ContratoObservacion objects
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
    public function findOneBySomeField($value): ?ContratoObservacion
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
