<?php

namespace App\Repository;

use App\Entity\UsuarioTipo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UsuarioTipo|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsuarioTipo|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsuarioTipo[]    findAll()
 * @method UsuarioTipo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsuarioTipoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsuarioTipo::class);
    }


    public function findByEmpresa($empresa,$listado)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.empresa is null or u.empresa = :val')
            ->setParameter('val',$empresa)
            ->andWhere('u.id != (:val2)')
            ->setParameter('val2',$listado)
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }


    // /**
    //  * @return UsuarioTipo[] Returns an array of UsuarioTipo objects
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
    public function findOneBySomeField($value): ?UsuarioTipo
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
