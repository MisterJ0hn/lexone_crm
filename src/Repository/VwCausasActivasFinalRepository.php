<?php

namespace App\Repository;

use App\Entity\VwCausasActivasFinal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method VwCausasActivasFinal|null find($id, $lockMode = null, $lockVersion = null)
 * @method VwCausasActivasFinal|null findOneBy(array $criteria, array $orderBy = null)
 * @method VwCausasActivasFinal[]    findAll()
 * @method VwCausasActivasFinal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VwCausasActivasFinalRepository extends ServiceEntityRepository
{
     public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VwCausasActivasFinal::class);
    }

    public function findByCuentas($cuentas, $tipoCausa=null)
    {
        $qb = $this->createQueryBuilder('v');

        switch ($tipoCausa) {
            case 'alDia':
                $qb->andWhere('v.moroso = 0');
                break;
            case 'clientesActivos':
                $qb->distinct(true);
                break;
            case 'clientesAlDia':
                $qb->andWhere('v.moroso = 0')
                ->distinct(true);
                break;
            case 'clientesMorosos':
                $qb->andWhere('v.moroso = 1')
                ->distinct(true);
                break;
            case 'clientesActivosVIP':
                $qb->andWhere('v.vip = 1')
                ->distinct(true);
                break;
            case 'clientesAlDiaVIP':
                $qb->andWhere('v.moroso = 0')
                ->andWhere('v.vip = 1')
                ->distinct(true);
                break;
            case 'conRol':
                $qb->andWhere('v.tieneRol = 1');
                break;
            case 'sinRol':
                $qb->andWhere('v.tieneRol = 0');
                break;
            case 'finalizadas':
                $qb->andWhere('v.causaFinalizada = 1');
                break;
            default:
                // No additional filtering
                break;
        }
        if ($cuentas) {
            $qb->andWhere('v.cuentaId in ('.$cuentas.')');
             
        }

       
        return $qb->getQuery()->getResult();
    }
    
    public function groupByCerrador($cuentas,$tipoCausa=null){
        $qb = $this->createQueryBuilder('v')
                   ->select('v.tramitadorId, v.tramitador, COUNT(v.tramitadorId) as total')
                   ->where('v.cuentaId in ('.$cuentas.')');


        switch ($tipoCausa) {
            case 'alDia':
                $qb->andWhere('v.moroso = 0');
                break;
            case 'clientesActivos':
                $qb->distinct(true);
                break;
            case 'clientesAlDia':
                $qb->andWhere('v.moroso = 0')
                ->distinct(true);
                break;
            case 'clientesMorosos':
                $qb->andWhere('v.moroso = 1')
                ->distinct(true);
                break;
            case 'clientesActivosVIP':
                $qb->andWhere('v.vip = 1')
                ->distinct(true);
                break;
            case 'clientesAlDiaVIP':
                $qb->andWhere('v.moroso = 0')
                ->andWhere('v.vip = 1')
                ->distinct(true);
                break;
            case 'conRol':
                $qb->andWhere('v.tieneRol = 1');
                break;
            case 'sinRol':
                $qb->andWhere('v.tieneRol = 0');
                break;
            case 'finalizadas':
                $qb->andWhere('v.causaFinalizada = 1');
                break;
            default:
                // No additional filtering
                break;
        }
        
        $qb->groupBy('v.tramitadorId')
        ->orderBy('total', 'DESC');
        return $qb->getQuery()->getResult();

    }
}
