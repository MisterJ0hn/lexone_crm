<?php

namespace App\Entity;

use App\Repository\CobranzaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CobranzaRepository::class)
 */
class Cobranza
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=CobranzaFuncion::class, inversedBy="cobranzas")
     */
    private $funcion;

    /**
     * @ORM\ManyToOne(targetEntity=CobranzaRespuesta::class, inversedBy="cobranzas")
     */
    private $respuesta;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaHora;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaCompromiso;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $observacion;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isNulo;
    
    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuarioRegistro;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fecha;

    /**
     * @ORM\ManyToOne(targetEntity=Contrato::class, inversedBy="cobranzas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $contrato;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFuncion(): ?CobranzaFuncion
    {
        return $this->funcion;
    }

    public function setFuncion(?CobranzaFuncion $funcion): self
    {
        $this->funcion = $funcion;

        return $this;
    }

    public function getRespuesta(): ?CobranzaRespuesta
    {
        return $this->respuesta;
    }

    public function setRespuesta(?CobranzaRespuesta $respuesta): self
    {
        $this->respuesta = $respuesta;

        return $this;
    }

    public function getFechaHora(): ?\DateTimeInterface
    {
        return $this->fechaHora;
    }

    public function setFechaHora(\DateTimeInterface $fechaHora): self
    {
        $this->fechaHora = $fechaHora;

        return $this;
    }

    public function getFechaCompromiso(): ?\DateTimeInterface
    {
        return $this->fechaCompromiso;
    }

    public function setFechaCompromiso(?\DateTimeInterface $fechaCompromiso): self
    {
        $this->fechaCompromiso = $fechaCompromiso;

        return $this;
    }

    public function getObservacion(): ?string
    {
        return $this->observacion;
    }

    public function setObservacion(?string $observacion): self
    {
        $this->observacion = $observacion;

        return $this;
    }

    public function getIsNulo(): ?bool
    {
        return $this->isNulo;
    }

    public function setIsNulo(?bool $isNulo): self
    {
        $this->isNulo = $isNulo;

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

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(?\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getContrato(): ?Contrato
    {
        return $this->contrato;
    }

    public function setContrato(?Contrato $contrato): self
    {
        $this->contrato = $contrato;

        return $this;
    }
}
