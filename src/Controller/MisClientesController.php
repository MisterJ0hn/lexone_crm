<?php

namespace App\Controller;

use App\Entity\Causa;
use App\Entity\Cuota;
use App\Entity\Materia;
use App\Entity\Servicio;
use App\Entity\Usuario;
use App\Repository\ContratoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/mis_clientes')]
class MisClientesController extends AbstractController
{
    /** Tipos de usuario que ven toda la empresa. */
    private const TIPOS_EMPRESA = [1, 3, 4, 8, 11, 12];
    private const TIPO_TRAMITADOR = 7;

    #[Route(path: '/', name: 'mis_clientes_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $this->denyAccessUnlessGranted('view', 'mis_clientes');

        $request->getSession()->set('origen', 'Mis_Clientes');
        $request->getSession()->set('origen_anexo', 'Mis_Clientes');

        $empresa = $this->getUser()->getEmpresaActual();

        return $this->render('mis_clientes/index.html.twig', [
            'bFiltro'    => '',
            'materias'   => $em->getRepository(Materia::class)->findBy(['empresa' => $empresa], ['nombre' => 'ASC']),
            'dateInicio' => (new \DateTime())->modify('-24 months')->format('Y-m-d'),
            'dateFin'    => date('Y-m-d'),
        ]);
    }

    #[Route(path: '/obtenerContenido', name: 'mis_clientes_obtener_contenido', methods: ['GET'])]
    public function obtenerContenido(
        ContratoRepository $contratoRepository,
        EntityManagerInterface $em,
        PaginatorInterface $paginator,
        Request $request
    ): JsonResponse {
        try {
            $this->denyAccessUnlessGranted('view', 'mis_clientes');

            $user = $this->getUser();
            $q = $request->query;

            $filtro     = $q->get('bFiltro') !== null && $q->get('bFiltro') !== '' ? $q->get('bFiltro') : null;
            $materia    = (int) $q->get('bMateria', 0) ?: null;
            $servicio   = (int) $q->get('bServicio', 0) ?: null;
            $tramitador = (int) $q->get('bTramitador', 0) ?: null;
            $primerPago = (int) $q->get('bPrimerPago', 0) ?: null;

            if ($q->get('bFecha')) {
                $aux = explode(' - ', $q->get('bFecha'));
                $dateInicio = $aux[0];
                $dateFin    = $aux[1] ?? date('Y-m-d');
            } else {
                $dateInicio = (new \DateTime())->modify('-24 months')->format('Y-m-d');
                $dateFin    = date('Y-m-d');
            }
            // Validar formato: las fechas se usan para acotar el rango de la consulta.
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateInicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFin)) {
                throw new \InvalidArgumentException('Rango de fechas inválido.');
            }

            [$empresa, $usuario, $tramitador] = $this->alcanceUsuario($user, $tramitador);

            $query = $contratoRepository->findMisClientesQuery(
                $empresa, $materia, $filtro, $usuario, $tramitador, $servicio, $primerPago, $dateInicio, $dateFin
            );

            $contratos = $paginator->paginate(
                $query,
                $request->query->getInt('page', 1),
                10,
                ['defaultSortFieldName' => 'c.id', 'defaultSortDirection' => 'desc']
            );

            $extras = [];
            foreach ($contratos->getItems() as $contrato) {
                $extras[$contrato->getId()] = $this->datosCausas($em, $contrato);
            }

            return new JsonResponse([
                'html' => $this->renderView('mis_clientes/_tabla.html.twig', [
                    'contratos' => $contratos,
                    'extras'    => $extras,
                ]),
                'total'            => $contratos->getTotalItemCount(),
                'total_formateado' => number_format($contratos->getTotalItemCount(), 0, ',', '.'),
            ]);
        } catch (\Exception $e) {
            if ($e->getCode() == 403) {
                return new JsonResponse(['error' => 'No fue posible acceder a los datos. Su sesión puede haber expirado. Por favor, vuelva a iniciar sesión.']);
            }

            return new JsonResponse(['error' => $e->getMessage()]);
        }
    }

    #[Route(path: '/filtros_por_materia', name: 'mis_clientes_filtros_por_materia', methods: ['GET'])]
    public function filtrosPorMateria(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('view', 'mis_clientes');

        $user = $this->getUser();
        $empresa = $user->getEmpresaActual();
        $materiaId = (int) $request->query->get('materia', 0);

        $criterio = ['empresa' => $empresa, 'estado' => true];
        if ($materiaId) {
            $criterio['materia'] = $materiaId;
        }
        $servicios = array_map(
            fn(Servicio $s) => ['id' => $s->getId(), 'nombre' => $s->getNombre()],
            $em->getRepository(Servicio::class)->findBy($criterio, ['nombre' => 'ASC'])
        );

        if ($this->esAlcanceEmpresa($user)) {
            $usuarios = $em->getRepository(Usuario::class)->findBy(['usuarioTipo' => self::TIPO_TRAMITADOR, 'estado' => 1]);
        } else {
            $usuarios = $em->getRepository(Usuario::class)->findBy(['id' => $user->getId(), 'estado' => 1]);
        }
        $tramitadores = array_map(
            fn(Usuario $u) => ['id' => $u->getId(), 'nombre' => $u->getNombre()],
            $usuarios
        );

        return new JsonResponse(['servicios' => $servicios, 'tramitadores' => $tramitadores]);
    }

    private function esAlcanceEmpresa($user): bool
    {
        return in_array($user->getUsuarioTipo()->getId(), self::TIPOS_EMPRESA, true);
    }

    /**
     * @return array{0: ?int, 1: ?int, 2: ?int} [empresa, abogado, tramitador]
     */
    private function alcanceUsuario($user, ?int $tramitador): array
    {
        $tipo = $user->getUsuarioTipo()->getId();

        if ($this->esAlcanceEmpresa($user)) {
            return [$user->getEmpresaActual(), null, $tramitador];
        }
        if ($tipo === self::TIPO_TRAMITADOR) {
            // Un tramitador solo ve los contratos que tiene asignados.
            return [$user->getEmpresaActual(), null, $user->getId()];
        }

        return [$user->getEmpresaActual(), $user->getId(), $tramitador];
    }

    /**
     * Datos de las causas activas del contrato y estado de la primera cuota.
     */
    private function datosCausas(EntityManagerInterface $em, $contrato): array
    {
        $materias = $servicios = $rits = [];
        foreach ($contrato->getAgenda()->getCausas() as $causa) {
            /** @var Causa $causa */
            if (!$causa->getEstado() || $causa->getCausaFinalizada()) {
                continue;
            }
            if ($causa->getMateria()) {
                $materias[$causa->getMateria()->getId()] = $causa->getMateria()->getNombre();
            }
            if ($causa->getServicio()) {
                $servicios[$causa->getServicio()->getId()] = $causa->getServicio()->getNombre();
            }
            if ($causa->getLetra() || $causa->getRol() || $causa->getAnio()) {
                $rits[] = $causa->getLetra() . '-' . $causa->getRol() . '-' . $causa->getAnio();
            }
        }

        $primerPagoPendiente = (int) $em->createQueryBuilder()
            ->select('COUNT(q.id)')
            ->from(Cuota::class, 'q')
            ->where('q.contrato = :c')
            ->andWhere('q.numero = 1')
            ->andWhere('q.anular IS NULL OR q.anular = 0')
            ->andWhere('q.pagado IS NULL OR q.monto > q.pagado')
            ->setParameter('c', $contrato)
            ->getQuery()
            ->getSingleScalarResult();

        $diasActividad = null;
        if ($contrato->getFechaUltimaGestion()) {
            $diasActividad = (new \DateTime('today'))->diff(new \DateTime($contrato->getFechaUltimaGestion()->format('Y-m-d')))->days;
        }

        return [
            'materias'       => array_values($materias),
            'servicios'      => array_values($servicios),
            'rits'           => $rits,
            'primerPago'     => $primerPagoPendiente,
            'diasActividad'  => $diasActividad,
        ];
    }
}
