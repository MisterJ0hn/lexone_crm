<?php

namespace App\Repository;

use App\Entity\EstrategiaJuridicaReporte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EstrategiaJuridicaReporte>
 *
 * @method EstrategiaJuridicaReporte|null find($id, $lockMode = null, $lockVersion = null)
 * @method EstrategiaJuridicaReporte|null findOneBy(array $criteria, array $orderBy = null)
 * @method EstrategiaJuridicaReporte[]    findAll()
 * @method EstrategiaJuridicaReporte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EstrategiaJuridicaReporteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstrategiaJuridicaReporte::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(EstrategiaJuridicaReporte $entity, bool $flush = true): void
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
    public function remove(EstrategiaJuridicaReporte $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return EstrategiaJuridicaReporte[] Returns an array of EstrategiaJuridicaReporte objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EstrategiaJuridicaReporte
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
