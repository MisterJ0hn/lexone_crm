<?php

namespace App\Service;

use App\Entity\Contrato;
use App\Repository\CorreoBienvenidaRepository;
use App\Repository\TipoClienteRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Envía el correo de bienvenida al crear un contrato, usando el template HTML
 * configurado por la empresa para el tipo de cliente de la agenda.
 */
class CorreoBienvenidaService
{
    public function __construct(
        private readonly CorreoBienvenidaRepository $correos,
        private readonly TipoClienteRepository $tiposCliente,
        private readonly ContratoTemplateRenderer $renderer,
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
        #[Autowire('%kernel.project_dir%/var/correo_bienvenida')] private readonly string $directorioImagenes,
    ) {
    }

    /**
     * Valores de muestra de cada variable (sin escapar), para la vista previa y el envío de prueba.
     * '{{causas}}' ya es HTML.
     *
     * @return array<string,string>
     */
    public static function ejemplo(string $tipoCliente): array
    {
        $celda = 'border:1px solid #ccc;padding:4px';

        return [
            '{{cliente_nombre}}' => 'María González Pérez',
            '{{cliente_rut}}' => '12.345.678-9',
            '{{cliente_email}}' => 'maria.gonzalez@correo.cl',
            '{{cliente_telefono}}' => '+56 9 1234 5678',
            '{{cliente_direccion}}' => 'Av. Providencia 1234, Santiago',
            '{{tipo_contrato}}' => $tipoCliente,
            '{{folio}}' => '1234',
            '{{cuotas}}' => '12',
            '{{monto_contrato}}' => '1.200.000',
            '{{fecha_contrato}}' => date('d-m-Y'),
            '{{causas}}' => '<table style="border-collapse:collapse;width:100%"><thead><tr>'
                . '<th style="' . $celda . '">Materia</th><th style="' . $celda . '">Rol</th><th style="' . $celda . '">Tribunal</th>'
                . '</tr></thead><tbody><tr>'
                . '<td style="' . $celda . '">Familia</td><td style="' . $celda . '">C-1234-2026</td><td style="' . $celda . '">1° Juzgado de Familia de Santiago</td>'
                . '</tr></tbody></table>',
        ];
    }

    /**
     * Escapa los valores para insertarlos en el HTML del correo (no se inyecta HTML de datos del cliente);
     * '{{causas}}' se deja tal cual porque ya es HTML.
     *
     * @param array<string,string> $valores
     * @return array<string,string>
     */
    public static function escapar(array $valores): array
    {
        $seguras = [];
        foreach ($valores as $token => $valor) {
            $seguras[$token] = $token === '{{causas}}' ? $valor : htmlspecialchars((string) $valor, ENT_QUOTES);
        }

        return $seguras;
    }

    /**
     * Envía un correo de prueba con datos de ejemplo al destino indicado, con el contenido que se está
     * editando (no hace falta haberlo guardado). Nunca lanza.
     *
     * @return string|null null si se envió; si no, el motivo legible.
     */
    public function enviarPrueba(string $destino, string $desdeCorreo, ?string $desdeNombre, string $asunto, string $html, ?string $rutaImagen, string $tipoCliente): ?string
    {
        $crudo = self::ejemplo($tipoCliente);
        $seguras = self::escapar($crudo);
        $conImagen = $rutaImagen !== null && is_file($rutaImagen);
        $seguras['{{imagen}}'] = $conImagen ? 'cid:imagen' : '';

        try {
            $email = (new Email())
                ->from(new Address($desdeCorreo, (string) $desdeNombre))
                ->to($destino)
                ->subject('[PRUEBA] ' . strtr($asunto, $crudo))
                ->html(strtr($html, $seguras));
            if ($conImagen) {
                $email->embedFromPath($rutaImagen, 'imagen');
            }
            $this->mailer->send($email);
            $this->logger->info('Correo de bienvenida de prueba enviado a ' . $destino);

            return null;
        } catch (\Throwable $e) {
            $this->logger->error('No se pudo enviar el correo de bienvenida de prueba a ' . $destino . ': ' . $e->getMessage());

            return 'error al enviar: ' . mb_substr($e->getMessage(), 0, 200);
        }
    }
    /**
     * Intenta enviar el correo y deja el motivo en el log si no se envía (nunca lanza).
     *
     * @return string|null null si se envió; si no, el motivo legible (para mostrarlo al usuario).
     */
    public function enviar(Contrato $contrato): ?string
    {
        $motivo = $this->intentar($contrato);
        if ($motivo !== null) {
            $this->logger->warning('Correo de bienvenida del contrato ' . $contrato->getId() . ' no enviado: ' . $motivo);
        }
        return $motivo;
    }

    private function intentar(Contrato $contrato): ?string
    {
        $agenda = $contrato->getAgenda();
        $destino = trim((string) $contrato->getCliente()?->getCorreo());
        if (!$agenda || !$agenda->getEmpresa() || !filter_var($destino, FILTER_VALIDATE_EMAIL)) {
            return 'el cliente no tiene un correo válido.';
        }

        $tipo = $agenda->getTipoCliente() ?? $this->tiposCliente->findOneByNombre('Persona');
        $config = $tipo ? $this->correos->findOneBy(['empresa' => $agenda->getEmpresa(), 'tipoCliente' => $tipo, 'activo' => true]) : null;
        if (!$config || trim((string) $config->getContenido()) === '') {
            return 'no hay un correo de bienvenida activo con contenido para el tipo de cliente ' . ($tipo ?? 'Persona') . ' (Plantillas de Contrato → Correo de bienvenida).';
        }

        $variables = $this->renderer->variables($contrato);
        // Los datos del cliente se escapan para no inyectar HTML en el correo; {{causas}} ya es HTML.
        $seguras = self::escapar($variables);

        $rutaImagen = $config->getImagen() ? $this->directorioImagenes . '/' . $config->getImagen() : null;
        $conImagen = $rutaImagen !== null && is_file($rutaImagen);
        // {{imagen}} -> imagen incrustada (cid); si no hay imagen, queda vacío.
        $seguras['{{imagen}}'] = $conImagen ? 'cid:imagen' : '';

        try {
            $email = (new Email())
                ->from(new Address($config->getRemitenteCorreo(), (string) $config->getRemitenteNombre()))
                ->to($destino)
                ->subject(strtr((string) $config->getAsunto(), $variables))
                ->html(strtr($config->getContenido(), $seguras));
            if ($conImagen) {
                $email->embedFromPath($rutaImagen, 'imagen');
            }
            $this->mailer->send($email);
            return null;
        } catch (\Throwable $e) {
            $this->logger->error('No se pudo enviar el correo de bienvenida del contrato ' . $contrato->getId() . ': ' . $e->getMessage());
            return 'error al enviar: ' . mb_substr($e->getMessage(), 0, 200);
        }
    }
}
