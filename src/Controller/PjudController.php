<?php

namespace App\Controller;

use App\Entity\Causa;
use App\Repository\CausaRepository;
use App\Repository\EmpresaRepository;
use App\Service\PjudApiClient;
use App\Service\PjudApiException;
use App\Service\PjudNoEncontrado;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Endpoints JSON del popup global "Detalle PJUD" (templates/pjud/_popup.html.twig).
 * Lo consume cualquier módulo: basta un botón con data-pjud-causa="<id de Causa del CRM>".
 */
#[Route('/pjud')]
class PjudController extends AbstractController
{
    public function __construct(
        private readonly PjudApiClient $api,
        private readonly CausaRepository $causas,
        private readonly EmpresaRepository $empresas,
        private readonly \Psr\Log\LoggerInterface $logger,
    ) {
    }

    /** Materia del CRM (texto libre por empresa) -> competencia de api-pjud, o null si no aplica. */
    public static function competencia(?string $materia): ?string
    {
        $m = mb_strtolower((string) $materia);
        foreach (PjudApiClient::MATERIAS as $c) {
            if (str_contains($m, $c)) {
                return $c;
            }
        }
        return null;
    }

    #[Route('/causa/{id}', name: 'pjud_causa_detalle', requirements: ['id' => '[A-Za-z0-9_.\-]+'], methods: ['GET'])]
    public function detalle(string $id, Request $request): JsonResponse
    {
        return $this->consultar($id, $request, false);
    }

