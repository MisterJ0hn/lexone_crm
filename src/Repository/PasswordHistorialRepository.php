<?php

namespace App\Repository;

use App\Entity\PasswordHistorial;
use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PasswordHistorial|null find($id, $lockMode = null, $lockVersion = null)
 * @method PasswordHistorial|null findOneBy(array $criteria, array $orderBy = null)
 * @method PasswordHistorial[]    findAll()
 * @method PasswordHistorial[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PasswordHistorialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordHistorial::class);
    }

    /**
     * Retorna los últimos N passwords hasheados del usuario, ordenados del más reciente al más antiguo.
     *
     * @return PasswordHistorial[]
     */
    public function findUltimosN(Usuario $usuario, int $n): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.usuario = :usuario')
            ->setParameter('usuario', $usuario)
            ->orderBy('p.fechaCreacion', 'DESC')
            ->setMaxResults($n)
            ->getQuery()
            ->getResult();
    }

    /**
     * Elimina registros antiguos, conservando solo los últimos N del usuario.
     */
    public function eliminarAntiguos(Usuario $usuario, int $conservar): void
    {
        $rows = $this->createQueryBuilder('p')
            ->select('p.id')
            ->andWhere('p.usuario = :usuario')
            ->setParameter('usuario', $usuario)
            ->orderBy('p.fechaCreacion', 'DESC')
            ->setMaxResults($conservar)
            ->getQuery()
            ->getResult();

        $ids = array_column($rows, 'id');

        if (empty($ids)) {
            return;
        }

        $this->createQueryBuilder('p')
            ->delete()
            ->andWhere('p.usuario = :usuario')
            ->andWhere('p.id NOT IN (:ids)')
            ->setParameter('usuario', $usuario)
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }
}
