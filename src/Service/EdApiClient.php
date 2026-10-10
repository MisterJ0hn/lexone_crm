<?php

namespace App\Service;

use App\Entity\Empresa;
use App\Repository\CausaRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\PrivateKey;

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

    public function __construct(private readonly string $baseUrl)
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

    /** True si el texto es un PEM RSA privado que phpseclib puede cargar. */
    public static function llavePrivadaValida(string $pem): bool
    {
        try {
            return PublicKeyLoader::load($pem) instanceof PrivateKey;
        } catch (\Throwable) {
            return false;
        }
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
            throw new \RuntimeException(match ($codigo) {
                401 => 'Lexflow rechazó la API Key (inválida, revocada, vencida o IP del servidor no permitida)',
                403 => 'La API Key de Lexflow no tiene permiso para entregar credenciales (faltan permiso, IPs o llave pública)',
                429 => 'Lexflow limitó las consultas; reintente en unos segundos',
                default => 'Lexflow respondió con error ' . $codigo . ' al pedir las credenciales',
            });
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
     * Abre un sello v1.<llave AES>.<nonce>.<cifrado> (base64 URL-safe sin relleno): RSA-OAEP SHA-256
     * abre la llave AES-256 y AES-GCM (AAD fijo) abre el JSON {rut, clave, metodo_login}.
     *
     * @return array{rut:string,clave:string,metodo_login:int}
     */
    public function abrirSello(string $sello): array
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
        $cred = $json === false ? null : json_decode($json, true);
        if (!is_array($cred) || empty($cred['rut']) || empty($cred['clave'])) {
            throw new \RuntimeException('No se pudo abrir la credencial de Lexflow (sello alterado)');
        }

        return ['rut' => (string) $cred['rut'], 'clave' => (string) $cred['clave'], 'metodo_login' => (int) ($cred['metodo_login'] ?? 1)];
    }
}
