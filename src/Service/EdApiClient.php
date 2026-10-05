<?php

namespace App\Service;

use App\Entity\Empresa;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Cliente de solo lectura de la API de Estado Diario (edapi.temposoft.cl).
 * Autentica con el header X-API-Key; X-Cliente-Guid es opcional (multi-cliente).
 */
class EdApiClient
{
    private Client $http;

    private string $apiKey = '';
    private string $clienteGuid = '';

    public function __construct(private readonly string $baseUrl)
    {
        $this->http = new Client(['base_uri' => rtrim($baseUrl, '/') . '/', 'timeout' => 30]);
    }

    /** Toma las credenciales configuradas en la Empresa. */
    public function paraEmpresa(?Empresa $empresa): self
    {
        $this->apiKey = trim((string) $empresa?->getEdapiKey());
        $this->clienteGuid = trim((string) $empresa?->getEdapiClienteGuid());
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
}
