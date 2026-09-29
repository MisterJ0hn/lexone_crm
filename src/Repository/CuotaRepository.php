<?php

namespace App\Repository;

use App\Entity\Cuota;
use App\Security\Cifrado;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Cuota|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cuota|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cuota[]    findAll()
 * @method Cuota[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CuotaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cuota::class);
    }
    public function findVencimiento($usuario=null,$empresa=null,$compania=null,$filtro=null,$tipoUsuario=null,$vigente=true, $otros=null,$conrestriccion=true,$esCobranza=false){
        $query=$this->createQueryBuilder('c');
        $query->join('c.contrato','co');
        $query->join('co.agenda','a');
        $query->join('a.cuenta','cu');
        $query->leftJoin('co.cliente','cli');

        if($conrestriccion==true){
            if($vigente){
                if($esCobranza){
                    $query->andWhere('c.monto>c.pagado or c.pagado is null');
                }else{
                    $query->andWhere('c.monto>=c.pagado or c.pagado is null');
                }
                
                $query->andWhere('c.anular is null or c.anular = false');
                $query->andWhere(' co.isFinalizado = false or co.isFinalizado is null'); 

            }else{
                $query->andWhere(' co.isFinalizado=true');
            }
        }
        

        if(!is_null($empresa)){
            
            $query->andWhere('a.empresa = '.$empresa);
        }
        switch($tipoUsuario){
            case 6://Abogado
                if(!is_null($usuario)){
                    $query->andWhere('a.abogado = '.$usuario)
                    ->andWhere("DATEDIFF(now(),c.fechaPago)<=30")
                    ->andWhere("c.numero=1");

                }
                break;
            case 7://Tramitador
                if(!is_null($usuario))
                    $query->andWhere('co.tramitador = '.$usuario);
                break;
        }
        
        
        if(!is_null($filtro)){ 
          
          $query->andWhere('(cli.nombre LIKE :filtro OR cli.rutHash = :filtroRutHash OR cli.correoHash = :filtroCorreoHash)')
                ->setParameter('filtro', '%' . $filtro . '%')
                ->setParameter('filtroRutHash', Cifrado::hash($filtro, 'rut'))
                ->setParameter('filtroCorreoHash', Cifrado::hash($filtro, 'correo'))
         ;

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        
        if(!is_null($otros) && $otros!=''){ 
            $query->andWhere($otros)
         ;

        }
        $query->orderBy("co.fechaCreacion","Desc");
        $query->groupBy('c.contrato');
        
        return $query->getQuery()
            ->getResult()
        ;
    }

    public function findPagado($usuario=null,$empresa=null,$compania=null,$filtro=null,$tipoUsuario=null,$vigente=true, $otros=null,$conrestriccion=true,$esCobranza=false){
        $query=$this->createQueryBuilder('c')
        ->join('c.contrato','co')
        ->join('co.agenda','a')
        ->join('a.cuenta','cu')
        ->join('c.pagoCuotas','pc')
        ->join('pc.pago','p');
         $query->leftJoin('co.cliente','cli');
        if($conrestriccion==true){
            if($vigente){
                if($esCobranza){
                    $query->andWhere('c.monto>c.pagado or c.pagado is null');
                }else{
                    $query->andWhere('c.monto>=c.pagado or c.pagado is null');
                }
                
                $query->andWhere('c.anular is null or c.anular = false');
                $query->andWhere(' co.isFinalizado = false or co.isFinalizado is null'); 

            }else{
                $query->andWhere(' co.isFinalizado=true');
            }
        }
        

        if(!is_null($empresa)){
            
            $query->andWhere('cu.empresa = '.$empresa);
        }
        switch($tipoUsuario){
            case 6://Abogado
                if(!is_null($usuario)){
                    $query->andWhere('a.abogado = '.$usuario)
                    ->andWhere("DATEDIFF(now(),c.fechaPago)<=30")
                    ->andWhere("c.numero=1");

                }
                break;
            case 7://Tramitador
                if(!is_null($usuario))
                    $query->andWhere('co.tramitador = '.$usuario);
                break;
        }
        
        
        if(!is_null($filtro)){ 
         $query->andWhere('(cli.nombre LIKE :filtro OR cli.rutHash = :filtroRutHash) ')
                ->setParameter('filtro', '%' . $filtro . '%')
                ->setParameter('filtroRutHash', Cifrado::hash($filtro, 'rut'))
         ;


        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        
        if(!is_null($otros) && $otros!=''){ 
            $query->andWhere($otros)
         ;

        }
        $query->orderBy("co.fechaCreacion","Desc");
        $query->groupBy('c.contrato');
        
        return $query->getQuery()
            ->getResult()
        ;
    }
    public function findOneByPrimeraVigente($contrato,$isMulta=false): ?Cuota
    {
        $query=$this->createQueryBuilder('c')
        ->andWhere('c.contrato=:contra')
        ->setParameter('contra', $contrato)
        ->andWhere('c.monto>c.pagado or c.pagado is null')
        ;
        $query->andWhere('c.anular is null or c.anular = false');
       if($isMulta){
            $query->andWhere('c.isMulta = true');
        }
        $query
        ->setMaxResults(1);
        return $query->getQuery()
            ->getOneOrNullResult()
        ;
    }
    public function findOneByUltimaPagada($contrato): ?Cuota
    {
        $query=$this->createQueryBuilder('c')
        ->andWhere('c.contrato=:contra')
        ->andWhere('c.pagado>0')
        ->setParameter('contra', $contrato)
        ->orderBy('c.numero', 'Desc')
        ->setMaxResults(1);
        return $query->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function deudaTotal($contrato,$otros=false)
    {

        $query=$this->createQueryBuilder('c')
        ->join("c.contrato","co")
        ->select(array('sum(c.monto)','sum(c.pagado)'))
        ->andWhere('c.contrato=:contra');
        if($otros){
            $query->andWhere($otros);
        }
        $query->andWhere('c.monto>c.pagado or c.pagado is null')
        ->andWhere('c.anular is null or c.anular = false')
        ->setParameter('contra', $contrato)
        ->groupBy('c.contrato');
        return $query->getQuery()
            ->getResult()
        ;
    }
    //Retorna totales de cobranza agrupados por usuario cobrador
    public function totalesUsuario()
    {
        $query=$this->createQueryBuilder('c');
        $query->select(array('c','count(i.id)','sum(p.monto)'))
        ->join('c.contrato','co')
        ->join('co.agenda','a')
        ->join('a.cuenta','cu')
        ->join('c.pagoCuotas','pc')
        ->join('pc.pago','p');
        $query->leftJoin('co.cliente','cli');
        
        if($conrestriccion==true){
            if($vigente){
                if($esCobranza){
                    $query->andWhere('c.monto>c.pagado or c.pagado is null');
                }else{
                    $query->andWhere('c.monto>=c.pagado or c.pagado is null');
                }
                
                $query->andWhere('c.anular is null or c.anular = false');
                $query->andWhere(' co.isFinalizado = false or co.isFinalizado is null'); 

            }else{
                $query->andWhere(' co.isFinalizado=true');
            }
        }
        

        if(!is_null($empresa)){
            
            $query->andWhere('cu.empresa = '.$empresa);
        }
        switch($tipoUsuario){
            case 6://Abogado
                if(!is_null($usuario)){
                    $query->andWhere('a.abogado = '.$usuario)
                    ->andWhere("DATEDIFF(now(),c.fechaPago)<=30")
                    ->andWhere("c.numero=1");

                }
                break;
            case 7://Tramitador
                if(!is_null($usuario))
                    $query->andWhere('co.tramitador = '.$usuario);
                break;
        }
        
        
        if(!is_null($filtro)){ 

            $query->andWhere('(cli.nombre LIKE :filtro OR cli.rutHash = :filtroRutHash )')
                ->setParameter('filtro', '%' . $filtro . '%')
                ->setParameter('filtroRutHash', Cifrado::hash($filtro, 'rut'))

         ;

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        
        if(!is_null($otros) && $otros!=''){ 
            $query->andWhere($otros)
         ;

        }
        $query->orderBy("co.fechaCreacion","Desc");
        $query->groupBy('c.contrato');
        
        return $query->getQuery()
            ->getResult()
        ;
    }
    public function findByPersGroup($usuario=null,$empresa=null,$compania=null,$status=null, $filtro=null,$esAbogado=null, $otros=null)
    {
        $query=$this->createQueryBuilder('c');
        $query->select(array('a','s','count(s.id) as valor'));
        $query->join('a.status','s');
        if(!is_null($status)){
            $query->andWhere('s.id in ('.$status.')');
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
    public function findCuotasTotales($idContrato): ?Cuota
    {
        return $this->createQueryBuilder('c')
 
            ->andWhere('c.contrato = :val')
            ->setParameter('val', $idContrato)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

    }
    public function findUltimaPagada($idContrato): ?Cuota
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.pagado=c.monto')
            ->andWhere('c.contrato = :val')
            ->setParameter('val', $idContrato)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

    }

    public function findNCuotas($idContrato): ?Cuota
    {
        $query=$this->createQueryBuilder('c');
        $query->select(array('count(c.id) as valor'));
        $query->where('c.');
        return $query->getQuery()
        ->getResult();

    }
    public function findVencimientoGroup($usuario=null,$empresa=null,$compania=null,$filtro=null,$tipoUsuario=null,$vigente=true, $otros=null,$conrestriccion=true,$esCobranza=false){
        $query=$this->createQueryBuilder('c');

        $query->select(array('c','count(distinct c.id)','sum(c.monto)'));
        $query->join('c.contrato','co');
        $query->join('co.agenda','a');
        $query->join('a.cuenta','cu');
        $query->leftJoin('co.cliente','cli');
        if($conrestriccion==true){
            if($vigente){
                if($esCobranza){
                    $query->andWhere('c.monto>c.pagado or c.pagado is null');
                }else{
                    $query->andWhere('c.monto>=c.pagado or c.pagado is null');
                }
                
                $query->andWhere('c.anular is null or c.anular = false');
                $query->andWhere(' co.isFinalizado = false or co.isFinalizado is null'); 

            }else{
                $query->andWhere(' co.isFinalizado=true');
            }
        }
        

        if(!is_null($empresa)){
            
            $query->andWhere('cu.empresa = '.$empresa);
        }
        switch($tipoUsuario){
            case 6://Abogado
                if(!is_null($usuario)){
                    $query->andWhere('a.abogado = '.$usuario)
                    ->andWhere("DATEDIFF(now(),c.fechaPago)<=30")
                    ->andWhere("c.numero=1");

                }
                break;
            case 7://Tramitador
                if(!is_null($usuario))
                    $query->andWhere('co.tramitador = '.$usuario);
                break;
        }
        
        
        if(!is_null($filtro)){ 
            
            $query->andWhere('(cli.nombre LIKE :filtro OR cli.rutHash = :filtroRutHash)')
                ->setParameter('filtro', '%' . $filtro . '%')
                ->setParameter('filtroRutHash', Cifrado::hash($filtro, 'rut'))                
         ;

        }
        if(!is_null($compania)){
            $query->andWhere('a.cuenta = '.$compania);
        }
        
        if(!is_null($otros) && $otros!=''){ 
            $query->andWhere($otros)
         ;

        }
    
        return $query->getQuery()
            ->getResult()
        ;
    }
    // /**
    //  * @return Cuota[] Returns an array of Cuota objects
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
    public function findOneBySomeField($value): ?Cuota
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
