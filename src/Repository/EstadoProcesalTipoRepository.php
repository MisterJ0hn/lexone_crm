<?php

namespace App\Repository;

use App\Entity\EstadoProcesalTipo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EstadoProcesalTipo|null find($id, $lockMode = null, $lockVersion = null)
 * @method EstadoProcesalTipo|null findOneBy(array $criteria, array $orderBy = null)
 * @method EstadoProcesalTipo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EstadoProcesalTipoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EstadoProcesalTipo::class);
    }
}
