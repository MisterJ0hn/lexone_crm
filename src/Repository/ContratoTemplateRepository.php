<?php

namespace App\Repository;

use App\Entity\ContratoTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContratoTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContratoTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContratoTemplate[]    findAll()
 * @method ContratoTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContratoTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContratoTemplate::class);
    }

    /**
     * Plantillas que puede elegir el usuario al crear un contrato: las
     * disponibles de la empresa para el tipo de cliente de su agenda.
     *
     * @return ContratoTemplate[]
     */
    public function findDisponibles(int $empresaId, int $tipoClienteId): array
    {
        return $this->findBy([
            'empresa' => $empresaId,
            'tipoCliente' => $tipoClienteId,
            'activo' => true,
        ], ['nombre' => 'ASC']);
    }

    /**
     * Respaldo para contratos creados antes de poder elegir plantilla, o sin
     * ninguna seleccionada: la primera disponible de esa combinación.
     */
    public function findActiva(int $empresaId, int $tipoClienteId): ?ContratoTemplate
    {
        return $this->findOneBy([
            'empresa' => $empresaId,
            'tipoCliente' => $tipoClienteId,
            'activo' => true,
        ], ['id' => 'ASC']);
    }
}
