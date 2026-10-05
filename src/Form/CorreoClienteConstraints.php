<?php

namespace App\Form;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Restricciones del correo de un cliente: obligatorio y con formato válido (usuario@dominio.ext).
 * Se usan en todos los formularios que crean o modifican un Cliente, para que el formato se valide
 * en el servidor aunque el navegador no lo haga.
 */
final class CorreoClienteConstraints
{
    /** @return Constraint[] */
    public static function restricciones(): array
    {
        return [
            new NotBlank(message: 'Debe ingresar el correo del cliente.'),
            new Length(max: 254, maxMessage: 'El correo es demasiado largo.'),
            new Email(message: 'El correo ingresado no tiene un formato válido (ej. nombre@dominio.cl).', mode: Email::VALIDATION_MODE_HTML5),
            // El modo HTML5 acepta "usuario@localhost": se exige además un dominio con punto y extensión de 2+ letras.
            new Regex(pattern: '/^[^@\s]+@[^@\s]+\.[A-Za-z]{2,}$/', message: 'El correo ingresado no tiene un formato válido (ej. nombre@dominio.cl).'),
        ];
    }
}
