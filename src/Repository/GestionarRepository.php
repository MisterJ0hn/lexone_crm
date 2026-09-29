<?php

namespace App\Repository;

use App\Entity\Gestionar;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Gestionar|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gestionar|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gestionar[]    findAll()
 * @method Gestionar[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GestionarRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gestionar::class);
    }

    // /**
    //  * @return Gestionar[] Returns an array of Gestionar objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Gestionar
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
