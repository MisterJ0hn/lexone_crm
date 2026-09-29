<?php

namespace App\Repository;

use App\Entity\TipoCliente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TipoCliente|null find($id, $lockMode = null, $lockVersion = null)
 * @method TipoCliente|null findOneBy(array $criteria, array $orderBy = null)
 * @method TipoCliente[]    findAll()
 * @method TipoCliente[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TipoClienteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TipoCliente::class);
    }

    /**
     * Busca un tipo de cliente por su nombre ('Persona', 'Convenio', 'Empresa').
     */
    public function findOneByNombre(?string $nombre): ?TipoCliente
    {
        if ($nombre === null || $nombre === '') {
            return null;
        }

        return $this->findOneBy(['nombre' => $nombre]);
    }
}
