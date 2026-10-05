<?php

namespace App\Controller;

use App\Entity\Empresa;
use App\Repository\CausaRepository;
use App\Repository\EmpresaRepository;
use App\Service\EdApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Integración Lexflow: consulta (solo lectura) de Estado Diario, Movimientos y
 * Audiencias expuestos por la API edapi.temposoft.cl.
 */
#[Route('/lexflow')]
class LexflowController extends AbstractController
{
    private const TABS_ESTADO = ['no-leidos' => 'No Leídos', 'leidos' => 'Resueltos', 'pendientes' => 'Pendientes'];
    private const TIPOS_CORTE = ['' => 'Todas', 'suprema' => 'Corte Suprema', 'apelaciones' => 'Corte de Apelaciones'];
    private const VIGENCIAS = ['vigentes' => 'Vigentes', 'finalizadas' => 'No vigentes'];

    public function __construct(
        private readonly EdApiClient $api,
        private readonly EmpresaRepository $empresas,
        private readonly CausaRepository $causas,
    ) {
    }

    /** Apunta el cliente a las credenciales de la empresa del usuario. */
    private function usarEmpresa(): Empresa
    {
        $empresa = $this->empresas->find($this->getUser()->getEmpresaActual());
        if (!$empresa?->isLexflowHabilitado()) {
            throw $this->createAccessDeniedException('Lexflow no está habilitado para esta empresa');
        }
        $this->api->paraEmpresa($empresa);
        return $empresa;
    }

    // ───────────────────────── Estado diario ─────────────────────────

    #[Route('/estado-diario', name: 'ed_estado_diario_index', methods: ['GET'])]
    public function estadoDiario(Request $request): Response
    {
        $this->denyAccessUnlessGranted('view','ed_estado_diario');
        $empresa = $this->usarEmpresa();
        $tab = $request->query->get('tab') === 'cortes' ? 'cortes' : 'materias';
        // Por defecto "Fecha desde" = ayer; si el usuario la vacía explícitamente (llega como ''), se respeta.
        if ($tab === 'materias' && !$request->query->has('fecha_desde')) {
            $request->query->set('fecha_desde', date('Y-m-d', strtotime('-1 day')));
        }
        $page = max(1, $request->query->getInt('page', 1));
        $error = null;
        $datos = ['total' => 0, 'total_pages' => 1, 'page' => 1];
        $jurisdicciones = [];
        $filtros = [];
        $conteos = [];
        $estado = array_key_exists((string) $request->query->get('estado'), self::TABS_ESTADO)
            ? $request->query->get('estado') : 'no-leidos';

        try {
            if ($tab === 'cortes') {
                $filtros = $this->filtros($request, ['busqueda', 'tipo', 'corte']);
                $datos = $this->api->get('estado-diario/cortes', $filtros + ['page' => $page, 'limit' => 50]);
            } else {
                $filtros = $this->filtros($request, ['jurisdiccion', 'fecha_desde', 'fecha_hasta', 'rut']);
                $jurisdicciones = $this->api->get('jurisdicciones', ['excluir_corte' => 'true'])['jurisdicciones'] ?? [];
                // "Solo causas del CRM": se envían los roles a edapi; sin roles no se consulta (edapi devolvería todo).
                $roles = $this->rolesCrm($empresa);
                if ($roles === '') {
                    $conteos = array_fill_keys(array_keys(self::TABS_ESTADO), 0);
                } else {
                    $consulta = $filtros + ($roles !== null ? ['roles' => $roles] : []);
                    $datos = $this->api->get('estado-diario/' . $estado, $consulta + ['page' => $page, 'limit' => 20]);
                    foreach (array_keys(self::TABS_ESTADO) as $t) {
                        $conteos[$t] = $t === $estado
                            ? ($datos['total'] ?? 0)
                            : ($this->api->get('estado-diario/' . $t, $consulta + ['page' => 1, 'limit' => 1])['total'] ?? 0);
                    }
                }
            }
        } catch (\RuntimeException $e) {
            $error = $e->getMessage();
        }

        // Botón "Detalle PJUD": solo para filas que calzan con una Causa del CRM de materia soportada.
        $pjudInfo = ['configurado' => $empresa->isPjudConfigurado(), 'crm' => 0, 'coinciden' => 0, 'externas' => 0, 'sin_soporte' => 0];
        if ($tab === 'materias' && $pjudInfo['configurado'] && !empty($datos['movimientos'])) {
            $mapa = $this->causas->mapaPorRol($empresa->getId());
            $pjudInfo['crm'] = count($mapa);
            foreach ($datos['movimientos'] as &$fila) {
                $causa = $mapa[CausaRepository::claveRol((string) ($fila['rol'] ?? ''), (string) ($fila['tribunal'] ?? ''))] ?? null;
                if ($causa && PjudController::competencia($causa['materia'])) {
                    $fila['crm_causa_id'] = $causa['id'];
                    $pjudInfo['coinciden']++;
                } elseif ($token = PjudController::tokenExterno($fila)) {
                    // No está en el CRM: el detalle se consulta con las credenciales del usuario del sistema.
                    $fila['pjud_token'] = $token;
                    $pjudInfo['externas']++;
                } else {
                    $pjudInfo['sin_soporte']++;
                }
            }
            unset($fila);
        }

        return $this->render('lexflow/estado_diario.html.twig', [
            'tab' => $tab,
            'estado' => $estado,
            'tabs_estado' => self::TABS_ESTADO,
            'tipos_corte' => self::TIPOS_CORTE,
            'conteos' => $conteos,
            'pjud_info' => $pjudInfo,
            'datos' => $datos,
            'jurisdicciones' => $jurisdicciones,
            'filtros' => $filtros,
            'error' => $error,
            'chips' => $this->chips($request, $filtros, $jurisdicciones),
        ]);
    }

