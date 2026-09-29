<?php

namespace App\Repository;

use App\Entity\Escritura;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Escritura|null find($id, $lockMode = null, $lockVersion = null)
 * @method Escritura|null findOneBy(array $criteria, array $orderBy = null)
 * @method Escritura[]    findAll()
 * @method Escritura[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EscrituraRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Escritura::class);
    }

    // /**
    //  * @return Escritura[] Returns an array of Escritura objects
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
    public function findOneBySomeField($value): ?Escritura
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
