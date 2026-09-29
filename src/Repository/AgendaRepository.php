<?php

namespace App\Repository;

use App\Entity\Agenda;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Join;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;

/**
 * @method Agenda|null find($id, $lockMode = null, $lockVersion = null)
 * @method Agenda|null findOneBy(array $criteria, array $orderBy = null)
 * @method Agenda[]    findAll()
 * @method Agenda[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgendaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agenda::class);
    }
    /**
      * @return Agenda[] Returns an array of Agenda objects
    */
    public function findByPers($usuario=null,$empresa=null,$compania=null,$status=null, $filtro=null,$esAbogado=null,$otros=null,$tipoFecha=null)
    {
        $query=$this->createQueryBuilder('a');

        if(!is_null($status)){
            $query->andWhere('a.status in ('.$status.')');
        }
        if(!is_null($empresa)){
            $query->andWhere('a.empresa = '.$empresa);
        }
        switch($esAbogado){
            case 1:
                if(!is_null($usuario)){
                    $query->andWhere('a.abogado = '.$usuario);
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
            $query->andWhere("(a.nombreCliente like '%$filtro%' or a.telefonoCliente like '%$filtro%' or a.emailCliente like '%$filtro%')")
         ;

        }

        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        $query->orderBy('a.id','desc');
        //$query->setMaxResults(2000);
        return $query->getQuery()
            ->getResult()
        ;
    }

     /**
      * @return Agenda[] Returns an array of Agenda objects
    */
    public function findByPersSinToku($usuario=null,$empresa=null,$compania=null,$status=null, $filtro=null,$esAbogado=null,$otros=null,$tipoFecha=null)
    {
        $query=$this->createQueryBuilder('a')
        ->join('a.contrato','c')
        ->join('c.cliente','u')
        ->where('u.tokuId is null')
        ->andWhere("c.fechaCreacion>='2022-08-10'");

        if(!is_null($status)){
            $query->andWhere('a.status in ('.$status.')');
        }
        if(!is_null($empresa)){
            $query->join('a.cuenta','c');
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
            $query->andWhere("(a.nombreCliente like '%$filtro%' or a.telefonoCliente like '%$filtro%' or a.emailCliente like '%$filtro%')")
         ;

        }

        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        $query->orderBy('a.id','desc');
        //$query->setMaxResults(2000);
        return $query->getQuery()
            ->getResult()
        ;
    }


    public function findByPersFull($usuario=null,$empresa=null,$compania=null,$status=null, $filtro=null,$esAbogado=null,$otros=null,$tipoFecha=null)
    {
        $query=$this->createQueryBuilder('a');
        if(!is_null($status)){
            $query->andWhere('a.status in ('.$status.')');
        }
        if(!is_null($empresa)){
            $query->join('a.cuenta','c');
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
            $query->andWhere("(a.nombreCliente like '%$filtro%' or a.telefonoCliente like '%$filtro%' or a.emailCliente like '%$filtro%')")
         ;

        }

        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        $query->orderBy('a.id','desc');
        //$query->setMaxResults(2000);
        return $query->getQuery()
            ->getResult()
        ;
    }
    
    
    /**
    * @return Agenda[] Retorna un array de Agenda objects sin contrato creado
    */
    public function findByPersSinContr($usuario=null,$empresa=null,$compania=null,$status=null, $filtro=null,$esAbogado=null,$otros=null,$tipoFecha=null)
    {
        $query=$this->createQueryBuilder('a')
        ->leftJoin('a.contratos', 'i')
        ->andWhere('i.id is null');

        if(!is_null($status)){
            $query->andWhere('a.status in ('.$status.')');
        }
        if(!is_null($empresa)){
            $query->join('a.cuenta','c');
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
            $query->andWhere("(a.nombreCliente like '%$filtro%' or a.telefonoCliente like '%$filtro%' or a.emailCliente like '%$filtro%')")
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


    public function findByPersGroup($usuario=null,$empresa=null,$compania=null,$status=null, $filtro=null,$esAbogado=null, $otros=null)
    {
        $query=$this->createQueryBuilder('a');
        $query->select(array('a','s','count(s.id) as valor'));
        $query->join('a.status','s');
        if(!is_null($status)){
            $query->andWhere('s.id in ('.$status.')');
        }
        if(!is_null($empresa)){
            $query->join('a.cuenta','c');
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
            $query->andWhere("(a.nombreCliente like '%$filtro%' or a.telefonoCliente like '%$filtro%' or a.emailCliente like '%$filtro%')")
         ;

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
    
    //Grafico Agenda-Cierre
    public function findByPersGroupPeriodo($usuario=null,$empresa=null,$compania=null,$status=null, $filtro=null,$esAbogado=null, $otros=null, $TipoFecha=null,bool $filtroCuenta=false)
    {
        $query=$this->createQueryBuilder('a');
        $query->select(array('count(s.id) as valor','month('.$TipoFecha.') as mes'));
        $query->join('a.status','s');
        if(!is_null($status)){
            $query->andWhere('s.id in ('.$status.')');
        }
        if(!is_null($empresa)){
            //$query->join('a.cuenta','c');
            $query->andWhere('a.empresa = '.$empresa);
        }

        if(!is_null($filtroCuenta)){
            if(!is_null($usuario)){
            $query->join('a.cuenta','c')
            ->join('c.usuarioCuentas','uc')
            ->andWhere('uc.usuario='.$usuario);
            }

        }else{
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
        }
        

        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        if(!is_null($filtro)){ 
            $query->andWhere("(a.nombreCliente like '%$filtro%' or a.telefonoCliente like '%$filtro%' or a.emailCliente like '%$filtro%')")
         ;

        }
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        $query->orderBy($TipoFecha,'asc');
        $query->addGroupBy('mes');

        return $query->getQuery()
            ->getResult()
        ;

    }

    public function findByPersReporte($usuario=null,$empresa=null,$compania=null,$status=null, $filtro=null,$esAbogado=null, $otros=null)
    {
        $query=$this->createQueryBuilder('a');
        $query->select(array('a','s','count(s.id) as valor'));
        $query->join('a.status','s');
        $query->join('a.status','s2');
        $query->andWhere('s.id = 7 and s2.id=5 ');
        
        if(!is_null($empresa)){
            $query->join('a.cuenta','c');
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
            $query->andWhere("(a.nombreCliente like '%$filtro%' or a.telefonoCliente like '%$filtro%' or a.emailCliente like '%$filtro%')")
         ;

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
    public function findByAgendGroup($usuario=null,$empresa=null,$compania=null,$status=null, $filtro=null,$esAbogado=null, $otros=null)
    {


        $query=$this->createQueryBuilder('a');
        $query->select(array('a','u','count(u.id) as valor'));
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
            $query->andWhere("(a.nombreCliente like '%$filtro%' or a.telefonoCliente like '%$filtro%' or a.emailCliente like '%$filtro%')")
         ;

        }
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        $query->addGroupBy('u.id');

        return $query->getQuery()
            ->getResult()
        ;

    }

    public function findByAgendReporte($usuario=null,$empresa=null,$compania=null,$status=null, $filtro=null,$esAbogado=null, $otros=null)
    {


        $query=$this->createQueryBuilder('a');
        if($status == '7' or $status == '14' or $status == '7,14' ){
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
            $query->andWhere("(u.nombre like '%$filtro%')")
         ;

        }
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        $query->addGroupBy('u.id');

        return $query->getQuery()
            ->getResult();

    }

    public function findByCampaniaReporte($usuario=null,$empresa=null,$compania=null,$status=null, $filtro=null,$esAbogado=null, $otros=null, $campania=null)
    {


        $query=$this->createQueryBuilder('a');
        if($status == '7'){
            $query->select(array('a','count(a.id) as valor','sum(con.MontoContrato) as monto'));
            $query->join('a.contrato','con');
        }else{
            $query->select(array('a','count(a.id) as valor'));
        }
       

        if(!is_null($status)){
            if($status == '5'){
                $query->andWhere('a.fechaAsignado is not null');
            }else{
                $query->andWhere('a.status in ('.$status.')');
            }
            
        }
        if(!is_null($empresa)){
            $query->join('a.cuenta','c');
            $query->andWhere('a.empresa = '.$empresa);
        }
        
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }

        if(!is_null($filtro)){
            $query->andWhere("a.campania like '%".$filtro."%'");
        }
       
        if(!is_null($campania)){
            $query->andWhere("a.campania like '".$campania."'");
        }
        
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        $query->addGroupBy('a.campania');

        return $query->getQuery()
            ->getResult();

    }

    public function findByAgendReporteMontoContrato($usuario=null,$empresa=null,$compania=null,$status=null, $filtro=null,$esAbogado=null, $otros=null)
    {


        $query=$this->createQueryBuilder('a');
        $query->select(array('a','sum(con.MontoContrato) as monto'));

        if($esAbogado==1){
            $query->join('a.abogado','u');
        }else{
            $query->join('a.agendador','u');
        }

        $query->join('a.contrato','con');

        if(!is_null($status)){
            $query->andWhere('a.status in ('.$status.')');
        }
        if(!is_null($empresa)){
            $query->join('a.cuenta','c');
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
            $query->andWhere("(a.nombreCliente like '%$filtro%' or a.telefonoCliente like '%$filtro%' or a.emailCliente like '%$filtro%')")
         ;

        }
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        $query->addGroupBy('u.id');

        return $query->getQuery()
            ->getResult();

    }

    public function findByContratoReporte($usuario=null,$empresa=null,$compania=null,$status=null, $filtro=null,$esAbogado=null, $otros=null)
    {


        $query=$this->createQueryBuilder('a');
        $query->select(array('a','count(con.id) as valor','sum(con.MontoContrato) as monto'));

        
        $query->join('a.contrato','con');

        if(!is_null($status)){
            $query->andWhere('a.status in ('.$status.')');
        }
        if(!is_null($empresa)){
            $query->join('a.cuenta','c');
            $query->andWhere('a.empresa = '.$empresa);
        }
        

        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        if(!is_null($filtro)){ 
            $query->andWhere("(a.nombreCliente like '%$filtro%' or a.telefonoCliente like '%$filtro%' or a.emailCliente like '%$filtro%')")
         ;

        }
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

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
        $query->addGroupBy('a.cuenta');

        return $query->getQuery()
            ->getResult();

    }

    public function findByContrato2Reporte($usuario=null,$empresa=null,$compania=null,$status=null, $filtro=null,$esAbogado=null, $otros=null)
    {


        $query=$this->createQueryBuilder('a');
        
        $query->join('a.contrato','con');

        if(!is_null($status)){
            $query->andWhere('a.status in ('.$status.')');
        }
        if(!is_null($empresa)){
            $query->join('a.cuenta','c');
            $query->andWhere('a.empresa = '.$empresa);
        }
        

        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        if(!is_null($filtro)){ 
            $query->andWhere("(a.nombreCliente like '%$filtro%' or a.telefonoCliente like '%$filtro%' or a.emailCliente like '%$filtro%')")
         ;

        }
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
      
        return $query->getQuery()
            ->getResult();

    }

    public function findByContactoReporte($usuario=null,$empresa=null,$compania=null,$status=null, $filtro=null,$esAbogado=null, $otros=null, $contacto=null)
    {


        $query=$this->createQueryBuilder('a')
        ;
        if($status == '7'){
            $query->select(array('a','count(a.id) as valor','sum(con.MontoContrato) as monto'));
            $query->join('a.contrato','con');
        }else{
            $query->select(array('a','count(a.id) as valor'));
        }
        $query->where('a.agendaContacto is not null');

    
        if(!is_null($status)){
            if($status == '5'){
                $query->andWhere('a.fechaAsignado is not null');
            }else{
                $query->andWhere('a.status in ('.$status.')');
            }
            
        }
        if(!is_null($empresa)){
            $query->join('a.cuenta','c');
            $query->andWhere('a.empresa = '.$empresa);
        }
        
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }

        if(!is_null($filtro)){
            $query->andWhere("a.campania like '%".$filtro."%'");
        }
       
        if(!is_null($contacto)){
            $query->andWhere("a.agendaContacto = ".$contacto->getId()."");
        }
        
        if(!is_null($otros)){ 
            $query->andWhere($otros)
         ;

        }
        $query->addGroupBy('a.agendaContacto');

        return $query->getQuery()
            ->getResult();

    }
    // /**
    //  * @return Agenda[] Returns an array of Agenda objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Agenda
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
