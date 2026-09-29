<?php

namespace App\Repository;

use App\Entity\ContratoAnexo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContratoAnexo|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContratoAnexo|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContratoAnexo[]    findAll()
 * @method ContratoAnexo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContratoAnexoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContratoAnexo::class);
    }

    // /**
    //  * @return ContratoAnexo[] Returns an array of ContratoAnexo objects
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
    public function findOneBySomeField($value): ?ContratoAnexo
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
