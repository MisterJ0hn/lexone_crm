<?php

namespace App\Controller;

use App\Entity\Materia;
use App\Entity\Empresa;
use App\Form\MateriaType;
use App\Repository\CausaRepository;
use App\Repository\MateriaCorteRepository;
use App\Repository\MateriaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/materia")]
class MateriaController extends AbstractController
{
    #[Route("/", name: "materia_index", methods: ["GET"])]
    public function index(MateriaRepository $materiaRepository): Response
    {
        // Materia es un catálogo global: mismo listado para todas las empresas.
        return $this->render('materia/index.html.twig', [
            'materias' => $materiaRepository->findBy([], ['nombre' => 'ASC']),
        ]);
    }

    #[Route("/new", name: "materia_new", methods: ["GET","POST"])]
    public function new(Request $request): Response
    {
        $user = $this->getUser();
        $empresa = $this->getDoctrine()->getRepository(Empresa::class)->find($user->getEmpresaActual());
        $materium = new Materia();
        $materium->setEmpresa($empresa);

        $form = $this->createForm(MateriaType::class, $materium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($materium);
            $entityManager->flush();

            return $this->redirectToRoute('materia_index');
        }

        return $this->render('materia/new.html.twig', [
            'materium' => $materium,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}", name: "materia_show", methods: ["GET"])]
    public function show(Materia $materium): Response
    {
        return $this->render('materia/show.html.twig', [
            'materium' => $materium,
        ]);
    }

    #[Route("/{id}/edit", name: "materia_edit", methods: ["GET","POST"])]
    public function edit(Request $request, Materia $materium): Response
    {
        $form = $this->createForm(MateriaType::class, $materium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('materia_index');
        }

        return $this->render('materia/edit.html.twig', [
            'materium' => $materium,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/{id}/causa_letras", name: "materia_causa_letras", methods: ["GET"])]
    public function letras(Materia $materia): JsonResponse
    {
        $letras = [];
        if ($materia->getCausaLetras()->count() == 0) {
            return new JsonResponse($letras, 400);
        }
        foreach ($materia->getCausaLetras() as $letra) {
            $letras[] = $letra->getNombre();
        }

        return new JsonResponse($letras, 200);
    }

    /**
     * Materia es un catálogo global (mismo listado para todas las empresas). El
     * {id} de la ruta se conserva por compatibilidad con los llamados existentes
     * (antes era el id de la Cuenta); la materia ya no depende de la Cuenta ni
     * de la empresa.
     */
    #[Route("/{id}/combo", name: "materia_combo", methods: ["GET","POST"])]
    public function combo(int $id, MateriaRepository $materiaRepository): Response
    {
        // Materia es un catálogo global: mismo listado para todas las empresas.
        return $this->render('materia/combo.html.twig', [
            'materias' => $materiaRepository->findBy([], ['nombre' => 'ASC']),
        ]);
    }

    /**
     * Cortes válidos para una materia.
     */
    #[Route("/{id}/corte_combo", name: "materia_corte_combo", methods: ["GET","POST"])]
    public function corteCombo(Materia $materia, MateriaCorteRepository $materiaCorteRepository): Response
    {
        return $this->render('materia/comboCorte.html.twig', [
            'cortes' => $materiaCorteRepository->findBy(['materia' => $materia]),
        ]);
    }

    #[Route("/{id}", name: "materia_delete", methods: ["DELETE"])]
    public function delete(Request $request, Materia $materium, CausaRepository $causaRepository, MateriaCorteRepository $materiaCorteRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$materium->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();

            // No se puede borrar una materia que tiene causas asociadas.
            if ($causaRepository->count(['materia' => $materium]) > 0) {
                $this->addFlash('error', 'No se puede eliminar la materia: tiene causas asociadas.');

                return $this->redirectToRoute('materia_index');
            }

            foreach ($materium->getMateriaEstrategias() as $materia_estrategia) {
                $entityManager->remove($materia_estrategia);
            }
            foreach ($materium->getCausaLetras() as $causaLetra) {
                $entityManager->remove($causaLetra);
            }
            foreach ($materiaCorteRepository->findBy(['materia' => $materium]) as $materiaCorte) {
                $entityManager->remove($materiaCorte);
            }

            $entityManager->remove($materium);
            $entityManager->flush();
        }

        return $this->redirectToRoute('materia_index');
    }
}
