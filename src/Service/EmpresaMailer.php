<?php

namespace App\Service;

use App\Entity\Empresa;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Address;

/**
 * Correo saliente con el SMTP configurado en cada empresa (Empresa::smtp*), en vez del MAILER_DSN global.
 * El remitente de los correos es el que se configura junto al SMTP.
 */
class EmpresaMailer
{
    /** El mailer de la empresa, o null si no tiene un SMTP utilizable. */
    public function mailer(Empresa $empresa): ?MailerInterface
    {
        if (!$empresa->tieneSmtp()) {
            return null;
        }

        // 'ssl' = SMTPS (TLS implícito); 'tls' = STARTTLS (se negocia solo); 'ninguno' = sin cifrado.
        $transporte = new EsmtpTransport($empresa->getSmtpHost(), (int) $empresa->getSmtpPuerto(), $empresa->getSmtpCifrado() === 'ssl');
        if ($empresa->getSmtpCifrado() === 'ninguno') {
            $transporte->setAutoTls(false);
        }
        $usuario = (string) $empresa->getSmtpUsuario();
        if ($usuario !== '') {
            $transporte->setUsername($usuario);
            $transporte->setPassword((string) $empresa->getSmtpPassword());
        }

        return new Mailer($transporte);
    }

    /** Remitente configurado en el SMTP de la empresa. */
    public function remitente(Empresa $empresa): Address
    {
        return new Address((string) $empresa->getSmtpRemitenteCorreo(), (string) $empresa->getSmtpRemitenteNombre());
    }
}
