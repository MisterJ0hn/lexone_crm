<?php

namespace App\Doctrine;

use App\Entity\Agenda;
use App\Entity\Causa;
use App\Entity\Cobranza;
use App\Entity\ContratoAnexo;
use App\Entity\ContratoRol;
use App\Entity\Contrato;
use App\Entity\Cuenta;
use App\Entity\Cuota;
use App\Entity\Pago;
use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * Aislamiento por empresa (tenant) a nivel de SQL: agrega la condición de empresa
 * a CUALQUIER consulta (DQL, find(), findBy(), joins y carga perezosa) sobre las
 * entidades listadas aquí, sin depender de que cada controlador se acuerde de filtrar.
 *
 * Se activa desde App\EventListener\TenantSubscriber sólo para usuarios que no son
 * super-admin y que tienen una empresa resuelta. Fuera de un request (consola/cron)
 * no se activa, por lo que los comandos siguen viendo todo.
 *
 * Para proteger otra entidad basta con agregarla a DIRECTAS (tiene asociación
 * "empresa") o a POR_PADRE (llega a la empresa a través de otra entidad ya cubierta).
 */
class TenantFilter extends SQLFilter
{
    /** Entidades con asociación propia "empresa". */
    private const DIRECTAS = [
        Agenda::class,
        Cuenta::class,
        Ticket::class,
    ];

    /** Entidad => propiedad ManyToOne/OneToOne que lleva hacia su "padre" con empresa. */
    private const POR_PADRE = [
        Contrato::class => 'agenda',
        Causa::class => 'agenda',
        Cuota::class => 'contrato',
        Cobranza::class => 'contrato',
        Pago::class => 'contrato',
        ContratoRol::class => 'contrato',
        ContratoAnexo::class => 'contrato',
    ];

    private ?EntityManagerInterface $em = null;

    /** El constructor de SQLFilter es final y no expone el EM: se inyecta al activar el filtro. */
    public function activarPara(EntityManagerInterface $em, int $empresaId): void
    {
        $this->em = $em;
        $this->setParameter('empresa_id', $empresaId, 'integer');
    }

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if (!$this->hasParameter('empresa_id')) {
            return '';
        }

        return $this->condicion($targetEntity, $targetTableAlias, 0);
    }

    private function condicion(ClassMetadata $meta, string $alias, int $nivel): string
    {
        $clase = $meta->getName();

        if (in_array($clase, self::DIRECTAS, true)) {
            $columna = $meta->getAssociationMapping('empresa')['joinColumns'][0]['name'];

            return sprintf('%s.%s = %s', $alias, $columna, $this->getParameter('empresa_id'));
        }

        if (!isset(self::POR_PADRE[$clase]) || $nivel > 4) {
            return '';
        }

        $relacion = $meta->getAssociationMapping(self::POR_PADRE[$clase]);
        $columnaFk = $relacion['joinColumns'][0]['name'];
        $columnaPk = $relacion['joinColumns'][0]['referencedColumnName'];
        if ($this->em === null) {
            throw new \LogicException('TenantFilter activado sin EntityManager: use activarPara().');
        }
        $metaPadre = $this->em->getClassMetadata($relacion['targetEntity']);
        $aliasPadre = 'tf' . ($nivel + 1);

        $interna = $this->condicion($metaPadre, $aliasPadre, $nivel + 1);
        if ($interna === '') {
            return '';
        }

        // Los registros sin padre (FK nula) no pertenecen a ninguna empresa: no se
        // ocultan para no romper datos huérfanos que hoy se muestran.
        // EXISTS correlacionado (búsqueda por PK) en vez de IN (SELECT ...): en MySQL 5.7
        // el IN combinado con OR no se optimiza y se ejecuta fila por fila.
        return sprintf(
            '(%s.%s IS NULL OR EXISTS (SELECT 1 FROM %s %s WHERE %s.%s = %s.%s AND %s))',
            $alias,
            $columnaFk,
            $metaPadre->getTableName(),
            $aliasPadre,
            $aliasPadre,
            $columnaPk,
            $alias,
            $columnaFk,
            $interna
        );
    }
}
