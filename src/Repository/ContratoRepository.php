<?php

namespace App\Repository;

use App\Entity\Contrato;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Contrato|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contrato|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contrato[]    findAll()
 * @method Contrato[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContratoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contrato::class);
    }

    public function findRange($inicio, $fin)
    {
        $query=$this->createQueryBuilder('c');
        $query->where("c.id between  ".$inicio. " and ".$fin);
        
        return $query->getQuery()
        ->getResult();

    }
    public function findLoteMax($empresa=null, $orderby=null): ?Contrato
    {
        $query=$this->createQueryBuilder('c')
        ->join('c.agenda','a')
        ->join('a.cuenta','cu');
        if(!is_null($empresa)){
            
            $query->andWhere('a.empresa = '.$empresa);
        }

        $query->setMaxResults(1);
        if($orderby){
            $query->orderBy($orderby, 'DESC');
        }else{
            $query->orderBy('c.id', 'DESC');
        }

        return $query
        ->getQuery()
        ->getOneOrNullResult();

    }
    
    public function findByPers($usuario=null,$empresa=null,$compania=null,$filtro=null,$agendador=null, $otros=null, $deuda = false)
    {
        $query=$this->createQueryBuilder('c');
        $query->join('c.agenda','a');
        $query->join('a.cuenta','cu');
        $query->andWhere('(DATEDIFF(now(), c.fechaPrimerPago)/30)<c.vigencia');
        if(!is_null($empresa)){
            
            $query->andWhere('a.empresa = '.$empresa);
        }
        if(!is_null($usuario)){
            $query->andWhere('a.abogado = '.$usuario);
        }
        if(!is_null($agendador)){
            
            $query->andWhere('a.agendador = '.$agendador);
        }
        if(!is_null($filtro)){ 
            $query->leftjoin('c.cliente','cli');
            $query->andWhere("(cli.nombre like '%$filtro%' or cli.correo like '%$filtro%' or cli.telefono like '%$filtro%')")
         ;

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        
        return $query
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByPersGroup($usuario=null,$empresa=null,$compania=null,$status=null, $filtro=null,$esAbogado=null, $otros=null)
    {
        $query=$this->createQueryBuilder('c');
        $query->select(array('c','a','s','count(s.id) as valor'));
        $query->join('c.agenda','a');
        $query->join('a.status','s');
        $query->andWhere('(DATEDIFF(now(), c.fechaPrimerPago)/30)<c.vigencia');
        if(!is_null($status)){
            $query->andWhere('s.id in ('.$status.')');
        }
        if(!is_null($empresa)){
            $query->join('a.cuenta','cu');
            $query->andWhere('a.empresa = '.$empresa);
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
                }else{
                    $query->andWhere('a.agendador is not null ');
                }
                //$query->andWhere('(a.abogado is null or a.status in (4,6,7,8))');
            break;
            default:
                if(!is_null($usuario)){
                    $query->andWhere('a.agendador = '.$usuario);
                }
            break;
            
        }

        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        if(!is_null($filtro)){ 
            $query->leftjoin('c.cliente','cli');
            $query->andWhere("(cli.nombre like '%$filtro%' or cli.correo like '%$filtro%' or cli.telefono like '%$filtro%')")         ;

        }
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        $query->addGroupBy('s.id');

        return $query->getQuery()
            ->getResult()
        ;

    }
    /**
      * @return Contrato[] Retorna un array de Agenda objects sin contrato creado
    */
    public function findByPersSinContr($usuario=null,$empresa=null,$compania=null,$status=null, $filtro=null,$esAbogado=null,$otros=null,$tipoFecha=null)
    {
        $query=$this->createQueryBuilder('c')
        ->rightJoin('c.agenda', 'a')
        ->andWhere('c.id is null');
        


        /*->join('c.agenda', 'a')
        ;*/

        if(!is_null($status)){
            $query->andWhere('a.status in ('.$status.')');
        }
        if(!is_null($empresa)){
            $query->join('a.cuenta','i');
            $query->andWhere('a.empresa = '.$empresa);
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
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        if(!is_null($filtro)){ 
            $query->leftjoin('a.cliente','cli');
            $query->andWhere("(cli.nombre like '%$filtro%' or cli.correo like '%$filtro%' or cli.telefono like '%$filtro%')")
         ;

        }

        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
    
        return $query->getQuery()
            ->getResult()
        ;
    }

    public function findByPersDeuda($usuario=null,$empresa=null,$compania=null,$filtro=null,$agendador=null, $otros=null)
    {
        $query=$this->createQueryBuilder('c');
        $query->join('c.agenda','a');
        $query->join('a.cuenta','cu');
        if(!is_null($empresa)){
            
            $query->andWhere('a.empresa = '.$empresa);
        }
        if(!is_null($usuario)){
            $query->andWhere('a.abogado = '.$usuario);
        }
        if(!is_null($agendador)){
            
            $query->andWhere('a.agendador = '.$agendador);
        }
        if(!is_null($filtro)){ 
            $query->leftjoin('c.cliente','cli');
            $query->andWhere("(cli.nombre like '%$filtro%' or cli.correo like '%$filtro%' or cli.telefono like '%$filtro%')")
         ;

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        return $query
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByCerradores(int $usuario = null, int $empresa = null, $compania=null,$otros=null, $array=null ){
        
        $query=$this->createQueryBuilder('c')
        ->select(array('c','cuo.monto as monto_cuota','sum(pc.monto) as monto_pagado','cuo.numero as cuota_numero','cuo.fechaPago as fecha_vencimiento', 'p.fechaPago as fecha_pago',"dateadd(cuo.fechaPago,30,'DAY') as dia_vencimiento",'datediff(p.fechaPago, cuo.fechaPago) as q_dias' ))
        ->join('c.agenda','a')
        ->join('a.cuenta','cu')
        ->join('c.detalleCuotas','cuo')
        ->join('cuo.pagoCuotas','pc')
        ->join('pc.pago','p');
        
        if($otros != null){
            $query->andWhere($otros);
        }
        if($usuario != null){
            $query->andWhere("a.abodado=$usuario");
        }
        if($array!=null){
            
            $query->orderBy($array['sort'],$array['direction']);

        }
        

        //$query->addGroupBy('p.id');
        $query->addGroupBy('cuo.id');
        return $query
            ->getQuery()
            ->getResult()
        ;
    }
    public function findByCerradoresGroup(int $usuario = null, int $empresa = null, $compania=null,$otros=null, $array=null ){
        
        $query=$this->createQueryBuilder('c')
        ->select(array('c','count(c) as cantidad','sum(pc.monto) as monto_pagado ','cuo.numero as cuota_numero','cuo.fechaPago as fecha_vencimiento', 'p.fechaPago as fecha_pago',"dateadd(cuo.fechaPago,30,'DAY') as dia_vencimiento",'datediff(p.fechaPago, cuo.fechaPago) as q_dias' ))
        ->join('c.agenda','a')
        ->join('a.cuenta','cu')
        ->join('c.detalleCuotas','cuo')
        ->join('cuo.pagoCuotas','pc')
        ->join('pc.pago','p');
        
        if($otros != null){
            $query->andWhere($otros);
        }
        if($usuario != null){
            $query->andWhere("a.abodado=$usuario");
        }
        if($array!=null){
            
            $query->orderBy($array['sort'],$array['direction']);

        }
        $query->groupBy('a.abogado');
        return $query
            
            ->getQuery()
            ->getResult()
        ;
    }
    public function findByCerradoresResumen(int $usuario = null, int $empresa = null, $compania=null,$otros=null, $array=null ){
        
        $query=$this->createQueryBuilder('c')
        ->select(array('c','count(DISTINCT c) as cantidad','sum(pc.monto) as monto_pagado ','cuo.numero as cuota_numero','cuo.fechaPago as fecha_vencimiento', 'p.fechaPago as fecha_pago',"dateadd(cuo.fechaPago,30,'DAY') as dia_vencimiento",'datediff(p.fechaPago, cuo.fechaPago) as q_dias' ))
        ->join('c.agenda','a')
        ->join('a.cuenta','cu')
        ->join('c.detalleCuotas','cuo')
        ->join('cuo.pagoCuotas','pc')
        ->join('pc.pago','p');
        

        if($otros != null){
            $query->andWhere($otros);
        }
        if($usuario != null){
            $query->andWhere("a.abodado=$usuario");
        }
        if($array!=null){
            
            $query->orderBy($array['sort'],$array['direction']);

        }

        
        return $query
            
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    public function findByCerradoresResumenCant(int $usuario = null, int $empresa = null, $compania=null,$otros=null, $array=null ){
        
        $query=$this->createQueryBuilder('c')
        ->select(array('c','count(c) as cantidad','sum(pc.monto) as monto_pagado ','cuo.numero as cuota_numero','cuo.fechaPago as fecha_vencimiento', 'p.fechaPago as fecha_pago',"dateadd(cuo.fechaPago,30,'DAY') as dia_vencimiento",'datediff(p.fechaPago, cuo.fechaPago) as q_dias' ))
        ->join('c.agenda','a')
        ->join('a.cuenta','cu')
        ->join('c.detalleCuotas','cuo')
        ->join('cuo.pagoCuotas','pc')
        ->join('pc.pago','p');
        
        if($otros != null){
            $query->andWhere($otros);
        }
        if($usuario != null){
            $query->andWhere("a.abodado=$usuario");
        }
        if($array!=null){
            
            $query->orderBy($array['sort'],$array['direction']);

        }
        
        return $query
            
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    public function findNombreRut($texto){
        $query=$this->createQueryBuilder('c')
        ->where("c.email like '%".$texto."%' or c.nombre like '%".$texto."%' or c.rut like '%".$texto."%' ");

        return $query
            ->orderBy('c.fechaCreacion','Desc')
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Contrato[] Returns an array of Contrato objects
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
    public function findOneBySomeField($value): ?Contrato
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
