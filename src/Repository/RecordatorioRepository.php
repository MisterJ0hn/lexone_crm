<?php

namespace App\Repository;

use App\Entity\Recordatorio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Recordatorio|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recordatorio|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recordatorio[]    findAll()
 * @method Recordatorio[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecordatorioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recordatorio::class);
    }
    /**
    * @return Recordatorio[] Returns an array of Recordatorio objects
    */
    public function findByVencidas(int $usuario,string $fechaVencimiento)
    {
        $query=$this->createQueryBuilder('r');
        $query->andWhere('r.usuarioRegistro = '.$usuario)
        ->andWhere("r.fechaAviso<= '$fechaVencimiento' ");

        $query->andWhere('r.leido = false')
        ->orderBy('r.fechaAviso','Asc');

        return $query->getQuery()
            ->getResult();
    }
    /**
    * @return Recordatorio[] Returns an array of Recordatorio objects
    */
    public function findByVencidasCount(int $usuario,string $fechaVencimiento)
    {
        $query=$this->createQueryBuilder('r')
        ->select(array('count(r.id) as pendientes'));

        $query->andWhere('r.usuarioRegistro = '.$usuario)
        ->andWhere("r.fechaAviso<= '$fechaVencimiento' ");

        $query->andWhere('r.leido = false');

        return $query->getQuery()
        ->getOneOrNullResult();
    }

    public function findByPers(array $criterio,array $orderBy=null, $limit=null,$offset=null, array $joins=null, array $criterio_personal=null, array $or_criterio_personal=null){

        $query=$this->createQueryBuilder('r');
        
        for($i=0;$i<count($joins);$i++){
            $join=$joins[$i];
            foreach ($join as $campo => $valor ) {
                
                $query->join($campo,$valor);
            }
        }
        

        
        foreach($criterio as $index=>$nombre){
          
            $query->andWhere('r.'.$index.'='.$nombre);
        }

        foreach ($criterio_personal as $criterio ) {
            
            $query->andWhere($criterio[0]." ".$criterio[1]." ".$criterio[2]);
        }
        foreach ($or_criterio_personal as $criterio ) {
            
            $query->orWhere($criterio[0]." ".$criterio[1]." ".$criterio[2]);
        }

        if($offset){
            $query->setFirstResult($offset);
        }

        if($limit){
            $query->setMaxResults($limit);
        }
        
        foreach($orderBy as $index=>$nombre){
            $query->addOrderBy('r.'.$index,$nombre);
        }




        return $query->getQuery()
            ->getResult()
        ;
    }
    // /**
    //  * @return Recordatorio[] Returns an array of Recordatorio objects
    //  */
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
    public function findOneBySomeField($value): ?Recordatorio
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
