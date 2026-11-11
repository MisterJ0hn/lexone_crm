<?php

namespace App\Repository;

use App\Entity\PlanPrepago;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PlanPrepago|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlanPrepago|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlanPrepago[]    findAll()
 * @method PlanPrepago[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlanPrepagoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlanPrepago::class);
    }
}
