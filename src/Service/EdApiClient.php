<?php

namespace App\Service;

use App\Entity\Empresa;
use App\Repository\CausaRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\PrivateKey;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Cliente de solo lectura de la API de Estado Diario (edapi.temposoft.cl).
 * Autentica con el header X-API-Key; X-Cliente-Guid es opcional (multi-cliente).
 */
class EdApiClient
{
    private Client $http;

    private string $apiKey = '';
    private string $clienteGuid = '';
    private string $llavePrivada = '';

    /** AAD con el que edapi sella las credenciales PJUD (formato v1). */
    private const AAD_CREDENCIALES = 'estado-diario/credenciales-pjud/v1';

    private const API_PJUD_TTL = 300;

    /** @var array<string,array{base_url:string,email:string,password:string,client_key:string}|null> */
    private array $memoApiPjud = [];

    public function __construct(private readonly string $baseUrl, private readonly CacheInterface $cache, private readonly ?LoggerInterface $logger = null)
    {
        $this->http = new Client(['base_uri' => rtrim($baseUrl, '/') . '/', 'timeout' => 30]);
    }

    /** Toma las credenciales configuradas en la Empresa. */
    public function paraEmpresa(?Empresa $empresa): self
    {
        $this->apiKey = trim((string) $empresa?->getEdapiKey());
        $this->clienteGuid = trim((string) $empresa?->getEdapiClienteGuid());
        $this->llavePrivada = trim((string) $empresa?->getEdapiLlavePrivada());
        return $this;
    }

    public function configurado(): bool
    {
        return $this->apiKey !== '';
    }

