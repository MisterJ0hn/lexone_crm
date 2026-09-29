<?php

namespace App\Repository;

use App\Entity\UsuarioStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UsuarioStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsuarioStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsuarioStatus[]    findAll()
 * @method UsuarioStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsuarioStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsuarioStatus::class);
    }

    // /**
    //  * @return UsuarioStatus[] Returns an array of UsuarioStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UsuarioStatus
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
