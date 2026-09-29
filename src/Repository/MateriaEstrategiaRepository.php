<?php

namespace App\Repository;

use App\Entity\MateriaEstrategia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MateriaEstrategia|null find($id, $lockMode = null, $lockVersion = null)
 * @method MateriaEstrategia|null findOneBy(array $criteria, array $orderBy = null)
 * @method MateriaEstrategia[]    findAll()
 * @method MateriaEstrategia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MateriaEstrategiaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MateriaEstrategia::class);
    }

    // /**
    //  * @return MateriaEstrategia[] Returns an array of MateriaEstrategia objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MateriaEstrategia
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
