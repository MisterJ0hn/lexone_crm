<?php

namespace App\Entity;

use App\Repository\EstadoProcesalRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Ítem del estado procesal de una causa. Reemplaza a la antigua línea de tiempo
 * (LineaTiempo/Etapas/Terminada): cada causa lleva su propia lista, que parte con
 * "Inicio tramitación" ya completado y a la que se agregan ítems a mano. Se graba por
 * empresa (empresa_id) como el resto de las tablas del tenant.
 */
#[ORM\Entity(repositoryClass: EstadoProcesalRepository::class)]
class EstadoProcesal
{
    public const BIENVENIDA = 'Inicio tramitación';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Empresa::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $empresa;

    #[ORM\ManyToOne(targetEntity: Causa::class, inversedBy: "estadosProcesales")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private $causa;

    #[ORM\Column(type: "string", length: 255)]
    private $nombre;

    #[ORM\Column(type: "text", nullable: true)]
    private $observacion;

    /** true = completado (verde); false = pendiente (rojo). */
    #[ORM\Column(type: "boolean")]
    private $completado = false;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(nullable: true)]
    private $usuarioRegistro;

    #[ORM\Column(type: "datetime")]
    private $fecha;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $fechaCompletado;

    public function __construct()
    {
        $this->fecha = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getEmpresa(): ?Empresa { return $this->empresa; }
    public function setEmpresa(?Empresa $empresa): self { $this->empresa = $empresa; return $this; }

    public function getCausa(): ?Causa { return $this->causa; }
    public function setCausa(?Causa $causa): self { $this->causa = $causa; return $this; }

    public function getNombre(): ?string { return $this->nombre; }
    public function setNombre(?string $nombre): self { $this->nombre = $nombre; return $this; }

    public function getObservacion(): ?string { return $this->observacion; }
    public function setObservacion(?string $observacion): self { $this->observacion = $observacion; return $this; }

    public function isCompletado(): bool { return (bool) $this->completado; }
    public function setCompletado(bool $completado): self
    {
        $this->completado = $completado;
        $this->fechaCompletado = $completado ? new \DateTime() : null;
        return $this;
    }

    public function getUsuarioRegistro(): ?Usuario { return $this->usuarioRegistro; }
    public function setUsuarioRegistro(?Usuario $usuarioRegistro): self { $this->usuarioRegistro = $usuarioRegistro; return $this; }

    public function getFecha(): ?\DateTimeInterface { return $this->fecha; }
    public function setFecha(\DateTimeInterface $fecha): self { $this->fecha = $fecha; return $this; }

    public function getFechaCompletado(): ?\DateTimeInterface { return $this->fechaCompletado; }
}
