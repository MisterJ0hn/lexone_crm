<?php

namespace App\Repository;

use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Usuario|null find($id, $lockMode = null, $lockVersion = null)
 * @method Usuario|null findOneBy(array $criteria, array $orderBy = null)
 * @method Usuario[]    findAll()
 * @method Usuario[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsuarioRepository extends ServiceEntityRepository 
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuario::class);
    }


    public function findByCuenta($cuenta=null,$filtro=array())
    {
        $query=$this->createQueryBuilder('u')
            ->join('u.usuarioCuentas','uc')
            ->andWhere('uc.cuenta = :cuenta');
        if(null!=$cuenta){
            $query->setParameter('cuenta', $cuenta);
        }
            
        foreach($filtro as $index=>$nombre){
            $query->andWhere('u.'.$index.'='.$nombre);
        }
        return $query->getQuery()
            ->getResult()
        ;
    }

    public function findByEmpresa($empresa=null,$filtro=array())
    {
        $query=$this->createQueryBuilder('u')
            ->andWhere('u.empresa = :empresa')
            ->setParameter('empresa', $empresa);

        foreach($filtro as $index=>$nombre){
            $query->andWhere('u.'.$index.'='.$nombre);
        }
        return $query->getQuery()
            ->getResult()
        ;
    }

    public function cumpleanios($mes, $dia, $tipoUsuario){
        $query=$this->createQueryBuilder('u')
        ->select('MONTH(fechaNacimiento) as mes','DAY(fechaNacimiento) as dia')
        ->andWhere('usuarioTipo in ('.$tipoUsuario.')')
        ->andWhere('estado=true')
        ->andHaving('mes='.$mes)
        ->andHaving('dia='.$dia);

        return $query->getQuery()
        ->getResult();

    }

    public function respaldarUsername(UserInterface $user): void
    {
        if (!$user instanceof Usuario) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setUsernameOriginal($user->getUsername());
        $user->setUsername($user->getUsername()."_".date("YmdHis"));
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function restaurarUsername(UserInterface $user): void
    {
        if (!$user instanceof Usuario) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }
       
        $user->setUsername($user->getUsernameOriginal());       
        $this->_em->persist($user);
        $this->_em->flush();
         
    }

    // /**
    //  * @return Usuario[] Returns an array of Usuario objects
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
    public function findOneBySomeField($value): ?Usuario
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
