<?php

namespace App\Repository;

use App\Entity\CorreoBienvenida;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CorreoBienvenida|null findOneBy(array $criteria, array $orderBy = null)
 * @method CorreoBienvenida[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CorreoBienvenidaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CorreoBienvenida::class);
    }
}
