<?php

namespace App\Repository;

use App\Entity\ContratoVehiculo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContratoVehiculo|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContratoVehiculo|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContratoVehiculo[]    findAll()
 * @method ContratoVehiculo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContratoVehiculoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContratoVehiculo::class);
    }

    // /**
    //  * @return ContratoVehiculo[] Returns an array of ContratoVehiculo objects
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
    public function findOneBySomeField($value): ?ContratoVehiculo
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
