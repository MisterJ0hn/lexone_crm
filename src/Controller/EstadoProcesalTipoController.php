<?php

namespace App\Controller;

use App\Entity\EstadoProcesalTipo;
use App\Repository\EmpresaRepository;
use App\Repository\EstadoProcesalTipoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/** Catálogo de estados procesales de la empresa (alta rápida desde la línea de tiempo de la causa). */
#[Route("/estado_procesal_tipo")]
class EstadoProcesalTipoController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Crea el estado procesal en la empresa del usuario o reutiliza uno activo con el mismo
     * nombre, y devuelve {id, nombre} para dejarlo seleccionado en el combo.
     */
    #[Route("/rapido", name: "estado_procesal_tipo_rapido", methods: ["POST"])]
    public function rapido(Request $request, EstadoProcesalTipoRepository $tipoRepository, EmpresaRepository $empresaRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('create', 'linea_tiempo');
        if (!$this->isCsrfTokenValid('estado_procesal_tipo_rapido', (string) $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['detail' => 'Token inválido, recargue la página.'], 400);
        }
        $nombre = trim((string) $request->request->get('nombre'));
        if ($nombre === '' || mb_strlen($nombre) > 255) {
            return new JsonResponse(['detail' => 'Ingrese el nombre del estado procesal (máximo 255 caracteres).'], 422);
        }

        $empresa = $empresaRepository->find($this->getUser()->getEmpresaActual());
        foreach ($tipoRepository->findBy(['empresa' => $empresa->getId(), 'estado' => true]) as $existente) {
            if (mb_strtolower($existente->getNombre()) === mb_strtolower($nombre)) {
                return new JsonResponse(['id' => $existente->getId(), 'nombre' => $existente->getNombre()]);
            }
        }

        $tipo = (new EstadoProcesalTipo())->setEmpresa($empresa)->setNombre($nombre)->setEstado(true);
        $this->entityManager->persist($tipo);
        $this->entityManager->flush();

        return new JsonResponse(['id' => $tipo->getId(), 'nombre' => $tipo->getNombre()]);
    }
}