    /** @return array<string,mixed> */
    public function get(string $ruta, array $query = []): array
    {
        if (!$this->configurado()) {
            throw new \RuntimeException('La empresa no tiene API Key de Estado Diario (configúrela en Empresa)');
        }
        $headers = ['X-API-Key' => $this->apiKey, 'Accept' => 'application/json'];
        if ($this->clienteGuid !== '') {
            $headers['X-Cliente-Guid'] = $this->clienteGuid;
        }
        // Se omiten vacíos para no ensuciar la consulta.
        $query = array_filter($query, static fn($v) => $v !== null && $v !== '' && $v !== false);
        try {
            $resp = $this->http->get('api/v1/' . ltrim($ruta, '/'), ['headers' => $headers, 'query' => $query]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('No se pudo consultar Estado Diario: ' . $e->getMessage(), 0, $e);
        }
        return json_decode((string) $resp->getBody(), true) ?? [];
    }

    public function post(string $ruta, array $json = []): array
    {
        if (!$this->configurado()) {
            throw new \RuntimeException('La empresa no tiene API Key de Estado Diario (configúrela en Empresa)');
        }
        $headers = ['X-API-Key' => $this->apiKey, 'Accept' => 'application/json'];
        if ($this->clienteGuid !== '') {
            $headers['X-Cliente-Guid'] = $this->clienteGuid;
        }
        try {
            // Guzzle serializa [] como lista JSON y la API exige un objeto: sin datos, no se envía body.
            $opciones = ['headers' => $headers] + ($json ? ['json' => $json] : []);
            $resp = $this->http->post('api/v1/' . ltrim($ruta, '/'), $opciones);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('No se pudo actualizar en Estado Diario: ' . $e->getMessage(), 0, $e);
        }
        return json_decode((string) $resp->getBody(), true) ?? [];
    }

    /**
     * Crea una causa en Lexflow (POST api/v1/causas).
     *
     * @param array<string,string> $payload materia, rol, tribunal, tipo_causa, ruc, fecha_ingreso, caratulado, estado_causa, institucion
     * @return array{estado:'creada'|'duplicada'|'error',mensaje:string}
     */
    public function crearCausa(array $payload): array
    {
        if (!$this->configurado()) {
            return ['estado' => 'error', 'mensaje' => 'La empresa no tiene API Key de Estado Diario'];
        }
        $headers = ['X-API-Key' => $this->apiKey, 'Accept' => 'application/json'];
        if ($this->clienteGuid !== '') {
            $headers['X-Cliente-Guid'] = $this->clienteGuid;
        }
        try {
            $resp = $this->http->post('api/v1/causas', ['headers' => $headers, 'json' => $payload, 'http_errors' => false, 'timeout' => 10]);
        } catch (GuzzleException $e) {
            return ['estado' => 'error', 'mensaje' => 'No se pudo conectar con Lexflow: ' . mb_substr($e->getMessage(), 0, 160)];
        }
        $codigo = $resp->getStatusCode();
        if ($codigo >= 200 && $codigo < 300) {
            return ['estado' => 'creada', 'mensaje' => ''];
        }
        if ($codigo === 409) {
            return ['estado' => 'duplicada', 'mensaje' => 'La causa ya existe en Lexflow'];
        }
        $cuerpo = json_decode((string) $resp->getBody(), true);
        $detalle = is_array($cuerpo) ? ($cuerpo['detail'] ?? $cuerpo['mensaje'] ?? $cuerpo['message'] ?? null) : null;

        return ['estado' => 'error', 'mensaje' => match (true) {
            $codigo === 401 => 'Lexflow rechazó la API Key (inválida, revocada, vencida o IP no permitida)',
            $codigo === 403 => 'La API Key de Lexflow no tiene permiso para crear causas',
            is_string($detalle) && $detalle !== '' => 'Lexflow respondió ' . $codigo . ': ' . mb_substr($detalle, 0, 200),
            is_array($detalle) => 'Lexflow respondió ' . $codigo . ': ' . mb_substr((string) json_encode($detalle, JSON_UNESCAPED_UNICODE), 0, 200),
            default => 'Lexflow respondió con error ' . $codigo,
        }];
    }

    /**
     * Estado inicial del botón "Detalle PJUD" de varias causas por edapi: busca el id de cada una en Lexflow y pide
     * `pjud/estado` en paralelo (no llama a api-pjud). Las que no existen en Lexflow o fallan no vienen en el
     * resultado: quedan para el flujo directo de api-pjud. Nunca lanza.
     *
     * @param array<int|string,array{causa:array{tipo:string,rol:int,anio:int,tribunal:string}}> $specs
     * @return array<int|string,string> misma clave -> nuevo|sincronizando|error|listo
     */
    public function estadosPjud(array $specs): array
    {
        if (!$specs || !$this->configurado()) {
            return [];
        }
        $rolDe = static fn (array $s): string => $s['causa']['tipo'] . '-' . $s['causa']['rol'] . '-' . $s['causa']['anio'];
        try {
            $ids = $this->idsCausas(array_map(static fn (array $s) => [$rolDe($s), $s['causa']['tribunal']], array_values($specs)));
        } catch (\RuntimeException $e) {
            $this->logger?->info('PJUD edapi: no se pudo buscar las causas: ' . $e->getMessage());

            return [];
        }

        $headers = ['X-API-Key' => $this->apiKey, 'Accept' => 'application/json'];
        if ($this->clienteGuid !== '') {
            $headers['X-Cliente-Guid'] = $this->clienteGuid;
        }
        $promesas = [];
        foreach ($specs as $k => $s) {
            $id = $ids[CausaRepository::claveRol($rolDe($s), $s['causa']['tribunal'])] ?? null;
            if ($id !== null) {
                $promesas[$k] = $this->http->requestAsync('GET', 'api/v1/causas/' . $id . '/pjud/estado', ['headers' => $headers, 'http_errors' => false, 'timeout' => 8]);
            }
        }

        $estados = [];
        foreach (\GuzzleHttp\Promise\Utils::settle($promesas)->wait() as $k => $r) {
            if ($r['state'] !== 'fulfilled' || $r['value']->getStatusCode() !== 200) {
                continue;
            }
            $data = json_decode((string) $r['value']->getBody(), true);
            if (is_array($data)) {
                $estados[$k] = match ($data['estado'] ?? null) {
                    'sincronizando' => 'sincronizando',
                    'listo' => 'listo',
                    'error' => 'error',
                    default => 'nuevo', // sin_sincronizar
                };
            }
        }

        return $estados;
    }

    /**
     * Ids de edapi de las causas [rol, tribunal] que existen en Lexflow (caché de 1 minuto).
     *
     * @param list<array{0:string,1:string}> $causas
     * @return array<string,int> clave = CausaRepository::claveRol()
     * @throws \RuntimeException
     */
    public function idsCausas(array $causas): array
    {
        $filtros = [];
        foreach ($causas as [$rol, $tribunal]) {
            $filtros[strtoupper($rol) . '|' . trim(str_replace([',', '°'], [' ', 'º'], $tribunal))] = true;
        }
        $filtros = array_keys($filtros);
        sort($filtros);

        return $this->cache->get('edapi_causas_ids_' . md5($this->apiKey . '|' . $this->clienteGuid . '|' . implode(',', $filtros)), function (ItemInterface $item) use ($filtros): array {
            $item->expiresAfter(60);
            $ids = [];
            foreach (array_chunk($filtros, 40) as $tanda) {
                $page = 1;
                do {
                    $data = $this->get('causas', ['roles' => implode(',', $tanda), 'page' => $page, 'limit' => 50]);
                    foreach ($data['causas'] ?? [] as $c) {
                        $id = (int) ($c['id'] ?? $c['causa_id'] ?? 0);
                        if ($id > 0) {
                            $ids[CausaRepository::claveRol((string) ($c['rol'] ?? ''), (string) ($c['tribunal'] ?? ''))] = $id;
                        }
                    }
                } while (++$page <= (int) ($data['total_pages'] ?? 1));
            }

            return $ids;
        });
    }

    /**
     * Id con que edapi conoce la causa `rol` del `tribunal` (caché de 1 minuto), o null si no existe en Lexflow.
     *
     * @throws \RuntimeException
     */
    public function idCausa(string $rol, string $tribunal): ?int
    {
        // Mismo formato que credencialesPjud(): edapi compara el nombre exacto y usa "º".
        $filtro = strtoupper($rol) . '|' . trim(str_replace([',', '°'], [' ', 'º'], $tribunal));
        $objetivo = CausaRepository::claveRol($rol, $tribunal);

        $id = $this->cache->get('edapi_causa_id_' . md5($this->apiKey . '|' . $this->clienteGuid . '|' . $filtro), function (ItemInterface $item) use ($filtro, $objetivo): int {
            $item->expiresAfter(60);
            $data = $this->get('causas', ['roles' => $filtro, 'limit' => 50]);
            foreach ($data['causas'] ?? [] as $c) {
                if (CausaRepository::claveRol((string) ($c['rol'] ?? ''), (string) ($c['tribunal'] ?? '')) === $objetivo) {
                    return (int) ($c['id'] ?? $c['causa_id'] ?? 0);
                }
            }

            return 0;
        });

        return $id > 0 ? $id : null;
    }

    /**
     * Pide a edapi que sincronice la causa con el PJUD (POST causas/{id}/pjud/sincronizar). Responde de inmediato;
     * si ya había una en curso devuelve el mismo job con `ya_en_curso`. Requiere API key con permite_escritura.
     *
     * @return array<string,mixed>
     * @throws \RuntimeException con un mensaje apto para mostrar al usuario
     */
    public function pjudSincronizar(int $causaId): array
    {
        return $this->pjud('POST', 'causas/' . $causaId . '/pjud/sincronizar');
    }

    /**
     * Estado de la última sincronización (GET causas/{id}/pjud/estado): sin_sincronizar | sincronizando | listo | error
     * (el motivo viene en `mensaje_error`). No llama a api-pjud, se puede consultar seguido.
     *
     * @return array<string,mixed>
     * @throws \RuntimeException
     */
    public function pjudEstado(int $causaId): array
    {
        return $this->pjud('GET', 'causas/' . $causaId . '/pjud/estado');
    }

    /** @return array<string,mixed> */
    private function pjud(string $metodo, string $ruta): array
    {
        if (!$this->configurado()) {
            throw new \RuntimeException('La empresa no tiene API Key de Estado Diario (configúrela en Empresa)');
        }
        $headers = ['X-API-Key' => $this->apiKey, 'Accept' => 'application/json'];
        if ($this->clienteGuid !== '') {
            $headers['X-Cliente-Guid'] = $this->clienteGuid;
        }
        try {
            $resp = $this->http->request($metodo, 'api/v1/' . $ruta, ['headers' => $headers, 'http_errors' => false, 'timeout' => 10]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('No se pudo conectar con Lexflow: ' . mb_substr($e->getMessage(), 0, 160), 0, $e);
        }
        $codigo = $resp->getStatusCode();
        if ($codigo >= 200 && $codigo < 300) {
            $data = json_decode((string) $resp->getBody(), true);

            return is_array($data) ? $data : [];
        }

        throw new \RuntimeException(match ($codigo) {
            401 => 'Lexflow rechazó la API Key (inválida, revocada, vencida o IP del servidor no permitida)',
            403 => 'La API Key de Lexflow no permite sincronizar (requiere permiso de escritura) o el cliente no tiene PJUD habilitado',
            404 => 'La causa no existe en Lexflow',
            422 => 'La materia de la causa no se puede sincronizar con el PJUD',
            429 => 'Lexflow limitó las consultas; reintente en ' . ((int) $resp->getHeaderLine('Retry-After') ?: 'unos') . ' segundos',
            502 => 'Lexflow no pudo comunicarse con el PJUD',
            default => 'Lexflow respondió con error ' . $codigo,
        });
    }

    /** True si el texto es un PEM RSA privado que phpseclib puede cargar. */
    public static function llavePrivadaValida(string $pem): bool
    {
        return self::errorLlavePrivada($pem) === null;
    }

    /** Deja el PEM con saltos de línea \n reales (acepta "\n" literales, CRLF y texto antes/después del bloque). */
    public static function normalizarLlavePrivada(string $pem): string
    {
        $pem = str_replace(['\\r\\n', '\\n'], "\n", $pem);
        $pem = str_replace(["\r\n", "\r"], "\n", $pem);
        if (preg_match('/-----BEGIN [A-Z ]*PRIVATE KEY-----.*?-----END [A-Z ]*PRIVATE KEY-----/s', $pem, $m)) {
            $pem = $m[0];
        }

        return trim($pem);
    }

    /** Motivo por el que el PEM no sirve, o null si es una llave RSA privada cargable. */
    public static function errorLlavePrivada(string $pem): ?string
    {
        if (!str_contains($pem, 'PRIVATE KEY')) {
            return 'no se encontró un bloque "-----BEGIN ... PRIVATE KEY-----" (¿pegó la llave pública?)';
        }
        try {
            $llave = PublicKeyLoader::load($pem);
        } catch (\Throwable $e) {
            return 'no se pudo leer: ' . mb_substr($e->getMessage(), 0, 120);
        }

        return $llave instanceof PrivateKey ? null : 'la llave no es RSA privada (' . $llave::class . ')';
    }

    /**
     * Credenciales OJV (las que usa Lexflow) de la causa `rol` del `tribunal`, pedidas a edapi en el momento:
     * `credencial_ref` no es estable entre llamadas, así que no se puede guardar. La clave solo vive en memoria.
     *
     * @return array{rut:string,clave:string,metodo_login:int}
     * @throws \RuntimeException con un mensaje apto para mostrar al usuario
     */
    public function credencialesPjud(string $rol, string $tribunal): array
    {
        if (!$this->configurado()) {
            throw new \RuntimeException('La empresa no tiene API Key de Estado Diario (configúrela en Empresa)');
        }
        if ($this->llavePrivada === '' || !self::llavePrivadaValida($this->llavePrivada)) {
            throw new \RuntimeException('La empresa no tiene una llave privada válida para las credenciales de Lexflow (configúrela en Empresa)');
        }

        $headers = ['X-API-Key' => $this->apiKey, 'Accept' => 'application/json'];
        if ($this->clienteGuid !== '') {
            $headers['X-Cliente-Guid'] = $this->clienteGuid;
        }
        // Mismo formato que rolesParaEdapi(): edapi compara el nombre exacto y usa "º".
        $filtro = strtoupper($rol) . '|' . trim(str_replace([',', '°'], [' ', 'º'], $tribunal));
        try {
            $resp = $this->http->get('api/v1/causas', [
                'headers' => $headers,
                'query' => ['incluir_credenciales' => 'true', 'roles' => $filtro, 'limit' => 50],
                'http_errors' => false,
            ]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('No se pudo consultar las credenciales en Lexflow: ' . $e->getMessage(), 0, $e);
        }
        $codigo = $resp->getStatusCode();
        if ($codigo !== 200) {
            throw new \RuntimeException($this->mensajeErrorCredenciales($codigo));
        }
        $data = json_decode((string) $resp->getBody(), true);
        $causas = is_array($data) ? ($data['causas'] ?? []) : [];

        $objetivo = CausaRepository::claveRol($rol, $tribunal);
        $ref = null;
        foreach ($causas as $c) {
            if (CausaRepository::claveRol((string) ($c['rol'] ?? ''), (string) ($c['tribunal'] ?? '')) === $objetivo) {
                $ref = $c['credencial_ref'] ?? null;
                break;
            }
        }
        $sello = $ref !== null ? ($data['credenciales_pjud'][$ref] ?? null) : null;
        if (!is_string($sello) || $sello === '') {
            throw new \RuntimeException('Lexflow no tiene credenciales PJUD para esta causa (el RUT no calza con un abogado, calza con más de uno o el abogado no cargó su clave)');
        }

        return $this->abrirSello($sello);
    }

    /**
     * Conexión a api-pjud que Lexflow entrega en `api_pjud` (GET api/v1/causas?incluir_credenciales=true).
     * Es una sola por plataforma (no cambia entre llamadas), así que se cachea unos minutos.
     *
     * @return array{base_url:string,email:string,password:string,client_key:string}|null null = el cliente no tiene
     *         PJUD habilitado en Lexflow o la plataforma no tiene api-pjud configurada
     * @throws \RuntimeException con un mensaje apto para mostrar al usuario
     */
    public function conexionApiPjud(bool $ignorarHabilitado = false): ?array
    {
        if (!$this->configurado()) {
            throw new \RuntimeException('La empresa no tiene API Key de Estado Diario (configúrela en Empresa)');
        }
        if ($this->llavePrivada === '' || !self::llavePrivadaValida($this->llavePrivada)) {
            throw new \RuntimeException('La empresa no tiene una llave privada válida para las credenciales de Lexflow (configúrela en Empresa)');
        }
        $resumen = $this->resumenApiPjud();

        return ($resumen['habilitado'] || $ignorarHabilitado) && is_string($resumen['sello']) && $resumen['sello'] !== ''
            ? $this->abrirConexionApiPjud($resumen['sello'])
            : null;
    }

    /** @return array{habilitado:bool,sello:?string} */
    private function resumenApiPjud(): array
    {
        $clave = 'edapi_api_pjud_' . md5($this->apiKey . '|' . $this->clienteGuid . '|' . $this->baseUrl);
        if (!array_key_exists($clave, $this->memoApiPjud)) {
            $this->memoApiPjud[$clave] = $this->cache->get($clave, function (ItemInterface $item): array {
                $item->expiresAfter(self::API_PJUD_TTL);
                return $this->pedirApiPjud();
            });
        }

        return $this->memoApiPjud[$clave];
    }

    /**
     * True si el cliente tiene PJUD habilitado en Lexflow (caché de unos minutos). No necesita la llave privada.
     *
     * @throws \RuntimeException
     */
    public function pjudHabilitado(): bool
    {
        return $this->configurado() && $this->resumenApiPjud()['habilitado'];
    }

    /** Base del espejo de api-pjud de edapi: mismas rutas y JSON, autenticado con X-API-Key. */
    public function urlPjud(): string
    {
        return rtrim($this->baseUrl, '/') . '/api/v1/pjud';
    }

    /** @return array<string,string> */
    public function cabecerasApi(): array
    {
        $headers = ['X-API-Key' => $this->apiKey, 'Accept' => 'application/json'];
        if ($this->clienteGuid !== '') {
            $headers['X-Cliente-Guid'] = $this->clienteGuid;
        }

        return $headers;
    }

    /**
     * Cuáles de las causas [rol, tribunal] existen en Lexflow (caché de 1 minuto). Clave = CausaRepository::claveRol().
     *
     * @param list<array{0:string,1:string}> $causas
     * @return array<string,?bool> valor = `pjud_habilitado` de la causa (null si Lexflow no lo informa)
     * @throws \RuntimeException
     */
    public function causasExistentes(array $causas): array
    {
        $filtros = [];
        foreach ($causas as [$rol, $tribunal]) {
            // Mismo formato que credencialesPjud(): edapi compara el nombre exacto y usa "º".
            $filtros[strtoupper($rol) . '|' . trim(str_replace([',', '°'], [' ', 'º'], $tribunal))] = true;
        }
        $filtros = array_keys($filtros);
        sort($filtros);

        return $this->cache->get('edapi_causas_' . md5($this->apiKey . '|' . $this->clienteGuid . '|' . implode(',', $filtros)), function (ItemInterface $item) use ($filtros): array {
            $item->expiresAfter(60);
            $presentes = [];
            // roles viaja en la URL: se pide en tandas para no pasarse del largo permitido.
            foreach (array_chunk($filtros, 40) as $tanda) {
                $page = 1;
                do {
                    $data = $this->get('causas', ['roles' => implode(',', $tanda), 'page' => $page, 'limit' => 50]);
                    foreach ($data['causas'] ?? [] as $c) {
                        $this->logger?->info('PJUD trace: causa en Lexflow', ['rol' => $c['rol'] ?? null, 'tribunal' => $c['tribunal'] ?? null, 'pjud_habilitado' => $c['pjud_habilitado'] ?? '(ausente)']);
                        $presentes[CausaRepository::claveRol((string) ($c['rol'] ?? ''), (string) ($c['tribunal'] ?? ''))] = is_bool($c['pjud_habilitado'] ?? null) ? $c['pjud_habilitado'] : null;
                    }
                } while (++$page <= (int) ($data['total_pages'] ?? 1));
            }

            return $presentes;
        });
    }

    /** @return array{habilitado:bool,sello:?string} */
    private function pedirApiPjud(): array
    {
        $headers = ['X-API-Key' => $this->apiKey, 'Accept' => 'application/json'];
        if ($this->clienteGuid !== '') {
            $headers['X-Cliente-Guid'] = $this->clienteGuid;
        }
        try {
            $resp = $this->http->get('api/v1/causas', [
                'headers' => $headers,
                'query' => ['incluir_credenciales' => 'true', 'limit' => 1],
                'http_errors' => false,
            ]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('No se pudo consultar la conexión PJUD en Lexflow: ' . $e->getMessage(), 0, $e);
        }
        $codigo = $resp->getStatusCode();
        if ($codigo !== 200) {
            throw new \RuntimeException($this->mensajeErrorCredenciales($codigo));
        }
        $data = json_decode((string) $resp->getBody(), true);
        $data = is_array($data) ? $data : [];

        $this->logger?->info('PJUD trace: respuesta api/v1/causas (raíz)', [
            'pjud_habilitado_raiz' => $data['pjud_habilitado'] ?? '(ausente)',
            'trae_api_pjud' => is_string($data['api_pjud'] ?? null) && $data['api_pjud'] !== '',
            'claves_raiz' => array_keys($data),
            'claves_primera_causa' => array_keys($data['causas'][0] ?? []),
            'pjud_habilitado_primera_causa' => $data['causas'][0]['pjud_habilitado'] ?? '(ausente)',
        ]);

        return [
            'habilitado' => ($data['pjud_habilitado'] ?? false) === true,
            'sello' => is_string($data['api_pjud'] ?? null) ? $data['api_pjud'] : null,
        ];
    }

    /** @return array{base_url:string,email:string,password:string,client_key:string} */
    private function abrirConexionApiPjud(string $sello): array
    {
        $c = $this->abrirJson($sello);
        foreach (['base_url', 'email', 'password', 'client_key'] as $k) {
            if (!isset($c[$k]) || !is_string($c[$k]) || $c[$k] === '') {
                throw new \RuntimeException('La conexión PJUD que entrega Lexflow está incompleta (falta ' . $k . ')');
            }
        }

        return ['base_url' => $c['base_url'], 'email' => $c['email'], 'password' => $c['password'], 'client_key' => $c['client_key']];
    }

    private function mensajeErrorCredenciales(int $codigo): string
    {
        return match ($codigo) {
            401 => 'Lexflow rechazó la API Key (inválida, revocada, vencida o IP del servidor no permitida)',
            403 => 'La API Key de Lexflow no tiene permiso para entregar credenciales (faltan permiso, IPs o llave pública)',
            429 => 'Lexflow limitó las consultas; reintente en unos segundos',
            default => 'Lexflow respondió con error ' . $codigo . ' al pedir las credenciales',
        };
    }

    /**
     * Abre un sello de credenciales de un abogado: RSA-OAEP SHA-256 abre la llave AES-256 y AES-GCM abre el JSON
     * {rut, clave, metodo_login}. Ver abrirJson() para el formato.
     *
     * @return array{rut:string,clave:string,metodo_login:int}
     */
    public function abrirSello(string $sello): array
    {
        $cred = $this->abrirJson($sello);
        if (empty($cred['rut']) || empty($cred['clave'])) {
            throw new \RuntimeException('No se pudo abrir la credencial de Lexflow (sello alterado)');
        }

        return ['rut' => (string) $cred['rut'], 'clave' => (string) $cred['clave'], 'metodo_login' => (int) ($cred['metodo_login'] ?? 1)];
    }

    /**
     * Abre un sello v1.<llave AES>.<nonce>.<cifrado> (base64 URL-safe sin relleno): RSA-OAEP SHA-256 abre la
     * llave AES-256 y AES-GCM (AAD fijo) abre el JSON.
     *
     * @return array<string,mixed>
     */
    private function abrirJson(string $sello): array
    {
        $partes = explode('.', $sello);
        if (count($partes) !== 4 || $partes[0] !== 'v1') {
            throw new \RuntimeException('Formato de credencial de Lexflow no reconocido');
        }
        [, $envuelta, $nonce, $cifrado] = array_map(static fn(string $t) => base64_decode(strtr($t, '-_', '+/') . str_repeat('=', (4 - strlen($t) % 4) % 4), true), $partes);
        if ($envuelta === false || $nonce === false || $cifrado === false || strlen($cifrado) < 16) {
            throw new \RuntimeException('Credencial de Lexflow corrupta');
        }
        try {
            $llave = PublicKeyLoader::load($this->llavePrivada)
                ->withPadding(RSA::ENCRYPTION_OAEP)->withHash('sha256')->withMGFHash('sha256');
            $dek = $llave->decrypt($envuelta);
        } catch (\Throwable) {
            $dek = false;
        }
        if (!is_string($dek) || strlen($dek) !== 32) {
            throw new \RuntimeException('No se pudo abrir la credencial de Lexflow: la llave privada no corresponde a la pública registrada en edapi');
        }
        $json = openssl_decrypt(substr($cifrado, 0, -16), 'aes-256-gcm', $dek, OPENSSL_RAW_DATA, $nonce, substr($cifrado, -16), self::AAD_CREDENCIALES);
        $datos = $json === false ? null : json_decode($json, true);
        if (!is_array($datos)) {
            throw new \RuntimeException('No se pudo abrir la credencial de Lexflow (sello alterado)');
        }

        return $datos;
    }
}
