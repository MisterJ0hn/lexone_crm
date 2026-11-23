<?php

namespace App\Command;

use App\Entity\Contrato;
use App\Entity\ContratoAnexo;
use App\Entity\Cuota;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Genera la cuota mensual de los honorarios Pre-pago.
 *
 * Cada contrato tiene UNA sola cadena activa de cuotas mensuales:
 *  - Si no tiene anexos vigentes, la del propio contrato (si es Pre-pago).
 *  - Si tiene anexos, la del último anexo vigente (el que anula las cuotas anteriores), pero solo si ese
 *    anexo es Pre-pago. Un anexo normal detiene la generación automática, aunque el contrato sea Pre-pago.
 *    Un contrato Pre-pago con un anexo Pre-pago no genera cuotas propias: sigue la cadena del anexo.
 * Un anexo está vigente si no desiste (isDesiste) y no fue eliminado (estado = false), igual que
 * AnexoController::eliminar().
 *
 * Regla de la cadena: siempre debe existir una cuota con vencimiento hoy o a futuro. El día siguiente al
 * vencimiento de la última cuota se crea la del mes siguiente (mismo día del mes que la primera cuota; si
 * el mes no tiene ese día, el último día del mes). Se crea aunque la anterior siga impaga. No se generan
 * cuotas si el contrato terminó, se finalizó o el cliente desistió.
 *
 * Cada cuota nueva suma 1 al contador de cuotas y su valor al monto del dueño de la cadena
 * (Contrato::cuotas/montoContrato, o ContratoAnexo::nCuotas/MontoContrato; en el caso del anexo también
 * Contrato::cuotas, que ContratoAnexo replica al calcular cuotas).
 *
 * Idempotente: si corre varias veces el mismo día no duplica nada. Si el proceso estuvo detenido o hay
 * contratos antiguos sin cuotas al día, crea todas las cuotas que falten hasta alcanzar la fecha de hoy.
 *
 * Programar una vez al día, p. ej.:  5 0 * * *  php bin/console app:cuotas-prepago
 */
