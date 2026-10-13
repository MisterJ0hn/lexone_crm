<?php

namespace App\Service;

use App\Entity\Causa;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Crea en Lexflow (edapi, POST /api/v1/causas) las causas que se crean en el CRM.
 *
 * Solo se envía si la empresa tiene Lexflow habilitado y API Key, y la causa trae rol completo
 * (letra-número-año), corte y tribunal. Nunca bloquea: si Lexflow falla, la causa del CRM queda
 * creada, se deja un aviso al usuario y se registra en el log. Una causa duplicada (409) se ignora.
 */
class LexflowCausaCreator
{
    public function __construct(
        private readonly EdApiClient $api,
        private readonly LoggerInterface $logger,
        private readonly RequestStack $requestStack,
    ) {
    }

    /** La causa debe estar persistida (con su agenda/empresa). */
    public function enviar(Causa $causa): void
    {
        $payload = $this->payload($causa);
        if ($payload === null) {
            return;
        }
        $empresa = $causa->getAgenda()?->getEmpresa();
        if (!$empresa?->isLexflowHabilitado()) {
            return;
        }
        return; // Se deshabilita temporalmente la creación de causas en Lexflow, hasta que se resuelvan los problemas de duplicados y de roles incompletos.
        /*$res = $this->api->paraEmpresa($empresa)->crearCausa($payload);

        if ($res['estado'] === 'error') {
            $this->logger->warning('Lexflow: no se pudo crear la causa ' . $payload['rol'] . ' (id ' . $causa->getId() . '): ' . $res['mensaje']);
            $session = $this->requestStack->getSession();
            if (method_exists($session, 'getFlashBag')) {
                $session->getFlashBag()->add('error', 'La causa ' . $payload['rol'] . ' se creó, pero no se pudo crear en Lexflow: ' . $res['mensaje']);
            }
        }*/
    }

    /**
     * Cuerpo para edapi, o null si la causa no cumple lo mínimo (rol completo, corte y tribunal).
     *
     * @return array<string,string>|null
     */
    public function payload(Causa $causa): ?array
    {
        $letra = strtoupper(trim((string) $causa->getLetra()));
        $rol = trim((string) $causa->getRol());
        $anio = (int) $causa->getAnio();
        $juzgado = $causa->getJuzgado();
        $materia = $causa->getMateria()?->getNombre();
        if ($letra === '' || $rol === '' || $anio <= 0 || $causa->getCorte() === null || $juzgado === null || !$materia) {
            return null;
        }

        return [
            'materia' => $materia,
            'rol' => $letra . '-' . $rol . '-' . $anio,
            // Mismo formato que usa edapi al cruzar roles (ver CausaRepository::rolesParaEdapi): sin comas y con "º".
            'tribunal' => trim(str_replace([',', '°'], [' ', 'º'], (string) $juzgado->getNombre())),
            'tipo_causa' => '',
            'ruc' => '',
            'fecha_ingreso' => date('Y-m-d'),
            'caratulado' => (string) $causa->getCausaNombre(),
            'estado_causa' => '',
            'institucion' => '',
        ];
    }
}
