<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/tramitacion")]
class TramitacionController extends AbstractController
{

    
    #[Route("/", name: "tramitacion_index",methods: ["GET"])]
    public function index(): Response
    {

        return $this->render('tramitacion/index.html.twig', [
      
            'pagina'=>'Tramitación',
      
        ]);
    }

}
