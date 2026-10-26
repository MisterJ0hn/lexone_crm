<?php

namespace App\Controller;

use App\Entity\Profesion;
use App\Repository\EmpresaRepository;
use App\Repository\ProfesionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/** Catálogo de profesiones u oficios de la empresa (alta rápida desde el contrato Empresa/Convenio). */
#[Route("/profesion")]
class ProfesionController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Crea la profesión en la empresa del usuario o reutiliza una activa con el mismo
     * nombre, y devuelve {id, nombre} para dejarla seleccionada en el combo.
     */
    #[Route("/rapido", name: "profesion_rapido", methods: ["POST"])]
    public function rapido(Request $request, ProfesionRepository $profesionRepository, EmpresaRepository $empresaRepository): JsonResponse
    {
        // Se usa al contratar (panel_abogado) y desde la línea de tiempo del contrato.
        if (!$this->isGranted('create', 'panel_abogado') && !$this->isGranted('create', 'linea_tiempo')) {
            throw $this->createAccessDeniedException();
        }
        if (!$this->isCsrfTokenValid('profesion_rapido', (string) $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['detail' => 'Token inválido, recargue la página.'], 400);
        }
        $nombre = trim((string) $request->request->get('nombre'));
        if ($nombre === '' || mb_strlen($nombre) > 255) {
            return new JsonResponse(['detail' => 'Ingrese la profesión u oficio (máximo 255 caracteres).'], 422);
        }

        $empresa = $empresaRepository->find($this->getUser()->getEmpresaActual());
        foreach ($profesionRepository->findBy(['empresa' => $empresa->getId(), 'estado' => true]) as $existente) {
            if (mb_strtolower($existente->getNombre()) === mb_strtolower($nombre)) {
                return new JsonResponse(['id' => $existente->getId(), 'nombre' => $existente->getNombre()]);
            }
        }

        $profesion = (new Profesion())->setEmpresa($empresa)->setNombre($nombre)->setEstado(true);
        $this->entityManager->persist($profesion);
        $this->entityManager->flush();

        return new JsonResponse(['id' => $profesion->getId(), 'nombre' => $profesion->getNombre()]);
    }
}
