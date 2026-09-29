<?php

namespace App\Service;

use GuzzleHttp\Client;

class RecaptchaService
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    private string $secretKey;

    public function __construct(string $recaptchaSecretKey)
    {
        $this->secretKey = $recaptchaSecretKey;
    }

    /**
     * Verifica el token reCAPTCHA contra la API de Google.
     *
     * @param string $token Token enviado por el cliente (g-recaptcha-response)
     * @param string $ip    IP del cliente
     */
    public function verificar(string $token, string $ip): bool
    {
        if (empty($token)) {
            return false;
        }

        try {
            $client   = new Client(['timeout' => 5]);
            $response = $client->post(self::VERIFY_URL, [
                'form_params' => [
                    'secret'   => $this->secretKey,
                    'response' => $token,
                    'remoteip' => $ip,
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);

            return isset($data['success']) && $data['success'] === true;
        } catch (\Throwable $e) {
            // Si la llamada falla, bloqueamos por seguridad
            return false;
        }
    }
}
