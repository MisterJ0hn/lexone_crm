<?php

namespace App\Entity;

use App\Repository\EstrategiaJuridicaReporteArchivosRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EstrategiaJuridicaReporteArchivosRepository::class)
 */
class EstrategiaJuridicaReporteArchivos
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Causa::class, inversedBy="estrategiaJuridicaReporteArchivos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $causa;

    /**
     * @ORM\ManyToOne(targetEntity=EstrategiaJuridicaReporte::class, inversedBy="estrategiaJuridicaReporteArchivos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $estrategiaJuridicaReporte;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $archivo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class, inversedBy="estrategiaJuridicaReporteArchivos")
     */
    private $usuarioCreacion;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaYHoraCarga;

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

    public function getEstrategiaJuridicaReporte(): ?EstrategiaJuridicaReporte
    {
        return $this->estrategiaJuridicaReporte;
    }

    public function setEstrategiaJuridicaReporte(?EstrategiaJuridicaReporte $estrategiaJuridicaReporte): self
    {
        $this->estrategiaJuridicaReporte = $estrategiaJuridicaReporte;

        return $this;
    }

    public function getArchivo(): ?string
    {
        return $this->archivo;
    }

    public function setArchivo(string $archivo): self
    {
        $this->archivo = $archivo;

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getUsuarioCreacion(): ?Usuario
    {
        return $this->usuarioCreacion;
    }

    public function setUsuarioCreacion(?Usuario $usuarioCreacion): self
    {
        $this->usuarioCreacion = $usuarioCreacion;

        return $this;
    }

    public function getFechaYHoraCarga(): ?\DateTimeInterface
    {
        return $this->fechaYHoraCarga;
    }

    public function setFechaYHoraCarga(\DateTimeInterface $fechaYHoraCarga): self
    {
        $this->fechaYHoraCarga = $fechaYHoraCarga;

        return $this;
    }
}
