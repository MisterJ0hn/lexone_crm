<?php

namespace App\Repository;

use App\Entity\UsuarioTipoDocumento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UsuarioTipoDocumento|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsuarioTipoDocumento|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsuarioTipoDocumento[]    findAll()
 * @method UsuarioTipoDocumento[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsuarioTipoDocumentoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsuarioTipoDocumento::class);
    }

    // /**
    //  * @return UsuarioTipoDocumento[] Returns an array of UsuarioTipoDocumento objects
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
    public function findOneBySomeField($value): ?UsuarioTipoDocumento
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
