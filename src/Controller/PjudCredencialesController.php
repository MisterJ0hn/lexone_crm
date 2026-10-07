<?php

namespace App\Controller;

use App\Repository\EmpresaRepository;
use App\Repository\ModuloPerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Clave del Poder Judicial de la empresa (Oficina Judicial Virtual), que usa api-pjud para
 * sincronizar causas (ver PjudController). Es una sola por empresa, no por usuario; la edita
 * el administrador de cuenta desde Mantención según los privilegios del módulo 'pjud_credenciales'.
 */
#[Route("/pjud_credenciales")]
class PjudCredencialesController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route("/", name: "pjud_credenciales_index", methods: ["GET"])]
    public function index(EmpresaRepository $empresaRepository, ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view', 'pjud_credenciales');
        $user = $this->getUser();
        $pagina = $moduloPerRepository->findOneByName('pjud_credenciales', $user->getEmpresaActual());

        return $this->render('pjud_credenciales/index.html.twig', [
            'empresa' => $empresaRepository->find($user->getEmpresaActual()),
            'pagina' => $pagina ? $pagina->getNombre() : 'Clave Poder Judicial',
        ]);
    }

    #[Route("/guardar", name: "pjud_credenciales_guardar", methods: ["POST"])]
    public function guardar(Request $request, EmpresaRepository $empresaRepository): Response
    {
        $this->denyAccessUnlessGranted('edit', 'pjud_credenciales');
        if (!$this->isCsrfTokenValid('pjud_credenciales', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token inválido, intente nuevamente');
            return $this->redirectToRoute('pjud_credenciales_index');
        }

        $empresa = $empresaRepository->find($this->getUser()->getEmpresaActual());
        $empresa->setPjudRut(trim((string) $request->request->get('pjud_rut')) ?: null);
        $empresa->setPjudMetodoLogin($request->request->getInt('pjud_metodo_login', 1) === 2 ? 2 : 1);
        // La clave no se muestra: vacío = conservar la actual.
        $clave = (string) $request->request->get('pjud_clave');
        if ($clave !== '') {
            $empresa->setPjudClave($clave);
        }
        $this->entityManager->flush();

        $this->addFlash('success', 'Clave del Poder Judicial guardada');
        return $this->redirectToRoute('pjud_credenciales_index');
    }
}
