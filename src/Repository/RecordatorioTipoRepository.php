<?php

namespace App\Repository;

use App\Entity\RecordatorioTipo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RecordatorioTipo|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecordatorioTipo|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecordatorioTipo[]    findAll()
 * @method RecordatorioTipo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecordatorioTipoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecordatorioTipo::class);
    }

    // /**
    //  * @return RecordatorioTipo[] Returns an array of RecordatorioTipo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RecordatorioTipo
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
