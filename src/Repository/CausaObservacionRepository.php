<?php

namespace App\Repository;

use App\Entity\CausaObservacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CausaObservacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method CausaObservacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method CausaObservacion[]    findAll()
 * @method CausaObservacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CausaObservacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CausaObservacion::class);
    }

    // /**
    //  * @return CausaObservacion[] Returns an array of CausaObservacion objects
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
    public function findOneBySomeField($value): ?CausaObservacion
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
