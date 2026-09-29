<?php

namespace App\Repository;

use App\Entity\UsuarioNoDisponible;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UsuarioNoDisponible|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsuarioNoDisponible|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsuarioNoDisponible[]    findAll()
 * @method UsuarioNoDisponible[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsuarioNoDisponibleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsuarioNoDisponible::class);
    }

    public function getHoras(){
        $horario_inicio=explode(":",'00:00');
        $horario_fin=explode(":",'23:59');
    
        for($i=intval($horario_inicio[0]);$i<=intval($horario_fin[0]);$i++){
            if($i==intval($horario_inicio[0]) && $horario_inicio[1]=="30"){
                $horario[]="$i:30";
                continue;
            }
            if($i==intval($horario_fin[0]) && intval($horario_fin[1])==00){
                $horario[]="$i:00";
                continue;
            }
            $horario[]="$i:00";
            $horario[]="$i:30";
            
        }
        return $horario;
    }

    public function findByIntervalo($usuario,$fecha){
        $query=$this->createQueryBuilder('u')
        ->andWhere("u.fechaInicio <= '$fecha 00:00:00' and u.fechaFin >= '$fecha 23:59:00' ")
        ->andWhere("u.usuario = $usuario");

        return $query->getQuery()
                ->getResult();


    }
    public function findByDinamico($usuario,$fecha){
        $query=$this->createQueryBuilder('u')
            ->andWhere(" u.fechaInicio is null and u.fechaFin is null ")
            ->andWhere(" (u.anio = :vAnio or u.anio=0)")
            ->setParameter('vAnio', date("Y",strtotime($fecha)))
            ->andWhere(" (u.mes = :vMes or u.mes=0)")
            ->setParameter('vMes', date("m",strtotime($fecha)))
            ->andWhere(" (u.dia = :vDia or u.dia=0)")
            ->setParameter('vDia', date("d",strtotime($fecha)))
            ->andWhere("u.usuario = $usuario")
            ;

        return $query->getQuery()->getResult();

    }
    // /**
    //  * @return UsuarioNoDisponible[] Returns an array of UsuarioNoDisponible objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UsuarioNoDisponible
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
