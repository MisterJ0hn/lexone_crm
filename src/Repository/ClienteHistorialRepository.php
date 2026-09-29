<?php

namespace App\Repository;

use App\Entity\Cliente;
use App\Entity\ClienteHistorial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ClienteHistorial|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClienteHistorial|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClienteHistorial[]    findAll()
 * @method ClienteHistorial[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClienteHistorialRepository extends ServiceEntityRepository
{
    /**
     * Campos cuyo historial se puede consultar desde el modal de observación,
     * mapeados a la etiqueta que se muestra en pantalla. Funciona además como
     * lista blanca, porque el nombre del campo se concatena en el DQL.
     */
    public const CAMPOS_CONSULTABLES = [
        'nombre'    => 'Nombre',
        'correo'    => 'Email',
        'telefono'  => 'Teléfono',
        'direccion' => 'Dirección',
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClienteHistorial::class);
    }

    /**
     * Devuelve los cambios registrados para un solo campo del cliente, del más
     * reciente al más antiguo. Cada fila guarda el valor ANTERIOR del campo, y
     * las filas donde el campo quedó en null (porque en esa edición cambió otro
     * dato) se descartan.
     *
     * @return ClienteHistorial[]
     */
    public function findHistorialCampo(Cliente $cliente, string $campo): array
    {
        if (!isset(self::CAMPOS_CONSULTABLES[$campo])) {
            throw new \InvalidArgumentException(sprintf('Campo de historial no válido: %s', $campo));
        }

        return $this->createQueryBuilder('h')
            ->andWhere('h.cliente = :cliente')
            ->andWhere(sprintf('h.%s IS NOT NULL', $campo))
            ->setParameter('cliente', $cliente)
            ->orderBy('h.fechaModificacion', 'DESC')
            ->addOrderBy('h.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Valores distintos ya registrados en el historial, por campo:
     * ['nombre' => ['Juan Perez', ...], 'correo' => [...], ...].
     *
     * Se entregan al modal de observación para poder avisar en pantalla, mientras
     * se escribe, que ese valor ya se usó antes en ese campo.
     *
     * @return array<string, string[]>
     */
    public function valoresPorCampo(Cliente $cliente): array
    {
        $valores = [];

        foreach (array_keys(self::CAMPOS_CONSULTABLES) as $campo) {
            $valores[$campo] = [];

            foreach ($this->findHistorialCampo($cliente, $campo) as $registro) {
                $valor = trim((string) $this->valorCampo($registro, $campo));

                if ($valor !== '' && !in_array($valor, $valores[$campo], true)) {
                    $valores[$campo][] = $valor;
                }
            }
        }

        return $valores;
    }

    private function valorCampo(ClienteHistorial $registro, string $campo): ?string
    {
        switch ($campo) {
            case 'nombre':
                return $registro->getNombre();
            case 'correo':
                return $registro->getCorreo();
            case 'telefono':
                return $registro->getTelefono();
            case 'direccion':
                return $registro->getDireccion();
        }

        return null;
    }

    // /**
    //  * @return ClienteHistorial[] Returns an array of ClienteHistorial objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ClienteHistorial
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
