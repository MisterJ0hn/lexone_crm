<?php

namespace App\Repository;

use App\Entity\Cuenta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Cuenta|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cuenta|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cuenta[]    findAll()
 * @method Cuenta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CuentaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cuenta::class);
    }

    public function findByPers($usuario=null, $empresa=null)
    {
        $query=$this->createQueryBuilder('c');
        if(null !== $usuario){
            $query->join('c.usuarioCuentas','uc')
            ->andWhere('uc.usuario = :val')
            ->setParameter('val', $usuario);
        }

        if(null !== $empresa){
            $query->andWhere('c.empresa = :val2')
                ->setParameter('val2',$empresa);
        }
        
        
        return $query->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Cuenta[] Returns an array of Cuenta objects
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
    public function findOneBySomeField($value): ?Cuenta
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
