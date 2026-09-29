<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Corte;
use App\Entity\Cuenta;
use App\Entity\JuzgadoCuenta;
use App\Form\JuzgadoCuentaType;
use App\Repository\JuzgadoCuentaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/juzgado_cuenta")]
class JuzgadoCuentaController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }
    #[Route("/", name: "juzgado_cuenta_index", methods: ["GET"])]
    public function index(JuzgadoCuentaRepository $juzgadoCuentaRepository): Response
    {
        return $this->render('juzgado_cuenta/index.html.twig', [
            'juzgado_cuentas' => $juzgadoCuentaRepository->findAll(),
        ]);
    }

    #[Route("/new", name: "juzgado_cuenta_new", methods: ["GET","POST"])]
    public function new(Request $request): Response
    {
        $juzgadoCuentum = new JuzgadoCuenta();
        $form = $this->createForm(JuzgadoCuentaType::class, $juzgadoCuentum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;
            $entityManager->persist($juzgadoCuentum);
            $entityManager->flush();

            return $this->redirectToRoute('juzgado_cuenta_index');
        }

        return $this->render('juzgado_cuenta/new.html.twig', [
            'juzgado_cuentum' => $juzgadoCuentum,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name: "juzgado_cuenta_show", methods: ["GET"])]
    public function show(JuzgadoCuenta $juzgadoCuentum): Response
    {
        return $this->render('juzgado_cuenta/show.html.twig', [
            'juzgado_cuentum' => $juzgadoCuentum,
        ]);
    }

    #[Route("/{id}/edit", name: "juzgado_cuenta_edit", methods: ["GET","POST"])]
    public function edit(Request $request, JuzgadoCuenta $juzgadoCuentum): Response
    {
        $form = $this->createForm(JuzgadoCuentaType::class, $juzgadoCuentum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('juzgado_cuenta_index');
        }

        return $this->render('juzgado_cuenta/edit.html.twig', [
            'juzgado_cuentum' => $juzgadoCuentum,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}/combo", name: "juzgado_cuenta_combo", methods: ["GET","POST"])]
    public function combo(Request $request, Cuenta $cuenta): Response
    {

        return $this->render('juzgado_cuenta/combo.html.twig', [
            'juzgadoCuentas' => $cuenta->getJuzgadoCuentas(),
            
        ]);
    }
    #[Route("/{id}/corte_combo", name: "juzgado_corte_combo", methods: ["GET","POST"])]
    public function corteCombo(Request $request, Corte $corte): Response
    {

        return $this->render('juzgado_cuenta/comboCorte.html.twig', [
            'juzgados' => $corte->getJuzgados(),
        ]);
    }

    #[Route("/{id}", name: "juzgado_cuenta_delete", methods: ["DELETE"])]
    public function delete(Request $request, JuzgadoCuenta $juzgadoCuentum): Response
    {
        if ($this->isCsrfTokenValid('delete'.$juzgadoCuentum->getId(), $request->request->get('_token'))) {
            $entityManager = $this->entityManager;
            $entityManager->remove($juzgadoCuentum);
            $entityManager->flush();
        }

        return $this->redirectToRoute('juzgado_cuenta_index');
    }
}
