<?php

namespace App\Repository;

use App\Entity\Empresa;
use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Empresa|null find($id, $lockMode = null, $lockVersion = null)
 * @method Empresa|null findOneBy(array $criteria, array $orderBy = null)
 * @method Empresa[]    findAll()
 * @method Empresa[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmpresaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Empresa::class);
    }

    /**
     * Empresas que el usuario puede operar. El super-admin (usuarioTipo=8) puede
     * operar todas; el resto de usuarios sólo su empresa de membresía directa
     * (Usuario::empresa).
     *
     * @return Empresa[]
     */
    public function findDisponiblesParaUsuario(Usuario $usuario): array
    {
        if ($this->esSuperAdmin($usuario)) {
            return $this->createQueryBuilder('e')
                ->orderBy('e.nombre', 'ASC')
                ->getQuery()
                ->getResult();
        }

        return $usuario->getEmpresa() !== null ? [$usuario->getEmpresa()] : [];
    }

    /**
     * Si el usuario puede operar la empresa dada.
     */
    public function esMiembro(Usuario $usuario, int $empresaId): bool
    {
        if ($this->esSuperAdmin($usuario)) {
            return $this->find($empresaId) !== null;
        }

        return $usuario->getEmpresa() !== null && $usuario->getEmpresa()->getId() === $empresaId;
    }

    private function esSuperAdmin(Usuario $usuario): bool
    {
        return $usuario->getUsuarioTipo() !== null && $usuario->getUsuarioTipo()->getId() === 8;
    }

    public function findByBusqueda($json_criterio,array $orderBy = null, $limit = null)
    {
        $query=$this->createQueryBuilder('e');

        $array_criterio=json_decode($json_criterio,true);
        
        foreach($array_criterio as $campo => $valor ){
            if(trim($valor)!=''){
                $query->andWhere("e.".$campo."'$valor'");
            }
        }
        if(!is_null($orderBy)){
            foreach($orderBy as $campo => $valor ){
                if(trim($valor)!='')
                    $query->orderBy($campo,$valor);
            }
        }
        return $query
            ->getQuery()
            ->getResult()
        ;

    }
    // /**
    //  * @return Empresa[] Returns an array of Empresa objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Empresa
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
