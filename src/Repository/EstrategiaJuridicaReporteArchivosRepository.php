<?php

namespace App\Repository;

use App\Entity\EstrategiaJuridicaReporteArchivos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EstrategiaJuridicaReporteArchivos>
 *
 * @method EstrategiaJuridicaReporteArchivos|null find($id, $lockMode = null, $lockVersion = null)
 * @method EstrategiaJuridicaReporteArchivos|null findOneBy(array $criteria, array $orderBy = null)
 * @method EstrategiaJuridicaReporteArchivos[]    findAll()
 * @method EstrategiaJuridicaReporteArchivos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EstrategiaJuridicaReporteArchivosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstrategiaJuridicaReporteArchivos::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(EstrategiaJuridicaReporteArchivos $entity, bool $flush = true): void
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
    public function remove(EstrategiaJuridicaReporteArchivos $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return EstrategiaJuridicaReporteArchivos[] Returns an array of EstrategiaJuridicaReporteArchivos objects
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
    public function findOneBySomeField($value): ?EstrategiaJuridicaReporteArchivos
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
