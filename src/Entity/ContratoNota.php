<?php

namespace App\Entity;

use App\Repository\ContratoNotaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Observación del historial del contrato (línea de tiempo), con un archivo
 * opcional. Es un histórico: solo se agregan registros, no se editan ni se borran.
 * El archivo se guarda fuera de public/ (ver ContratoController::notaDescargar()).
 */
#[ORM\Entity(repositoryClass: ContratoNotaRepository::class)]
class ContratoNota
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Contrato::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private $contrato;

    /**
     * Sub cliente de un Convenio/Empresa al que pertenece la observación. null =
     * observación del contrato (cliente principal / Persona).
     */
    #[ORM\ManyToOne(targetEntity: Cliente::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    private $cliente;

    #[ORM\Column(type: "text")]
    private $observacion;

    /** Nombre del archivo guardado en disco (aleatorio). */
    #[ORM\Column(type: "string", length: 64, nullable: true)]
    private $archivo;

    /** Nombre original, el que ve y descarga el usuario. */
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $archivoNombre;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $usuario;

    #[ORM\Column(type: "datetime")]
    private $fechaRegistro;

    public function __construct()
    {
        $this->fechaRegistro = new \DateTime();
    }

    public function getId(): ?int { return $this->id; }

    public function getContrato(): ?Contrato { return $this->contrato; }
    public function setContrato(?Contrato $contrato): self { $this->contrato = $contrato; return $this; }

    public function getCliente(): ?Cliente { return $this->cliente; }
    public function setCliente(?Cliente $cliente): self { $this->cliente = $cliente; return $this; }

    public function getObservacion(): ?string { return $this->observacion; }
    public function setObservacion(?string $observacion): self { $this->observacion = $observacion; return $this; }

    public function getArchivo(): ?string { return $this->archivo; }
    public function setArchivo(?string $archivo): self { $this->archivo = $archivo; return $this; }

    public function getArchivoNombre(): ?string { return $this->archivoNombre; }
    public function setArchivoNombre(?string $archivoNombre): self { $this->archivoNombre = $archivoNombre; return $this; }

    public function getUsuario(): ?Usuario { return $this->usuario; }
    public function setUsuario(?Usuario $usuario): self { $this->usuario = $usuario; return $this; }

    public function getFechaRegistro(): ?\DateTimeInterface { return $this->fechaRegistro; }
}
