<?php

namespace App\Controller;

use App\Entity\Materia;
use App\Entity\Servicio;
use App\Form\ServicioType;
use App\Repository\EmpresaRepository;
use App\Repository\ServicioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Servicios de la empresa por materia (reemplaza a MateriaEstrategia/EstrategiaJuridica).
 * Un servicio no tiene línea de tiempo: solo materia y empresa.
 */
#[Route("/servicio")]
class ServicioController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /** Alta y listado de los servicios de una materia. */
    #[Route("/{id}/new", name: "servicio_new", requirements: ['id' => '\d+'], methods: ["GET","POST"])]
    public function new(Request $request, Materia $materia, ServicioRepository $servicioRepository, EmpresaRepository $empresaRepository): Response
    {
        $this->denyAccessUnlessGranted('create', 'materia_index');
        $empresa = $empresaRepository->find($this->getUser()->getEmpresaActual());

        $servicio = (new Servicio())->setMateria($materia)->setEmpresa($empresa)->setEstado(true);
        $form = $this->createForm(ServicioType::class, $servicio);
        $form->handleRequest($request);

        $toast = '';
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($servicio);
            $this->entityManager->flush();
            $toast = "Toast.fire({icon: 'success', title: 'Registro grabado con exito'})";
            // Formulario limpio para el siguiente servicio.
            $servicio = (new Servicio())->setMateria($materia)->setEmpresa($empresa)->setEstado(true);
            $form = $this->createForm(ServicioType::class, $servicio);
        }

        return $this->render('servicio/new.html.twig', [
            'materia' => $materia,
            'form' => $form->createView(),
            'error_toast' => $toast,
            'servicios' => $servicioRepository->findBy(['materia' => $materia->getId(), 'empresa' => $empresa->getId(), 'estado' => true], ['nombre' => 'ASC']),
        ]);
    }

    #[Route("/{id}/edit", name: "servicio_edit", requirements: ['id' => '\d+'], methods: ["GET","POST"])]
    public function edit(Request $request, Servicio $servicio): Response
    {
        $this->denyAccessUnlessGranted('edit', 'materia_index');
        $this->verificarServicio($servicio);

        $form = $this->createForm(ServicioType::class, $servicio);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->redirectToRoute('servicio_new', ['id' => $servicio->getMateria()->getId()]);
        }

        return $this->render('servicio/edit.html.twig', ['servicio' => $servicio, 'form' => $form->createView()]);
    }

    /** Baja lógica: las causas que ya usan el servicio lo conservan. */
    #[Route("/{id}/delete", name: "servicio_delete", requirements: ['id' => '\d+'], methods: ["POST"])]
    public function delete(Request $request, Servicio $servicio): Response
    {
        $this->denyAccessUnlessGranted('edit', 'materia_index');
        $this->verificarServicio($servicio);

        if ($this->isCsrfTokenValid('delete' . $servicio->getId(), (string) $request->request->get('_token'))) {
            $servicio->setEstado(false);
            $this->entityManager->flush();
        }
        return $this->redirectToRoute('servicio_new', ['id' => $servicio->getMateria()->getId()]);
    }

    /**
     * Alta rápida desde los formularios de causa (botón "+" junto al combo de servicios).
     * Crea el servicio en la empresa del usuario o reutiliza uno activo con el mismo
     * nombre, y devuelve {id, nombre} para dejarlo seleccionado.
     */
    #[Route("/{id}/rapido", name: "servicio_rapido", requirements: ['id' => '\d+'], methods: ["POST"])]
    public function rapido(Request $request, Materia $materia, ServicioRepository $servicioRepository, EmpresaRepository $empresaRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!$this->isCsrfTokenValid('servicio_rapido', (string) $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['detail' => 'Token inválido, recargue la página.'], 400);
        }
        $nombre = trim((string) $request->request->get('nombre'));
        if ($nombre === '' || mb_strlen($nombre) > 255) {
            return new JsonResponse(['detail' => 'Ingrese el nombre del servicio (máximo 255 caracteres).'], 422);
        }

        $empresa = $empresaRepository->find($this->getUser()->getEmpresaActual());
        foreach ($servicioRepository->findBy(['materia' => $materia->getId(), 'empresa' => $empresa->getId(), 'estado' => true]) as $existente) {
            if (mb_strtolower($existente->getNombre()) === mb_strtolower($nombre)) {
                return new JsonResponse(['id' => $existente->getId(), 'nombre' => $existente->getNombre()]);
            }
        }

        $servicio = (new Servicio())->setEmpresa($empresa)->setMateria($materia)->setNombre($nombre)->setEstado(true);
        $this->entityManager->persist($servicio);
        $this->entityManager->flush();

        return new JsonResponse(['id' => $servicio->getId(), 'nombre' => $servicio->getNombre()]);
    }

    /** Combo de servicios (solo <option>) de una materia, para los formularios de causa. */
    #[Route("/{id}/combo", name: "servicio_combo", requirements: ['id' => '\d+'], methods: ["GET","POST"])]
    public function combo(Materia $materia, ServicioRepository $servicioRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('servicio/combo.html.twig', [
            'servicios' => $servicioRepository->findBy(
                ['materia' => $materia->getId(), 'empresa' => $this->getUser()->getEmpresaActual(), 'estado' => true],
                ['nombre' => 'ASC']
            ),
        ]);
    }

    private function verificarServicio(Servicio $servicio): void
    {
        if ($servicio->getEmpresa() === null || $servicio->getEmpresa()->getId() !== $this->getUser()->getEmpresaActual()) {
            throw $this->createNotFoundException('Servicio no encontrado.');
        }
    }
}
