<?php

namespace App\Repository;

use App\Entity\Corte;
use App\Entity\Juzgado;
use App\Entity\Materia;
use App\Entity\MateriaJuzgado;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MateriaJuzgado>
 *
 * @method MateriaJuzgado|null find($id, $lockMode = null, $lockVersion = null)
 * @method MateriaJuzgado|null findOneBy(array $criteria, array $orderBy = null)
 * @method MateriaJuzgado[]    findAll()
 * @method MateriaJuzgado[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MateriaJuzgadoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MateriaJuzgado::class);
    }

    /**
     * Juzgados de una corte que atienden la materia, ordenados por nombre.
     *
     * @return Juzgado[]
     */
    public function findJuzgados(Materia $materia, Corte $corte): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('j')
            ->from(Juzgado::class, 'j')
            ->innerJoin(MateriaJuzgado::class, 'mj', 'WITH', 'mj.juzgado = j')
            ->andWhere('mj.materia = :materia')
            ->andWhere('j.corte = :corte')
            ->setParameter('materia', $materia)
            ->setParameter('corte', $corte)
            ->orderBy('j.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function atiende(Materia $materia, Juzgado $juzgado): bool
    {
        return $this->count(['materia' => $materia, 'juzgado' => $juzgado]) > 0;
    }
}
