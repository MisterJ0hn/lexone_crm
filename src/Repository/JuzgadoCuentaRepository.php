<?php

namespace App\Repository;

use App\Entity\JuzgadoCuenta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method JuzgadoCuenta|null find($id, $lockMode = null, $lockVersion = null)
 * @method JuzgadoCuenta|null findOneBy(array $criteria, array $orderBy = null)
 * @method JuzgadoCuenta[]    findAll()
 * @method JuzgadoCuenta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JuzgadoCuentaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JuzgadoCuenta::class);
    }

    // /**
    //  * @return JuzgadoCuenta[] Returns an array of JuzgadoCuenta objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('j.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?JuzgadoCuenta
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
