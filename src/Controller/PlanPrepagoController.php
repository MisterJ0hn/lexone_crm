<?php

namespace App\Controller;

use App\Entity\PlanPrepago;
use App\Form\PlanPrepagoType;
use App\Repository\EmpresaRepository;
use App\Repository\PlanPrepagoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Mantenedor de los planes de pre-pago (honorarios tipo "Pre-pago") de la empresa del usuario.
 */
#[Route("/plan_prepago")]
class PlanPrepagoController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route("/", name: "plan_prepago_index", methods: ["GET", "POST"])]
    public function index(Request $request, PlanPrepagoRepository $planRepository, EmpresaRepository $empresaRepository): Response
    {
        $user = $this->getUser();

        $plan = new PlanPrepago();
        $plan->setEmpresa($empresaRepository->find($user->getEmpresaActual()));
        $plan->setEstado(true);
        $form = $this->createForm(PlanPrepagoType::class, $plan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($plan);
            $this->entityManager->flush();

            return $this->redirectToRoute('plan_prepago_index');
        }

        return $this->render('plan_prepago/index.html.twig', [
            'planes' => $planRepository->findBy(['empresa' => $user->getEmpresaActual(), 'estado' => true], ['nombre' => 'ASC']),
            'form' => $form->createView(),
            'pagina' => 'Planes de pre-pago',
        ]);
    }

    #[Route("/{id}/edit", name: "plan_prepago_edit", requirements: ['id' => '\d+'], methods: ["GET", "POST"])]
    public function edit(Request $request, PlanPrepago $plan): Response
    {
        $this->verificarEmpresa($plan);
        $form = $this->createForm(PlanPrepagoType::class, $plan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('plan_prepago_index');
        }

        return $this->render('plan_prepago/edit.html.twig', [
            'plan' => $plan,
            'form' => $form->createView(),
            'pagina' => 'Planes de pre-pago',
        ]);
    }

    /** Se desactiva (no se borra): los contratos ya firmados conservan su plan. */
    #[Route("/{id}", name: "plan_prepago_delete", requirements: ['id' => '\d+'], methods: ["DELETE"])]
    public function delete(Request $request, PlanPrepago $plan): Response
    {
        $this->verificarEmpresa($plan);
        if ($this->isCsrfTokenValid('delete' . $plan->getId(), (string) $request->request->get('_token'))) {
            $plan->setEstado(false);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('plan_prepago_index');
    }

    private function verificarEmpresa(PlanPrepago $plan): void
    {
        if ($plan->getEmpresa()?->getId() !== $this->getUser()->getEmpresaActual()) {
            throw $this->createNotFoundException('Plan no encontrado.');
        }
    }
}
