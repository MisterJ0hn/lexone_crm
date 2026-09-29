<?php

namespace App\Service;

use App\Entity\Configuracion;
use App\Entity\PasswordHistorial;
use App\Entity\Usuario;
use App\Repository\ConfiguracionRepository;
use App\Repository\PasswordHistorialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PasswordService
{
    private const HISTORIAL_LIMITE    = 4;
    private const DIAS_EXPIRACION     = 90;

    private EntityManagerInterface        $em;
    private UserPasswordEncoderInterface  $encoder;
    private PasswordHistorialRepository   $historialRepo;
    private ConfiguracionRepository       $configuracionRepo;
    private Configuracion $configuracion;
    public function __construct(
        EntityManagerInterface       $em,
        UserPasswordEncoderInterface $encoder,
        PasswordHistorialRepository  $historialRepo,
        ConfiguracionRepository $configuracionRepository
    ) {
        $this->em            = $em;
        $this->encoder       = $encoder;
        $this->historialRepo = $historialRepo;
        $this->configuracionRepo = $configuracionRepository;

        $this->configuracion = $this->configuracionRepo->find(1);

    }

    /**
     * Verifica si el plainPassword ya fue usado en los últimos HISTORIAL_LIMITE contraseñas.
     * Retorna true si la contraseña YA FUE USADA (no se puede reutilizar).
     * Usa password_verify directamente para comparar contra hashes históricos (bcrypt/argon2).
     */
    public function yaFueUsada(Usuario $usuario, string $plainPassword): bool
    {
        $historial = $this->historialRepo->findUltimosN($usuario, $this->configuracion->getPaswordHistorial() ?? self::HISTORIAL_LIMITE);

        foreach ($historial as $registro) {
            if (password_verify($plainPassword, $registro->getPasswordHash())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Guarda el hash en el historial y elimina entradas antiguas, conservando solo las últimas HISTORIAL_LIMITE.
     */
    public function guardarEnHistorial(Usuario $usuario, string $encodedPassword): void
    {
        $registro = new PasswordHistorial();
        $registro->setUsuario($usuario);
        $registro->setPasswordHash($encodedPassword);
        $registro->setFechaCreacion(new \DateTime());

        $this->em->persist($registro);
        $this->em->flush();

        $this->historialRepo->eliminarAntiguos($usuario, $this->configuracion->getPaswordHistorial() ??self::HISTORIAL_LIMITE);
    }

    /**
     * Actualiza la fecha de expiración a DIAS_EXPIRACION días desde hoy.
     */
    public function renovarExpiracion(Usuario $usuario): void
    {
        $dias = $this->configuracion->getPasswordDiasExpiracion() ?? self::DIAS_EXPIRACION;
        $expiracion = new \DateTime('+' . $dias . ' days');
        $usuario->setPasswordExpiracion($expiracion);
        $this->em->persist($usuario);
        $this->em->flush();
    }

    /**
     * Codifica la contraseña, guarda en historial, renueva expiración y persiste todo.
     * Retorna el hash resultante.
     */
    public function aplicarNuevoPassword(Usuario $usuario, string $plainPassword): string
    {
        $encoded = $this->encoder->encodePassword($usuario, $plainPassword);
        $usuario->setPassword($encoded);
       
        $dias = $this->configuracion->getPasswordDiasExpiracion() ?? self::DIAS_EXPIRACION;
        $expiracion = new \DateTime('+' . $dias . ' days');
        $usuario->setPasswordExpiracion($expiracion);

        $this->em->persist($usuario);
        $this->em->flush();

        $this->guardarEnHistorial($usuario, $encoded);

        return $encoded;
    }

    public function getDiasExpiracion(): int
    {
        return $this->configuracion->getPasswordDiasExpiracion() ?? self::DIAS_EXPIRACION;
    }

    public function getHistorialLimite(): int
    {
        return $this->configuracion->getPaswordHistorial() ?? self::HISTORIAL_LIMITE;
    }
}
