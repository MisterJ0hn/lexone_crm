<?php

namespace App\Controller;

use App\Entity\Causa;
use App\Entity\Contrato;
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
            'materias'   => $em->getRepository(Materia::class)->findBy([], ['orden' => 'ASC', 'nombre' => 'ASC']),
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
            $servicio   = trim((string) $q->get('bServicio', '0'));
            $servicio   = ($servicio === '' || $servicio === '0') ? null : $servicio;
            $usuarioRegistro = (int) $q->get('bUsuario', 0) ?: null;
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

            [$empresa, $usuario, $usuarioRegistro] = $this->alcanceUsuario($user, $usuarioRegistro);

            $query = $contratoRepository->findMisClientesQuery(
                $empresa, $materia, $filtro, $usuario, $usuarioRegistro, $servicio, $primerPago, $dateInicio, $dateFin
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
        // Varias materias repiten el nombre de un servicio: se agrupa por nombre.
        // El valor del filtro es el nombre (no el id).
        $nombres = [];
        foreach ($em->getRepository(Servicio::class)->findBy($criterio, ['nombre' => 'ASC']) as $s) {
            $nombres[mb_strtolower(trim($s->getNombre()))] ??= trim($s->getNombre());
        }
        $servicios = array_map(fn(string $n) => ['id' => $n, 'nombre' => $n], array_values($nombres));

        // Usuarios que registraron contratos (visibles para quien consulta).
        $qb = $em->createQueryBuilder()
            ->select('DISTINCT u.id AS id, u.nombre AS nombre')
            ->from(Contrato::class, 'c')
            ->join('c.usuarioRegistro', 'u')
            ->join('c.agenda', 'a')
            ->join('a.cuenta', 'cu')
            ->where('cu.empresa = :empresa')
            ->setParameter('empresa', $empresa)
            ->orderBy('u.nombre', 'ASC');
        [, $abogado, $usuarioRegistro] = $this->alcanceUsuario($user, null);
        if (null !== $abogado) {
            $qb->andWhere('a.abogado = :abogado')->setParameter('abogado', $abogado);
        }
        if (null !== $usuarioRegistro) {
            $qb->andWhere('u.id = :ur')->setParameter('ur', $usuarioRegistro);
        }
        $usuarios = $qb->getQuery()->getArrayResult();

        return new JsonResponse(['servicios' => $servicios, 'usuarios' => $usuarios]);
    }

    private function esAlcanceEmpresa($user): bool
    {
        return in_array($user->getUsuarioTipo()->getId(), self::TIPOS_EMPRESA, true);
    }

    /**
     * @return array{0: ?int, 1: ?int, 2: ?int} [empresa, abogado, usuarioRegistro]
     */
    private function alcanceUsuario($user, ?int $usuarioRegistro): array
    {
        $tipo = $user->getUsuarioTipo()->getId();

        if ($this->esAlcanceEmpresa($user)) {
            return [$user->getEmpresaActual(), null, $usuarioRegistro];
        }
        if ($tipo === self::TIPO_TRAMITADOR) {
            // Un tramitador solo ve los contratos que tiene asignados.
            return [$user->getEmpresaActual(), null, $user->getId()];
        }

        return [$user->getEmpresaActual(), $user->getId(), $usuarioRegistro];
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
