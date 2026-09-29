<?php

namespace App\Repository;

use App\Entity\Cliente;
use App\Security\Cifrado;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Cliente|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cliente|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cliente[]    findAll()
 * @method Cliente[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClienteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cliente::class);
    }

    /**
     * Busca un cliente por RUT. Las columnas de PII están cifradas con IV aleatorio,
     * así que la búsqueda es por el hash determinístico (rutHash), no por LIKE.
     */
    public function findOneByRut(?string $rut): ?Cliente
    {
        if ($rut === null || $rut === '') {
            return null;
        }

        return $this->findOneBy(['rutHash' => Cifrado::hash($rut, 'rut')]);
    }
}
