<?php

namespace App\Repository;

use App\Entity\ContratoVivienda;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContratoVivienda|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContratoVivienda|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContratoVivienda[]    findAll()
 * @method ContratoVivienda[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContratoViviendaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContratoVivienda::class);
    }

    // /**
    //  * @return ContratoVivienda[] Returns an array of ContratoVivienda objects
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
    public function findOneBySomeField($value): ?ContratoVivienda
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
