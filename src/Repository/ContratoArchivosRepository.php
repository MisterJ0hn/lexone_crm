<?php

namespace App\Repository;

use App\Entity\ContratoArchivos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContratoArchivos|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContratoArchivos|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContratoArchivos[]    findAll()
 * @method ContratoArchivos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContratoArchivosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContratoArchivos::class);
    }

    // /**
    //  * @return ContratoArchivos[] Returns an array of ContratoArchivos objects
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
    public function findOneBySomeField($value): ?ContratoArchivos
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