    #[Route('/estado-diario/{id}/leido', name: 'ed_estado_diario_leido', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function marcarLeido(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit','ed_estado_diario');
        return $this->cambiarLectura($id, $request, 'leido', 'Registro marcado como leído');
    }

    #[Route('/estado-diario/{id}/no-leido', name: 'ed_estado_diario_no_leido', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function marcarNoLeido(int $id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('edit','ed_estado_diario');
        return $this->cambiarLectura($id, $request, 'no-leido', 'Registro marcado como no leído');
    }

    private function cambiarLectura(int $id, Request $request, string $accion, string $mensaje): Response
    {
        $volver = $this->redirectToRoute('ed_estado_diario_index', $request->request->all('query'));
        if (!$this->isCsrfTokenValid('ed_' . $accion . $id, (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token inválido, intente nuevamente');
            return $volver;
        }
        $this->usarEmpresa();
        try {
            $this->api->post('estado-diario/' . $id . '/' . $accion);
            $this->addFlash('success', $mensaje);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        }
        return $volver;
    }

    // ───────────────────────── Movimientos ─────────────────────────

    #[Route('/movimientos', name: 'ed_movimientos_index', methods: ['GET'])]
    public function movimientos(Request $request): Response
    {
        $this->denyAccessUnlessGranted('view','ed_movimientos');
        $empresa = $this->usarEmpresa();
        $tab = $request->query->get('tab') === 'cortes' ? 'cortes' : 'materias';
        $page = max(1, $request->query->getInt('page', 1));
        $vigencia = array_key_exists((string) $request->query->get('vigencia'), self::VIGENCIAS)
            ? $request->query->get('vigencia') : 'vigentes';
        $error = null;
        $datos = ['total' => 0, 'total_pages' => 1, 'page' => 1];
        $resumen = ['total' => 0, 'por_materia' => [], 'estados_causa' => []];
        $materia = $request->query->get('materia');
        $filtros = [];

        try {
            if ($tab === 'cortes') {
                $filtros = $this->filtros($request, ['busqueda', 'tipo', 'corte']);
                $datos = $this->api->get('movimientos/cortes', $filtros + ['vigencia' => $vigencia, 'page' => $page, 'limit' => 50]);
            } else {
                $filtros = $this->filtros($request, ['busqueda', 'estado_causa', 'tribunal']);
                $roles = $this->rolesCrm($empresa);
                if ($roles !== '') {
                    $consulta = $filtros + ($roles !== null ? ['roles' => $roles] : []);
                    $datos = $this->api->get('movimientos', $consulta + [
                        'materia' => $materia, 'vigencia' => $vigencia, 'page' => $page, 'limit' => 20,
                    ]);
                    $resumen = $this->api->get('movimientos/resumen', $consulta);
                }
            }
        } catch (\RuntimeException $e) {
            $error = $e->getMessage();
        }

        return $this->render('lexflow/movimientos.html.twig', [
            'tab' => $tab,
            'vigencia' => $vigencia,
            'vigencias' => self::VIGENCIAS,
            'tipos_corte' => self::TIPOS_CORTE,
            'materia' => $materia,
            'resumen' => $resumen,
            'datos' => $datos,
            'filtros' => $filtros,
            'error' => $error,
            'chips' => $this->chips($request, $filtros + ($vigencia === 'finalizadas' ? ['vigencia' => 'No vigentes'] : [])),
        ]);
    }

    // ───────────────────────── Audiencias ─────────────────────────

    #[Route('/audiencias', name: 'ed_audiencias_index', methods: ['GET'])]
    public function audiencias(Request $request): Response
    {
         $this->denyAccessUnlessGranted('view','ed_audiencias');
        $empresa = $this->usarEmpresa();
        $page = max(1, $request->query->getInt('page', 1));
        $materia = $request->query->get('materia');
        $pasadas = $request->query->getBoolean('incluir_pasadas');
        $filtros = $this->filtros($request, ['busqueda', 'tipo_audiencia', 'tribunal', 'desde', 'hasta']);
        $error = null;
        $datos = ['total' => 0, 'total_pages' => 1, 'page' => 1, 'audiencias' => []];
        $resumen = ['total' => 0, 'por_materia' => [], 'tipos_audiencia' => []];

        try {
            $roles = $this->rolesCrm($empresa);
            if ($roles !== '') {
                $base = $filtros + ($pasadas ? ['incluir_pasadas' => 'true'] : []) + ($roles !== null ? ['roles' => $roles] : []);
                $datos = $this->api->get('audiencias', $base + ['materia' => $materia, 'page' => $page, 'limit' => 50]);
                $resumen = $this->api->get('audiencias/resumen', $base);
            }
        } catch (\RuntimeException $e) {
            $error = $e->getMessage();
        }

        // Agrupa por día (la API ya entrega ordenado por fecha y hora).
        $grupos = [];
        foreach ($datos['audiencias'] ?? [] as $a) {
            $grupos[$a['fecha_audiencia']][] = $a;
        }

        return $this->render('lexflow/audiencias.html.twig', [
            'grupos' => $grupos,
            'hoy' => date('Y-m-d'),
            'datos' => $datos,
            'resumen' => $resumen,
            'materia' => $materia,
            'pasadas' => $pasadas,
            'filtros' => $filtros,
            'error' => $error,
            'chips' => $this->chips($request, $filtros),
        ]);
    }

    // ───────────────────────── helpers ─────────────────────────

    /**
     * Roles del CRM para limitar la consulta a edapi. null = sin restricción (interruptor
     * apagado); '' = restringido pero sin causas cruzables, y no hay que consultar
     * (edapi devolvería todo).
     */
    private function rolesCrm(Empresa $empresa): ?string
    {
        return $empresa->isLexflowSoloCrm() ? $this->causas->rolesParaEdapi($empresa->getId()) : null;
    }

    /** Toma del querystring solo los filtros permitidos y no vacíos. */
    private function filtros(Request $request, array $permitidos): array
    {
        $out = [];
        foreach ($permitidos as $k) {
            $v = trim((string) $request->query->get($k, ''));
            if ($v !== '') {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    /**
     * Badges de filtros aplicados; cada uno trae la query sin ese filtro
     * para poder quitarlo con un clic.
     */
    private function chips(Request $request, array $filtros, array $jurisdicciones = []): array
    {
        $etiquetas = [
            'busqueda' => 'Búsqueda', 'rut' => 'RUT', 'tribunal' => 'Tribunal', 'estado_causa' => 'Estado',
            'tipo' => 'Tipo', 'corte' => 'Corte', 'fecha_desde' => 'Desde', 'fecha_hasta' => 'Hasta',
            'desde' => 'Desde', 'hasta' => 'Hasta', 'tipo_audiencia' => 'Tipo', 'jurisdiccion' => 'Jurisdicción',
            'vigencia' => 'Vigencia',
        ];
        $query = $request->query->all();
        unset($query['page']);
        $chips = [];
        foreach ($filtros as $k => $v) {
            if ($k === 'jurisdiccion') {
                foreach ($jurisdicciones as $j) {
                    if ((string) $j['id'] === (string) $v) {
                        $v = $j['nombre'];
                    }
                }
            } elseif ($k === 'tipo') {
                $v = self::TIPOS_CORTE[$v] ?? $v;
            }
            $sin = $query;
            unset($sin[$k]);
            if ($k === 'fecha_desde') {
                $sin[$k] = ''; // vacío explícito, para que no vuelva a aplicarse el valor por defecto
            }
            $chips[] = ['etiqueta' => $etiquetas[$k] ?? $k, 'valor' => $v, 'query' => $sin];
        }
        return $chips;
    }
}
