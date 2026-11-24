<?php

namespace App\Controller;

use App\Entity\CuentaCorriente;
use App\Entity\PagoCanal;
use App\Repository\EmpresaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Alta rápida de los catálogos por empresa que usa el formulario de pagos
 * (cuenta corriente y canal de pago). Sin privilegios propios: basta con estar autenticado.
 */
#[Route("/pago_catalogo")]
class PagoCatalogoController extends AbstractController
{
    private const CATALOGOS = [
        'cuenta_corriente' => CuentaCorriente::class,
        'pago_canal' => PagoCanal::class,
    ];

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /** Crea el registro en la empresa del usuario (o reutiliza el del mismo nombre) y devuelve {id, nombre}. */
    #[Route("/rapido/{tipo}", name: "pago_catalogo_rapido", requirements: ['tipo' => 'cuenta_corriente|pago_canal'], methods: ["POST"])]
    public function rapido(string $tipo, Request $request, EmpresaRepository $empresaRepository): JsonResponse
    {
        if (!$this->isCsrfTokenValid('pago_catalogo_rapido', (string) $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['detail' => 'Token inválido, recargue la página.'], 400);
        }
        $nombre = trim((string) $request->request->get('nombre'));
        if ($nombre === '' || mb_strlen($nombre) > 255) {
            return new JsonResponse(['detail' => 'Ingrese el nombre (máximo 255 caracteres).'], 422);
        }

        $clase = self::CATALOGOS[$tipo];
        $empresa = $empresaRepository->find($this->getUser()->getEmpresaActual());
        $repo = $this->entityManager->getRepository($clase);
        foreach ($repo->findBy(['empresa' => $empresa->getId()]) as $existente) {
            if (mb_strtolower($existente->getNombre()) === mb_strtolower($nombre)) {
                return new JsonResponse(['id' => $existente->getId(), 'nombre' => $existente->getNombre()]);
            }
        }

        $nuevo = (new $clase())->setNombre($nombre)->setEmpresa($empresa);
        if ($nuevo instanceof PagoCanal) {
            $orden = (int) $this->entityManager->createQuery('SELECT MAX(p.orden) FROM ' . PagoCanal::class . ' p WHERE p.empresa = :e')
                ->setParameter('e', $empresa)->getSingleScalarResult();
            $nuevo->setEstado(true)->setOrden($orden + 1);
        }
        $this->entityManager->persist($nuevo);
        $this->entityManager->flush();

        return new JsonResponse(['id' => $nuevo->getId(), 'nombre' => $nuevo->getNombre()]);
    }
}