    /** Pide al PJUD que sincronice de nuevo (botón Actualizar / Reintentar). Dispara un scrape, por eso POST + CSRF. */
    #[Route('/causa/{id}/actualizar', name: 'pjud_causa_actualizar', requirements: ['id' => '[A-Za-z0-9_.\-]+'], methods: ['POST'])]
    public function actualizar(string $id, Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('pjud', (string) $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['detail' => 'Token inválido, recargue la página'], 400);
        }
        return $this->consultar($id, $request, true);
    }

    #[Route('/documento', name: 'pjud_documento', methods: ['GET'])]
    public function documento(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $empresa = $this->empresas->find($this->getUser()->getEmpresaActual());
        if (!$empresa?->isPjudConfigurado()) {
            throw $this->createNotFoundException();
        }
        $this->api->paraEmpresa($empresa);
        try {
            $upstream = $this->api->abrirDocumento((string) $request->query->get('url'));
        } catch (PjudNoEncontrado) {
            throw $this->createNotFoundException('Documento no encontrado en el PJUD');
        } catch (PjudApiException $e) {
            return new Response($e->getMessage(), 502);
        }

        $cuerpo = $upstream->getBody();
        $tipo = $upstream->getHeaderLine('Content-Type') ?: 'application/pdf';
        return new StreamedResponse(static function () use ($cuerpo) {
            while (!$cuerpo->eof()) {
                echo $cuerpo->read(8192);
                flush();
            }
        }, 200, ['Content-Type' => $tipo, 'Content-Disposition' => 'inline', 'X-Content-Type-Options' => 'nosniff']);
    }

    /**
     * El popup espera siempre JSON: cualquier fallo (permiso, error de PHP, etc.) se devuelve como
     * {detail: ...} para que el usuario vea el motivo en vez de "Respuesta ilegible del servidor".
     */
    private function consultar(string $id, Request $request, bool $forzar): JsonResponse
    {
        try {
            return $this->consultarCausa($id, $request, $forzar);
        } catch (\Symfony\Component\Security\Core\Exception\AccessDeniedException) {
            return new JsonResponse(['detail' => 'No tiene permiso para ver el detalle PJUD.'], 403);
        } catch (\Throwable $e) {
            $this->logger->error('Detalle PJUD (' . substr($id, 0, 20) . '): ' . $e::class . ': ' . $e->getMessage(), ['exception' => $e]);

            return new JsonResponse(['detail' => 'Error interno al consultar el detalle PJUD: ' . $e::class . ' - ' . mb_substr($e->getMessage(), 0, 200)], 500);
        }
    }

    private function consultarCausa(string $id, Request $request, bool $forzar): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        $empresa = $this->empresas->find($user->getEmpresaActual());
        if (!$empresa?->isPjudConfigurado()) {
            return new JsonResponse(['detail' => 'La empresa no tiene configurada la API PJUD'], 404);
        }

        // Fila de Lexflow que no está en el CRM: el id es un token con rol, tribunal y materia (ver tokenExterno()).
        if (!ctype_digit($id)) {
            return $this->consultarExterna($id, $request, $forzar, $empresa, $user);
        }

        $causa = $this->causas->find((int) $id);
        if (!$causa || $causa->getAgenda()?->getEmpresa()?->getId() !== $empresa->getId()) {
            return new JsonResponse(['detail' => 'Causa no encontrada'], 404);
        }
        $competencia = self::competencia($causa->getMateria()?->getNombre());
        if ($competencia === null) {
            return new JsonResponse(['detail' => 'El detalle PJUD solo está disponible para causas Civil, Familia, Laboral, Cobranza o Penal'], 422);
        }
        $datos = $this->datosCausa($causa);
        if ($datos === null) {
            return new JsonResponse(['detail' => 'La causa no tiene letra, rol, año y juzgado completos en el CRM'], 422);
        }

        $this->api->paraEmpresa($empresa);
        $cuaderno = $request->query->get('cuaderno');
        try {
            $res = $this->api->detalle($competencia, $datos, $forzar, $cuaderno !== null ? (int) $cuaderno : null, $this->credenciales($user, $causa));
        } catch (PjudApiException $e) {
            return new JsonResponse(['detail' => $e->getMessage()], 502);
        }

        $res['materia'] = $competencia;
        $res['crm'] = ['rol' => $datos['tipo'] . '-' . $datos['rol'] . '-' . $datos['anio'], 'tribunal' => $datos['tribunal']];
        return new JsonResponse($res);
    }

    /**
     * Competencia de api-pjud (civil, familia, laboral, cobranza, penal) de una fila de Lexflow: primero la
     * jurisdicción (que equivale a la materia) y, si no la trae, el nombre del tribunal. null = no soportada
     * (p. ej. Policía Local).
     */
    public static function competenciaDeFila(array $fila): ?string
    {
        $c = self::competencia((string) ($fila['jurisdiccion'] ?? ''));
        if ($c !== null) {
            return $c;
        }
        $tribunal = mb_strtolower((string) ($fila['tribunal'] ?? ''));
        return match (true) {
            str_contains($tribunal, 'cobranza') => 'cobranza',
            str_contains($tribunal, 'familia') => 'familia',
            str_contains($tribunal, 'trabajo') => 'laboral',
            str_contains($tribunal, 'garant'), str_contains($tribunal, 'juicio oral'), str_contains($tribunal, 'penal') => 'penal',
            str_contains($tribunal, 'civil') => 'civil',
            default => null,
        };
    }

    /**
     * Identificador para el botón "Detalle PJUD" de una fila de Lexflow que NO coincide con una causa del CRM.
     * Lleva rol, tribunal, competencia y tipo de causa en base64url; null si la fila no se puede consultar
     * (competencia no soportada o rol sin formato LETRA-NÚMERO-AÑO).
     */
    public static function tokenExterno(array $fila): ?string
    {
        $competencia = self::competenciaDeFila($fila);
        $rol = trim((string) ($fila['rol'] ?? ''));
        $tribunal = trim((string) ($fila['tribunal'] ?? ''));
        if ($competencia === null || $tribunal === '' || !preg_match('/^[A-Za-z]+-\d+-\d{4}$/', $rol)) {
            return null;
        }
        $json = json_encode(['r' => $rol, 't' => $tribunal, 'm' => $competencia, 'c' => $fila['tipo_causa'] ?? null], JSON_UNESCAPED_UNICODE);

        return 'x.' . rtrim(strtr(base64_encode((string) $json), '+/', '-_'), '=');
    }

    /**
     * Detalle PJUD de una causa que no está en el CRM (fila de Lexflow). No hay cliente asociado, así que
     * se sincroniza con las credenciales de la empresa.
     */
    private function consultarExterna(string $token, Request $request, bool $forzar, \App\Entity\Empresa $empresa, object $user): JsonResponse
    {
        if (!$this->isGranted('view', 'ed_estado_diario') && !$this->isGranted('view', 'ed_causas')) {
            throw $this->createAccessDeniedException();
        }
        if (!$empresa->isLexflowHabilitado()) {
            return new JsonResponse(['detail' => 'Lexflow no está habilitado para esta empresa'], 404);
        }
        $d = str_starts_with($token, 'x.') && strlen($token) < 1500
            ? json_decode((string) base64_decode(strtr(substr($token, 2), '-_', '+/')), true)
            : null;
        if (!is_array($d) || !isset($d['r'], $d['t'], $d['m']) || !in_array($d['m'], PjudApiClient::MATERIAS, true)) {
            return new JsonResponse(['detail' => 'Identificador de causa inválido'], 422);
        }
        if (!preg_match('/^([A-Za-z]+)-(\d+)-(\d{4})$/', (string) $d['r'], $m)) {
            return new JsonResponse(['detail' => 'El rol de la causa no tiene el formato LETRA-NÚMERO-AÑO'], 422);
        }
        $datos = [
            'tipo' => strtoupper($m[1]),
            'rol' => (int) $m[2],
            'anio' => (int) $m[3],
            'tribunal' => (string) $d['t'],
            'tipo_causa' => isset($d['c']) ? (string) $d['c'] : null,
        ];

        $this->api->paraEmpresa($empresa);
        $cuaderno = $request->query->get('cuaderno');
        try {
            $res = $this->api->detalle($d['m'], $datos, $forzar, $cuaderno !== null ? (int) $cuaderno : null, $this->credencialesEmpresa($user));
        } catch (PjudApiException $e) {
            return new JsonResponse(['detail' => $e->getMessage()], 502);
        }

        $res['materia'] = $d['m'];
        $res['crm'] = ['rol' => $datos['tipo'] . '-' . $datos['rol'] . '-' . $datos['anio'], 'tribunal' => $datos['tribunal']];
        return new JsonResponse($res);
    }
    /**
     * Credenciales OJV para sincronizar: la causa es del CRM, así que primero las del cliente
     * asociado a la causa (RUT + ClaveÚnica); si el cliente no las tiene completas, las del
     * usuario del sistema.
     *
     * @return array{rut:?string,clave:?string,metodo_login:int}
     */
    private function credenciales(object $user, Causa $causa): array
    {
        $cliente = $causa->getCliente() ?? $causa->getAgenda()?->getContrato()?->getCliente();
        if ($cliente && $cliente->getRut() && $cliente->getClaveUnica()) {
            return ['rut' => $cliente->getRut(), 'clave' => $cliente->getClaveUnica(), 'metodo_login' => 2];
        }
        return $this->credencialesEmpresa($user);
    }

    /**
     * Clave del Poder Judicial de la empresa (Mantención → Clave Poder Judicial).
     *
     * @return array{rut:?string,clave:?string,metodo_login:int}
     */
    private function credencialesEmpresa(object $user): array
    {
        $empresa = $this->empresas->find($user->getEmpresaActual());
        if ($empresa && $empresa->getPjudRut() && $empresa->getPjudClave()) {
            return ['rut' => $empresa->getPjudRut(), 'clave' => $empresa->getPjudClave(), 'metodo_login' => $empresa->getPjudMetodoLogin()];
        }
        return ['rut' => null, 'clave' => null, 'metodo_login' => 1];
    }

    /** @return array{tipo:string,rol:int,anio:int,tribunal:string,tipo_causa:?string}|null */
    private function datosCausa(Causa $c): ?array
    {
        $tribunal = $c->getJuzgado()?->getNombre();
        if (!$c->getLetra() || !ctype_digit(trim((string) $c->getRol())) || !$c->getAnio() || !$tribunal) {
            return null;
        }
        return [
            'tipo' => strtoupper(trim($c->getLetra())),
            'rol' => (int) trim((string) $c->getRol()),
            'anio' => $c->getAnio(),
            'tribunal' => $tribunal,
            'tipo_causa' => null,
        ];
    }
}
