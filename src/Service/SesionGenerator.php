<?php

namespace App\Service;
use App\Entity\Empresa;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
class SesionGenerator
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
    public function getEmpresaActual()
    {
        $u = $this->container->getUser();
        $em=$this->container2->get('doctrine');
        $e=$em->getRepository(Empresa::class)->find($u->getEmpresaActual());

        return $e;
    }
}