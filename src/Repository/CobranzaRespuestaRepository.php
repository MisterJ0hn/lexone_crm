<?php

namespace App\Repository;

use App\Entity\CobranzaRespuesta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CobranzaRespuesta|null find($id, $lockMode = null, $lockVersion = null)
 * @method CobranzaRespuesta|null findOneBy(array $criteria, array $orderBy = null)
 * @method CobranzaRespuesta[]    findAll()
 * @method CobranzaRespuesta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CobranzaRespuestaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CobranzaRespuesta::class);
    }

    public function qMov(){
        
    }
    // /**
    //  * @return CobranzaRespuesta[] Returns an array of CobranzaRespuesta objects
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
    public function findOneBySomeField($value): ?CobranzaRespuesta
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
