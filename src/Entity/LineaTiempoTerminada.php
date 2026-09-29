<?php

namespace App\Entity;

use App\Repository\LineaTiempoTerminadaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LineaTiempoTerminadaRepository::class)
 */
class LineaTiempoTerminada
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Causa::class, inversedBy="lineaTiempoTerminadas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $causa;

    /**
     * @ORM\ManyToOne(targetEntity=LineaTiempoEtapas::class, inversedBy="lineaTiempoTerminadas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $lineaTiempoEtapas;

    /**
     * @ORM\Column(type="boolean")
     */
    private $estado;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuarioRegistro;

    /**
     * @ORM\Column(type="text")
     */
    private $observacion;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fecha;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCausa(): ?Causa
    {
        return $this->causa;
    }

    public function setCausa(?Causa $causa): self
    {
        $this->causa = $causa;

        return $this;
    }

    public function getLineaTiempoEtapas(): ?LineaTiempoEtapas
    {
        return $this->lineaTiempoEtapas;
    }

    public function setLineaTiempoEtapas(?LineaTiempoEtapas $lineaTiempoEtapas): self
    {
        $this->lineaTiempoEtapas = $lineaTiempoEtapas;

        return $this;
    }

    public function getEstado(): ?bool
    {
        return $this->estado;
    }

    public function setEstado(bool $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    public function getUsuarioRegistro(): ?Usuario
    {
        return $this->usuarioRegistro;
    }

    public function setUsuarioRegistro(?Usuario $usuarioRegistro): self
    {
        $this->usuarioRegistro = $usuarioRegistro;

        return $this;
    }

    public function getObservacion(): ?string
    {
        return $this->observacion;
    }

    public function setObservacion(string $observacion): self
    {
        $this->observacion = $observacion;

        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }
}
