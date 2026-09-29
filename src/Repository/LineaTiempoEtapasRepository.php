<?php

namespace App\Repository;

use App\Entity\LineaTiempoEtapas;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LineaTiempoEtapas>
 *
 * @method LineaTiempoEtapas|null find($id, $lockMode = null, $lockVersion = null)
 * @method LineaTiempoEtapas|null findOneBy(array $criteria, array $orderBy = null)
 * @method LineaTiempoEtapas[]    findAll()
 * @method LineaTiempoEtapas[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LineaTiempoEtapasRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LineaTiempoEtapas::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(LineaTiempoEtapas $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(LineaTiempoEtapas $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

     /**
      * @return LineaTiempoEtapas[] Returns an array of LineaTiempoEtapas objects
     */
 
    public function findByRango(int $etapaInicio,int $etapaFin,int $lineaTiempo)
    {
        return $this->createQueryBuilder('l')
            ->join('l.lineaTiempo','lt')
            ->andWhere('l.id > '.$etapaInicio)
            ->andWhere("l.id <= ".$etapaFin)
            ->andWhere("lt.id=".$lineaTiempo)
            ->orderBy('l.id', 'ASC')
 
            ->getQuery()
            ->getResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?LineaTiempoEtapas
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