#[AsCommand(name: 'app:cuotas-prepago', description: 'Genera la cuota mensual de los contratos y anexos Pre-pago')]
class CuotasPrepagoCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Muestra lo que se crearía sin guardar nada');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $hoy = new \DateTimeImmutable('today');

        // Candidatos: contratos vivos que son Pre-pago o que tienen algún anexo Pre-pago.
        $ids = $this->em->createQuery(
            'SELECT DISTINCT c.id FROM App\Entity\Contrato c
             WHERE (c.isFinalizado = false OR c.isFinalizado IS NULL)
               AND c.fechaTermino IS NULL
               AND c.fechaDesiste IS NULL
               AND (c.isPrePago = true
                    OR EXISTS (SELECT a.id FROM App\Entity\ContratoAnexo a WHERE a.contrato = c AND a.isPrePago = true))'
        )->getSingleColumnResult();

        $contratosAfectados = 0;
        $cuotasCreadas = 0;

        foreach ($ids as $id) {
            $contrato = $this->em->find(Contrato::class, $id);
            if (!$contrato) {
                continue;
            }

            $anexo = $this->anexoVigente($contrato);
            if ($anexo !== null) {
                $creadas = $anexo->getIsPrePago() ? $this->generarPendientes($contrato, $anexo, $hoy, $dryRun, $io) : 0;
            } else {
                $creadas = $contrato->getIsPrePago() ? $this->generarPendientes($contrato, null, $hoy, $dryRun, $io) : 0;
            }

            if ($creadas > 0) {
                $contratosAfectados++;
                $cuotasCreadas += $creadas;
                if (!$dryRun) {
                    $this->em->flush();
                }
            }
            if (!$dryRun) {
                $this->em->clear();
            }
        }

        $io->success(sprintf(
            '%s%d cuota(s) en %d contrato(s) Pre-pago.',
            $dryRun ? '[dry-run] Se crearían ' : 'Creadas ',
            $cuotasCreadas,
            $contratosAfectados
        ));

        return Command::SUCCESS;
    }

    /** Último anexo vigente del contrato (mayor folio, no desistido ni eliminado), o null si no tiene. */
    private function anexoVigente(Contrato $contrato): ?ContratoAnexo
    {
        $anexos = $this->em->getRepository(ContratoAnexo::class)->findBy(['contrato' => $contrato], ['folio' => 'DESC']);
        foreach ($anexos as $anexo) {
            if (!$anexo->getIsDesiste() && $anexo->getEstado() !== false) {
                return $anexo;
            }
        }

        return null;
    }

    /**
     * Crea las cuotas mensuales que falten para la cadena del contrato ($anexo = null) o del anexo.
     * Devuelve la cantidad de cuotas creadas (o que se crearían en dry-run).
     */
    private function generarPendientes(Contrato $contrato, ?ContratoAnexo $anexo, \DateTimeImmutable $hoy, bool $dryRun, SymfonyStyle $io): int
    {
        // Solo las cuotas del dueño de la cadena (el contrato o ese anexo).
        $propias = array_filter(
            $this->em->getRepository(Cuota::class)->findBy(['contrato' => $contrato], ['numero' => 'ASC']),
            fn (Cuota $c) => $anexo === null ? $c->getAnexo() === null : $c->getAnexo()?->getId() === $anexo->getId()
        );

        $maxNumero = 0;
        $vigentes = [];
        foreach ($propias as $c) {
            $maxNumero = max($maxNumero, (int) $c->getNumero());
            // Cuotas válidas para fijar la cadencia mensual: ni anuladas ni multas.
            if (!$c->getAnular() && !$c->getIsMulta()) {
                $vigentes[] = $c;
            }
        }

        $valor = (int) ($anexo ? $anexo->getValorCuota() : $contrato->getValorCuota());

        // Sin cuotas base (p. ej. el PDF del contrato aún no las generó) no hay desde dónde continuar.
        if (!$vigentes || $valor <= 0) {
            return 0;
        }

        $base = \DateTimeImmutable::createFromInterface($vigentes[0]->getFechaPago());
        $ultima = $base;
        foreach ($vigentes as $c) {
            $f = \DateTimeImmutable::createFromInterface($c->getFechaPago());
            if ($f > $ultima) {
                $ultima = $f;
            }
        }

        $diaBase = (int) $base->format('j');
        $meses = ((int) $ultima->format('Y') - (int) $base->format('Y')) * 12
            + ((int) $ultima->format('n') - (int) $base->format('n'));

        $creadas = 0;
        // La cuota nueva se crea el día siguiente al vencimiento de la última.
        while ($ultima < $hoy) {
            $meses++;
            $ultima = $this->fechaCuota($base, $diaBase, $meses);
            $maxNumero++;
            $creadas++;

            $io->writeln(sprintf(
                '  contrato %d (folio %s)%s: cuota %d vence %s',
                $contrato->getId(),
                $contrato->getFolio() ?? '-',
                $anexo ? ' anexo ' . $anexo->getFolio() : '',
                $maxNumero,
                $ultima->format('d-m-Y')
            ));

            if ($dryRun) {
                continue;
            }

            $cuota = new Cuota();
            $cuota->setContrato($contrato);
            if ($anexo) {
                $cuota->setAnexo($anexo);
            }
            $cuota->setNumero($maxNumero);
            $cuota->setFechaPago(\DateTime::createFromImmutable($ultima));
            $cuota->setMonto($valor);
            $this->em->persist($cuota);

            if ($anexo) {
                $anexo->setNCuotas(((int) $anexo->getNCuotas()) + 1);
                $anexo->setMontoContrato(((float) $anexo->getMontoContrato()) + $valor);
                $contrato->setCuotas($anexo->getNCuotas());
            } else {
                $contrato->setCuotas(((int) $contrato->getCuotas()) + 1);
                $contrato->setMontoContrato((string) (((int) $contrato->getMontoContrato()) + $valor));
            }
        }

        return $creadas;
    }

    /** Mismo día del mes que la primera cuota, acotado al último día del mes cuando no existe. */
    private function fechaCuota(\DateTimeImmutable $base, int $diaBase, int $meses): \DateTimeImmutable
    {
        $primero = $base->modify('first day of this month')->modify(sprintf('+%d months', $meses));
        $dia = min($diaBase, (int) $primero->format('t'));

        return $primero->setDate((int) $primero->format('Y'), (int) $primero->format('n'), $dia);
    }
}
