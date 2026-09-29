<?php

namespace App\Repository;

use App\Entity\PrivilegioTipousuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PrivilegioTipousuario|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrivilegioTipousuario|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrivilegioTipousuario[]    findAll()
 * @method PrivilegioTipousuario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrivilegioTipousuarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrivilegioTipousuario::class);
    }

    public function findByEmpresa($empresa_id,$tipoUsuario_id=null){
        $query=$this->createQueryBuilder('p')
        ->join('p.moduloPer','m')
        ->join('m.empresa','e')
        ->join('p.tipousuario','t')
        ->andWhere('e.id='.$empresa_id);

        if($tipoUsuario_id!=null){
            $query->andWhere('t.id='.$tipoUsuario_id);
        }

        return $query->getQuery()
        ->getResult();

    }

    // /**
    //  * @return PrivilegioTipousuario[] Returns an array of PrivilegioTipousuario objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PrivilegioTipousuario
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
