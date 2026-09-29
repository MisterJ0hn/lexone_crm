<?php

namespace App\Repository;

use App\Entity\ContratoRol;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContratoRol|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContratoRol|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContratoRol[]    findAll()
 * @method ContratoRol[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContratoRolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContratoRol::class);
    }
    public function findByTemporal($abogado)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.abogado = :val')
            ->setParameter('val', $abogado)
            ->andWhere('c.contrato is null')
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return ContratoRol[] Returns an array of ContratoRol objects
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
    public function findOneBySomeField($value): ?ContratoRol
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
