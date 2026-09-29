<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;

use App\Form\ConfiguracionType;
use App\Repository\ConfiguracionRepository;
use App\Repository\ModuloPerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route("/max_dias_comision")]
class MaxDiasComisionController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }
    #[Route("/", name: "max_dias_comision_index")]
    public function index(Request $request,ConfiguracionRepository $configuracionRepository,ModuloPerRepository $moduloPerRepository): Response
    {
        $user=$this->getUser();
        
        $pagina=$moduloPerRepository->findOneByName('configuracion',$user->getEmpresaActual());
        $configuracion=$configuracionRepository->find(1);
        $form = $this->createForm(ConfiguracionType::class, $configuracion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('max_dias_comision_index');
        }

        return $this->render('max_dias_comision/index.html.twig', [
            'configuracion' => $configuracion,
            'pagina'=>$pagina->getNombre(),
            'form' => $form->createView(),
        ]);
    }
}
