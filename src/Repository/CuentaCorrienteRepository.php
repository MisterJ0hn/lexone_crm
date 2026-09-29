<?php

namespace App\Repository;

use App\Entity\CuentaCorriente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CuentaCorriente|null find($id, $lockMode = null, $lockVersion = null)
 * @method CuentaCorriente|null findOneBy(array $criteria, array $orderBy = null)
 * @method CuentaCorriente[]    findAll()
 * @method CuentaCorriente[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CuentaCorrienteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CuentaCorriente::class);
    }

    // /**
    //  * @return CuentaCorriente[] Returns an array of CuentaCorriente objects
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
    public function findOneBySomeField($value): ?CuentaCorriente
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
