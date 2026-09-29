<?php

namespace App\Security\Voter;

use App\Entity\Modulo;
use App\Entity\ModuloPer;

use App\Entity\Privilegio;
use App\Entity\Accion;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PrincipalVoter extends Voter
{
    public function __construct(ContainerInterface $container) {
        $this->em =$container->get('doctrine');
    }

    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['view', 'edit','create','full']);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        $modulo=$this->em->getRepository(Modulo::class)->findOneBy(['nombre'=>$subject]);
        $moduloPer=$this->em->getRepository(ModuloPer::class)->findOneBy(['modulo'=>$modulo->getId(),'empresa'=>1]);

        if(!$moduloPer){
            return false;
        }
        try
        {

            // ... (check conditions and return true to grant permission) ...
            switch ($attribute) {
                case 'view':
                    // logic to determine if the user can EDIT
                    // return true or false
                    return $this->canView($moduloPer,$user);
                    break;
                case 'edit':
                    // logic to determine if the user can VIEW
                    // return true or false
                    return $this->canEdit($moduloPer,$user);
                    break;
                case 'create':
                    // logic to determine if the user can VIEW
                    // return true or false
                    return $this->canCreate($moduloPer,$user);
                    break;
                case 'full':
                    // logic to determine if the user can VIEW
                    // return true or false
                    return $this->full($moduloPer,$user);
                    break;
            }
        }catch(Exception $e){
            throw("Error personalizado");
        }

        return false;
    }
    private function canView(ModuloPer $modulo, Usuario $user)
    {
        // si pueden editar, pueden ver
        if ($this->canEdit($modulo, $user)) {
            return true;
        }
        if ($this->canCreate($modulo, $user)) {
            return true;
        }
        if ($this->full($modulo, $user)) {
            return true;
        }
        $accion=$this->em->getRepository(Accion::class)->findOneBy(['empresa'=>1,'accion'=>'view']);

        if(!$accion){
            
        }
        $privilegio= $this->em->getRepository(Privilegio::class)->findOneBy(['moduloPer'=>$modulo->getId(),'usuario'=>$user->getId(),'accion'=>$accion->getId()]);
       
        if($privilegio){
            return true;
        }else{
            return false;
        }
        // el objeto modulo podría tener, por ejemplo, un método isPrivate()
        // que comprueba la propiedad booleana $private
        return true;
    }

    private function canEdit(ModuloPer $modulo, Usuario $user)
    {
        if ($this->full($modulo, $user)) {
            return true;
        }
        // esto asume que el objeto tiene un método getOwner()
        // para obtener la entidad del usuario que posee este objeto


        $accion=$this->em->getRepository(Accion::class)->findOneBy(['empresa'=>1,'accion'=>'edit']);

        if(!$accion){
            
        }
        
        $privilegio= $this->em->getRepository(Privilegio::class)->findOneBy(['moduloPer'=>$modulo->getId(),'usuario'=>$user->getId(),'accion'=>$accion->getId()]);
        
        if($privilegio){
            return true;
        }else{
            return false;
        }
        
    }
    private function canCreate(ModuloPer $modulo, Usuario $user)
    {
        if ($this->full($modulo, $user)) {
            return true;
        }

        $accion=$this->em->getRepository(Accion::class)->findOneBy(['empresa'=>1,'accion'=>'create']);

        if(!$accion){
            
        }
        $privilegio= $this->em->getRepository(Privilegio::class)->findOneBy(['moduloPer'=>$modulo->getId(),'usuario'=>$user->getId(),'accion'=>$accion->getId()]);
    
        if($privilegio){
            return true;
        }else{
            return false;
        }
        // esto asume que el objeto tiene un método getOwner()
        // para obtener la entidad del usuario que posee este objeto
       
    }
    private function full(ModuloPer $modulo, Usuario $user)
    {
        $accion=$this->em->getRepository(Accion::class)->findOneBy(['empresa'=>1,'accion'=>'full']);

        if(!$accion){
            
        }
        $privilegio= $this->em->getRepository(Privilegio::class)->findOneBy(['moduloPer'=>$modulo->getId(),'usuario'=>$user->getId(),'accion'=>$accion->getId()]);
       
        if($privilegio){
            return true;
        }else{
            return false;
        }
        // esto asume que el objeto tiene un método getOwner()
        // para obtener la entidad del usuario que posee este objeto
       
    }
}
