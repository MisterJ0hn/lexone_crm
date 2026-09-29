<?php

namespace App\Repository;

use App\Entity\MenuCabezera;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MenuCabezera|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuCabezera|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuCabezera[]    findAll()
 * @method MenuCabezera[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuCabezeraRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuCabezera::class);
    }

    // /**
    //  * @return MenuCabezera[] Returns an array of MenuCabezera objects
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
    public function findOneBySomeField($value): ?MenuCabezera
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
