<?php

namespace App\Repository;

use App\Entity\EstadoProcesal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EstadoProcesal|null find($id, $lockMode = null, $lockVersion = null)
 * @method EstadoProcesal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EstadoProcesalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstadoProcesal::class);
    }
}
