<?php

namespace App\Repository;

use App\Entity\Causa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Causa>
 *
 * @method Causa|null find($id, $lockMode = null, $lockVersion = null)
 * @method Causa|null findOneBy(array $criteria, array $orderBy = null)
 * @method Causa[]    findAll()
 * @method Causa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CausaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Causa::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Causa $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Causa $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /** Materias cuyas causas son apelaciones (Corte de Apelaciones / Corte Suprema). */
    public const MATERIAS_APELACION = [9, 10];

    /**
     * Parámetros `roles` y `roles_origen` de los cortes de edapi (causas/cortes y estado-diario/cortes).
     * - roles: causas de materia apelación, "libro-rol-era|corte" (máx. 300).
     * - roles_origen: causas de las demás materias (origen de las apelaciones), "rol|tribunal".
     *
     * @return array{roles:string,roles_origen:string}
     */
    public function rolesCortesParaEdapi(int $empresaId): array
    {
        $apelaciones = [];
        foreach ($this->causasActivas($empresaId, true) as $f) {
            if (trim((string) $f['letra']) === '' || trim((string) $f['rol']) === '' || !$f['anio']) {
                continue;
            }
            $clave = trim($f['letra']) . '-' . trim($f['rol']) . '-' . $f['anio'];
            $corte = trim(str_replace(',', ' ', (string) $f['corte']));
            $apelaciones[$clave . ($corte !== '' ? '|' . $corte : '')] = true;
        }

        return [
            'roles' => implode(',', array_slice(array_keys($apelaciones), 0, 300)),
            'roles_origen' => $this->rolesParaEdapi($empresaId, false),
        ];
    }

    /** @return list<array{letra:?string,rol:?string,anio:?int,corte:?string}> */
    private function causasActivas(int $empresaId, bool $apelacion): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.letra, c.rol, c.anio, k.nombre AS corte')
            ->join('c.agenda', 'a')
            ->leftJoin('c.corte', 'k')
            ->andWhere('a.empresa = :empresa')
            ->andWhere('c.estado = 1')
            ->andWhere('IDENTITY(c.materia) ' . ($apelacion ? 'IN' : 'NOT IN') . ' (:apelacion)')
            ->setParameter('empresa', $empresaId)
            ->setParameter('apelacion', self::MATERIAS_APELACION)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Roles de las causas activas del CRM de la empresa, en el formato que espera
     * edapi (parametro roles): "C-1234-2020|1° Juzgado Civil de Santiago,F-5-2024|...".
     * Se omiten las causas sin letra, rol, año o juzgado porque no se pueden cruzar.
     */
    public function rolesParaEdapi(int $empresaId, ?bool $apelaciones = null): string
    {
        $qb = $this->createQueryBuilder('c');
        if ($apelaciones !== null) {
            $qb->andWhere('IDENTITY(c.materia) ' . ($apelaciones ? 'IN' : 'NOT IN') . ' (:apelacion)')
                ->setParameter('apelacion', self::MATERIAS_APELACION);
        }
        $filas = $qb
            ->select('c.letra, c.rol, c.anio, j.nombre AS juzgado')
            ->join('c.agenda', 'a')
            ->join('c.juzgado', 'j')
            ->andWhere('a.empresa = :empresa')
            ->andWhere('c.estado = 1')
            ->andWhere("c.letra <> '' AND c.rol <> '' AND c.anio IS NOT NULL")
            ->setParameter('empresa', $empresaId)
            ->getQuery()
            ->getArrayResult();

        $roles = [];
        foreach ($filas as $f) {
            // Edapi compara el nombre exacto y usa el indicador ordinal "º" (el CRM guarda "°").
            $tribunal = trim(str_replace([',', '°'], [' ', 'º'], $f['juzgado']));
            $roles[strtoupper(trim($f['letra'])) . '-' . trim($f['rol']) . '-' . $f['anio'] . '|' . $tribunal] = true;
        }
        return implode(',', array_keys($roles));
    }

    /** Clave comparable "rol|tribunal" sin acentos, °/º, puntos ni espacios de más. */
    public static function claveRol(string $rol, string $tribunal): string
    {
        $norm = static function (string $t): string {
            $t = strtr(mb_strtolower(trim($t)), ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', '°' => '', 'º' => '', '.' => '']);
            return implode(' ', preg_split('/\s+/', $t) ?: []);
        };
        return $norm($rol) . '|' . $norm($tribunal);
    }

    /**
     * Causas activas de la empresa indexadas por claveRol(), con su materia, para enlazar filas externas
     * (p. ej. Estado Diario) con la Causa del CRM.
     *
     * @return array<string,array{id:int,materia:?string}>
     */
    public function mapaPorRol(int $empresaId): array
    {
        $filas = $this->createQueryBuilder('c')
            ->select('c.id, c.letra, c.rol, c.anio, j.nombre AS juzgado, m.nombre AS materia')
            ->join('c.agenda', 'a')
            ->join('c.juzgado', 'j')
            ->leftJoin('c.materia', 'm')
            ->andWhere('a.empresa = :empresa')
            ->andWhere('c.estado = 1')
            ->andWhere("c.letra <> '' AND c.rol <> '' AND c.anio IS NOT NULL")
            ->setParameter('empresa', $empresaId)
            ->getQuery()
            ->getArrayResult();

        $mapa = [];
        foreach ($filas as $f) {
            $mapa[self::claveRol(strtoupper(trim($f['letra'])) . '-' . trim($f['rol']) . '-' . $f['anio'], $f['juzgado'])] = ['id' => $f['id'], 'materia' => $f['materia']];
        }
        return $mapa;
    }

    // /**
    //  * @return Causa[] Returns an array of Causa objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Causa
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
