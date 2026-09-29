<?php

namespace App\Repository;

use App\Entity\ActuacionAnexoProcesal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ActuacionAnexoProcesal>
 *
 * @method ActuacionAnexoProcesal|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActuacionAnexoProcesal|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActuacionAnexoProcesal[]    findAll()
 * @method ActuacionAnexoProcesal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActuacionAnexoProcesalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActuacionAnexoProcesal::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(ActuacionAnexoProcesal $entity, bool $flush = true): void
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
    public function remove(ActuacionAnexoProcesal $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return ActuacionAnexoProcesal[] Returns an array of ActuacionAnexoProcesal objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ActuacionAnexoProcesal
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
