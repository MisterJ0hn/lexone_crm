<?php

namespace App\Security;

/**
 * Utilidad estática de cifrado/hash para datos sensibles de Cliente (rut, teléfono,
 * correo, dirección, clave única).
 *
 * Es estática (no un servicio inyectable) porque los tipos custom de Doctrine
 * (ver App\Doctrine\EncryptedStringType) son instanciados por el propio Doctrine,
 * fuera del contenedor de servicios, así que no pueden recibir dependencias por DI.
 *
 * - encrypt()/decrypt(): AES-256-GCM reversible, para poder mostrar/reusar el valor real
 *   (ej. clave_unica se necesita en texto plano para automatizar un login externo).
 * - hash(): HMAC-SHA256 determinístico, solo para poder buscar por igualdad exacta
 *   (WHERE xxx_hash = :hash) sin tener que desencriptar toda la tabla.
 */
class Cifrado
{
    private static function claveCifrado(): string
    {
        $b64 = $_ENV['APP_ENCRYPTION_KEY'] ?? getenv('APP_ENCRYPTION_KEY');

        if (empty($b64)) {
            throw new \RuntimeException('APP_ENCRYPTION_KEY no está configurado');
        }

        $clave = base64_decode($b64, true);

        if ($clave === false || strlen($clave) !== 32) {
            throw new \RuntimeException('APP_ENCRYPTION_KEY debe ser una clave de 32 bytes codificada en base64');
        }

        return $clave;
    }

    private static function peperHash(): string
    {
        $pepper = $_ENV['APP_HASH_PEPPER'] ?? getenv('APP_HASH_PEPPER');

        if (empty($pepper)) {
            throw new \RuntimeException('APP_HASH_PEPPER no está configurado');
        }

        return $pepper;
    }

    public static function encrypt(?string $valorPlano): ?string
    {
        if ($valorPlano === null || $valorPlano === '') {
            return $valorPlano;
        }

        $iv = random_bytes(12);
        $tag = '';

        $cifrado = openssl_encrypt($valorPlano, 'aes-256-gcm', self::claveCifrado(), OPENSSL_RAW_DATA, $iv, $tag, '', 16);

        if ($cifrado === false) {
            throw new \RuntimeException('No se pudo cifrar el valor');
        }

        return base64_encode($iv . $tag . $cifrado);
    }

    /**
     * Heurística usada por el backfill (app:cifrar-datos-cliente) para no volver a
     * cifrar un valor que ya quedó cifrado en una corrida anterior (idempotencia).
     */
    public static function pareceCifrado(?string $valor): bool
    {
        if ($valor === null || $valor === '') {
            return true;
        }

        $raw = base64_decode($valor, true);

        return $raw !== false && strlen($raw) >= 29 && base64_encode($raw) === $valor;
    }

    public static function decrypt(?string $valorCifrado): ?string
    {
        if ($valorCifrado === null || $valorCifrado === '') {
            return $valorCifrado;
        }

        $raw = base64_decode($valorCifrado, true);

        if ($raw === false || strlen($raw) < 29) {
            // No parece un valor cifrado por esta clase (ej. dato legado en texto plano
            // de antes de la migración): se devuelve tal cual en vez de reventar.
            return $valorCifrado;
        }

        $iv = substr($raw, 0, 12);
        $tag = substr($raw, 12, 16);
        $cifrado = substr($raw, 28);

        $plano = openssl_decrypt($cifrado, 'aes-256-gcm', self::claveCifrado(), OPENSSL_RAW_DATA, $iv, $tag);

        return $plano === false ? null : $plano;
    }

    /**
     * Hash determinístico (HMAC-SHA256, hex de 64 caracteres) para búsquedas por
     * igualdad exacta. El valor se normaliza antes de hashear para que variaciones
     * de formato (espacios, puntos, mayúsculas) del mismo dato produzcan el mismo hash.
     */
    public static function hash(?string $valorPlano, string $normalizacion = 'ninguna'): ?string
    {
        if ($valorPlano === null || $valorPlano === '') {
            return null;
        }

        $normalizado = self::normalizar($valorPlano, $normalizacion);

        return hash_hmac('sha256', $normalizado, self::peperHash());
    }

    private static function normalizar(string $valor, string $tipo): string
    {
        switch ($tipo) {
            case 'rut':
                $valor = strtoupper(str_replace(['.', ' '], '', trim($valor)));

                return $valor;
            case 'telefono':
                return preg_replace('/\D+/', '', $valor);
            case 'correo':
                return mb_strtolower(trim($valor));
            default:
                return trim($valor);
        }
    }
}
