<?php

namespace App\Repository;

use App\Entity\Juzgado;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Juzgado|null find($id, $lockMode = null, $lockVersion = null)
 * @method Juzgado|null findOneBy(array $criteria, array $orderBy = null)
 * @method Juzgado[]    findAll()
 * @method Juzgado[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JuzgadoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Juzgado::class);
    }

    public function findAll()
    {
        return $this->findBy(array(), array('id' => 'ASC'));
    }
    // /**
    //  * @return Juzgado[] Returns an array of Juzgado objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('j.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Juzgado
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
