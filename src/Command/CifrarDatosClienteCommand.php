<?php

namespace App\Command;

use App\Security\Cifrado;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Backfill único: cifra (AES-256-GCM) los valores de rut, telefono, telefono_recado,
 * correo, direccion y clave_unica que hoy están en texto plano en "cliente" y
 * "cliente_historial", y calcula rut_hash/telefono_hash/telefono_recado_hash/correo_hash
 * en "cliente".
 *
 * Usa la conexión DBAL directamente (no el ORM/EntityManager de Cliente) para no
 * cargar miles de entidades en memoria y para poder decidir fila por fila si el
 * valor ya está cifrado (idempotente: correrlo dos veces no vuelve a cifrar nada).
 *
 * Requiere que las migraciones Version20260723120000 y Version20260729120000 (correo)
 * ya se hayan ejecutado.
 */
class CifrarDatosClienteCommand extends Command
{
    protected static $defaultName        = 'app:cifrar-datos-cliente';
    protected static $defaultDescription = 'Cifra (backfill) rut/telefono/telefono_recado/correo/direccion/clave_unica existentes en cliente y cliente_historial';

    private Connection $conexion;

    public function __construct(EntityManagerInterface $em)
    {
        $this->conexion = $em->getConnection();
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $totalCliente = $this->procesarCliente($io);
        $totalHistorial = $this->procesarClienteHistorial($io);

        $io->table(
            ['Tabla', 'Filas actualizadas'],
            [
                ['cliente', $totalCliente],
                ['cliente_historial', $totalHistorial],
            ]
        );
        $io->success('Backfill de cifrado finalizado.');

        return 0;
    }

    private function procesarCliente(SymfonyStyle $io): int
    {
        $filas = $this->conexion->fetchAllAssociative(
            'SELECT id, rut, telefono, telefono_recado, correo, direccion, clave_unica,
                    rut_hash, telefono_hash, telefono_recado_hash, correo_hash
             FROM cliente'
        );

        $io->text(sprintf('cliente: %d filas encontradas.', count($filas)));
        $progreso = $io->createProgressBar(count($filas));
        $actualizadas = 0;

        foreach ($filas as $fila) {
            $rutPlano = $this->obtenerPlano($fila['rut']);
            $telefonoPlano = $this->obtenerPlano($fila['telefono']);
            $telefonoRecadoPlano = $this->obtenerPlano($fila['telefono_recado']);
            $correoPlano = $this->obtenerPlano($fila['correo']);

            $datos = [
                'rut' => Cifrado::pareceCifrado($fila['rut']) ? null : Cifrado::encrypt($rutPlano),
                'telefono' => Cifrado::pareceCifrado($fila['telefono']) ? null : Cifrado::encrypt($telefonoPlano),
                'telefono_recado' => Cifrado::pareceCifrado($fila['telefono_recado']) ? null : Cifrado::encrypt($telefonoRecadoPlano),
                'correo' => Cifrado::pareceCifrado($fila['correo']) ? null : Cifrado::encrypt($correoPlano),
                'direccion' => Cifrado::pareceCifrado($fila['direccion']) ? null : Cifrado::encrypt($this->obtenerPlano($fila['direccion'])),
                'clave_unica' => Cifrado::pareceCifrado($fila['clave_unica']) ? null : Cifrado::encrypt($this->obtenerPlano($fila['clave_unica'])),
                'rut_hash' => $fila['rut_hash'] ?? Cifrado::hash($rutPlano, 'rut'),
                'telefono_hash' => $fila['telefono_hash'] ?? Cifrado::hash($telefonoPlano, 'telefono'),
                'telefono_recado_hash' => $fila['telefono_recado_hash'] ?? Cifrado::hash($telefonoRecadoPlano, 'telefono'),
                'correo_hash' => $fila['correo_hash'] ?? Cifrado::hash($correoPlano, 'correo'),
            ];

            $datos = array_filter($datos, static fn ($valor) => $valor !== null);

            if (!empty($datos)) {
                $this->conexion->update('cliente', $datos, ['id' => $fila['id']]);
                $actualizadas++;
            }

            $progreso->advance();
        }

        $progreso->finish();
        $io->newLine(2);

        return $actualizadas;
    }

    private function procesarClienteHistorial(SymfonyStyle $io): int
    {
        $filas = $this->conexion->fetchAllAssociative(
            'SELECT id, rut, telefono, telefono_recado, correo, direccion, clave_unica FROM cliente_historial'
        );

        $io->text(sprintf('cliente_historial: %d filas encontradas.', count($filas)));
        $progreso = $io->createProgressBar(count($filas));
        $actualizadas = 0;

        foreach ($filas as $fila) {
            $datos = [
                'rut' => Cifrado::pareceCifrado($fila['rut']) ? null : Cifrado::encrypt($this->obtenerPlano($fila['rut'])),
                'telefono' => Cifrado::pareceCifrado($fila['telefono']) ? null : Cifrado::encrypt($this->obtenerPlano($fila['telefono'])),
                'telefono_recado' => Cifrado::pareceCifrado($fila['telefono_recado']) ? null : Cifrado::encrypt($this->obtenerPlano($fila['telefono_recado'])),
                'correo' => Cifrado::pareceCifrado($fila['correo']) ? null : Cifrado::encrypt($this->obtenerPlano($fila['correo'])),
                'direccion' => Cifrado::pareceCifrado($fila['direccion']) ? null : Cifrado::encrypt($this->obtenerPlano($fila['direccion'])),
                'clave_unica' => Cifrado::pareceCifrado($fila['clave_unica']) ? null : Cifrado::encrypt($this->obtenerPlano($fila['clave_unica'])),
            ];

            $datos = array_filter($datos, static fn ($valor) => $valor !== null);

            if (!empty($datos)) {
                $this->conexion->update('cliente_historial', $datos, ['id' => $fila['id']]);
                $actualizadas++;
            }

            $progreso->advance();
        }

        $progreso->finish();
        $io->newLine(2);

        return $actualizadas;
    }

    /**
     * Si la columna ya quedó cifrada en una corrida anterior, la desencripta para
     * poder recalcular su hash; si aún está en texto plano, la devuelve tal cual.
     */
    private function obtenerPlano(?string $valor): ?string
    {
        if (Cifrado::pareceCifrado($valor)) {
            return Cifrado::decrypt($valor);
        }

        return $valor;
    }
}
