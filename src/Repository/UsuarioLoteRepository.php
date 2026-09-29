<?php

namespace App\Repository;

use App\Entity\UsuarioLote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UsuarioLote|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsuarioLote|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsuarioLote[]    findAll()
 * @method UsuarioLote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsuarioLoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsuarioLote::class);
    }

    // /**
    //  * @return UsuarioLote[] Returns an array of UsuarioLote objects
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
    public function findOneBySomeField($value): ?UsuarioLote
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
