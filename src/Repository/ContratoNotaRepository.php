<?php

namespace App\Repository;

use App\Entity\ContratoNota;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContratoNota|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContratoNota[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContratoNotaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContratoNota::class);
    }
}
