<?php

namespace App\Repository;

use App\Entity\Importacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Importacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Importacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Importacion[]    findAll()
 * @method Importacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImportacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Importacion::class);
    }

    // /**
    //  * @return Importacion[] Returns an array of Importacion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Importacion
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
