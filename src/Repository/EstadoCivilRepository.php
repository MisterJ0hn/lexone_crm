<?php

namespace App\Repository;

use App\Entity\EstadoCivil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EstadoCivil|null find($id, $lockMode = null, $lockVersion = null)
 * @method EstadoCivil|null findOneBy(array $criteria, array $orderBy = null)
 * @method EstadoCivil[]    findAll()
 * @method EstadoCivil[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EstadoCivilRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstadoCivil::class);
    }

    // /**
    //  * @return EstadoCivil[] Returns an array of EstadoCivil objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EstadoCivil
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
