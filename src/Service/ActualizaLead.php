<?php

namespace App\Service;
use App\Entity\Agenda;
use App\Entity\Configuracion;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
class ActualizaLead
{
    private $container;
    private $container2;
    private $generator;

    public function __construct(Security $security,UrlGeneratorInterface $generatorUrl,ContainerInterface $container){
        $this->container=$security;   
        $this->generator=$generatorUrl;
        $this->container2=$container;

        
    }
    public function getSesion()
    {
       
        $u = $this->container->getUser();

        return $u;

        
    }
    public function isIncompleto($agenda)
    {
        
        
        if((trim($agenda->getCampania())=='' || trim($agenda->getNombreCliente())=='' || trim($agenda->getEmailCliente())=='' || trim($agenda->getTelefonoCliente())=='') && null != $agenda->getLead()){
            return true;
        }else{
            return false;
        }
    }
    public function completar($agenda)
    {
        $u = $this->container->getUser();
        $entityManager = $this->container2->get('doctrine')->getManager();
        $em=$this->container2->get('doctrine');

        if($this->isIncompleto($agenda)){

            
            $configuracion=$em->getRepository(Configuracion::class)->find(1);
            if(false !== @file_get_contents('https://graph.facebook.com/'.$agenda->getLead().'?access_token='.$configuracion->getAccessToken())){
                $data_1 = json_decode( file_get_contents('https://graph.facebook.com/'.$agenda->getLead().'?access_token='.$configuracion->getAccessToken()), true );
                $data=$data_1['field_data'];
                var_dump($data);
                $nombre=$data[0]['values'][0];
                $telefono=$data[1]['values'][0];
                $correo=$data[2]['values'][0];

                if(trim($agenda->getNombreCliente())==''){
                    $agenda->setNombreCliente($nombre);
                }
                if(trim($agenda->getEmailCliente())==''){
                    $agenda->setEmailCliente($correo);
                }
                if(trim($agenda->getTelefonoCliente())==''){
                    $agenda->setTelefonoCliente($telefono);
                }


                $campania = json_decode( file_get_contents('https://graph.facebook.com/v8.0/'.$agenda->getFormId().'?access_token='.$configuracion->getAccessToken()), true );

                if(trim($agenda->getCampania())==''){
                    $agenda->setCampania($campania['name']);
                }
                $entityManager->persist($agenda);
                $entityManager->flush();
            }
        }
    }
}