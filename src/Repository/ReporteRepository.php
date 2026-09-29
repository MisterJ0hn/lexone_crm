<?php

namespace App\Repository;

use App\Entity\AgendaObservacion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method AgendaObservacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgendaObservacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgendaObservacion[]    findAll()
 * @method AgendaObservacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReporteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgendaObservacion::class);
    }
    /**
      * @return AgendaObservacion[] Returns an array of Reporte objects
    */

    public function findByGestionReporte($usuario=null,$empresa=null,$perfil=null,$status=null,$empleado=null,$esAbogado=null, $otros=null)
    {
        $query=$this->createQueryBuilder('a')
        ->join('a.usuarioRegistro','u')
        ->join('u.usuarioTipo','ut');

        if(null!==$perfil){
            $query->andWhere('ut.id='.$perfil);
        }

        if(null !== $usuario){
            $query->andWhere('u.id='.$usuario);
        }

        if(null !== $otros){
            $query->andWhere($otros);
        }


        /**if($status == '7' or $status == '14' or $status == '7,14' ){
            $query->select(array('a','u','count(u.id) as valor','sum(con.MontoContrato) as monto'));
            $query->join('a.contrato','con');
        }else{
            $query->select(array('a','u','count(u.id) as valor'));
        }
        

        if($esAbogado==1){
            $query->join('a.abogado','u');
        }else{
            $query->join('a.agendador','u');
        }
        
    
        if(!is_null($status)){
                $query->andWhere('a.status in ('.$status.')');

        }
        if(!is_null($empresa)){
            $query->join('a.cuenta','c');
            $query->andWhere('c.empresa = '.$empresa);
        }
        switch($esAbogado){
            case 1:
                if(!is_null($usuario)){
                    $query->andWhere('a.abogado = '.$usuario);
                }else{
                    $query->andWhere('a.abogado is not null ');
                }
            break;
            case 0:
                if(!is_null($usuario)){
                    $query->andWhere('a.agendador = '.$usuario);
                }
                //$query->andWhere('(a.abogado is null or a.status in (4,6,7,8))');
            break;
            default:
                if(!is_null($usuario)){
                    $query->andWhere('a.agendador = '.$usuario);
                }
            break;

        }
        
        if(!is_null($perfil)){
            $query->andWhere('a.cuenta = '.$perfil);
        }
        if(!is_null($filtro)){ 
            $query->andWhere("(u.nombre like '%$filtro%')")
         ;

        }
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        $query->addGroupBy('u.id');*/

        return $query->getQuery()
            ->getResult();

    }

    /**
      * @return Reportes[] Returns an array of Reportes objects
    */

    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Reportes
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
