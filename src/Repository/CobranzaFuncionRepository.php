<?php

namespace App\Repository;

use App\Entity\CobranzaFuncion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CobranzaFuncion|null find($id, $lockMode = null, $lockVersion = null)
 * @method CobranzaFuncion|null findOneBy(array $criteria, array $orderBy = null)
 * @method CobranzaFuncion[]    findAll()
 * @method CobranzaFuncion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CobranzaFuncionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CobranzaFuncion::class);
    }

    // /**
    //  * @return CobranzaFuncion[] Returns an array of CobranzaFuncion objects
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
    public function findOneBySomeField($value): ?CobranzaFuncion
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
