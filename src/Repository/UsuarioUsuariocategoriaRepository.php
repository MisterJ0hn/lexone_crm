<?php

namespace App\Repository;

use App\Entity\UsuarioUsuariocategoria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UsuarioUsuariocategoria|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsuarioUsuariocategoria|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsuarioUsuariocategoria[]    findAll()
 * @method UsuarioUsuariocategoria[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsuarioUsuariocategoriaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsuarioUsuariocategoria::class);
    }

    // /**
    //  * @return UsuarioUsuariocategoria[] Returns an array of UsuarioUsuariocategoria objects
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
    public function findOneBySomeField($value): ?UsuarioUsuariocategoria
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
