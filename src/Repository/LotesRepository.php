<?php

namespace App\Repository;

use App\Entity\Lotes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Lotes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lotes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lotes[]    findAll()
 * @method Lotes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LotesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lotes::class);
    }

    public function findHabilitados()
    {
        $query=$this->createQueryBuilder('l')
        ->leftJoin('l.usuarioLotes','ul')
        ->andWhere('ul.id is null')
        ->andWhere('l.estado = true')
        ->orderBy('l.orden','ASC');
    
    return $query->getQuery()
        ->getResult()
    ;

    }
    public function findPrimerDisponible(): ?Lotes
    {
        $query=$this->createQueryBuilder('l')
        ->andWhere('l.isUtilizado = false')
        ->andWhere('l.estado = true')
        ->orderBy('l.orden','ASC');
    
    return $query->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult()
    ;

    }

    
    // /**
    //  * @return Lotes[] Returns an array of Lotes objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Lotes
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
