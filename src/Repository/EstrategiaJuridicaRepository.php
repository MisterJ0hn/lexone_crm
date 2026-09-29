<?php

namespace App\Repository;

use App\Entity\EstrategiaJuridica;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EstrategiaJuridica|null find($id, $lockMode = null, $lockVersion = null)
 * @method EstrategiaJuridica|null findOneBy(array $criteria, array $orderBy = null)
 * @method EstrategiaJuridica[]    findAll()
 * @method EstrategiaJuridica[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EstrategiaJuridicaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstrategiaJuridica::class);
    }

    // /**
    //  * @return EstrategiaJuridica[] Returns an array of EstrategiaJuridica objects
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
    public function findOneBySomeField($value): ?EstrategiaJuridica
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
