<?php

namespace App\Repository;

use App\Entity\PjudExhortos;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PjudExhortos>
 *
 * @method PjudExhortos|null find($id, $lockMode = null, $lockVersion = null)
 * @method PjudExhortos|null findOneBy(array $criteria, array $orderBy = null)
 * @method PjudExhortos[]    findAll()
 * @method PjudExhortos[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PjudExhortosRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PjudExhortos::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(PjudExhortos $entity, bool $flush = true): void
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
    public function remove(PjudExhortos $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return PjudExhortos[] Returns an array of PjudExhortos objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PjudExhortos
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
