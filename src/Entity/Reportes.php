<?php

namespace App\Entity;

use App\Repository\ReportesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReportesRepository::class)
 */
class Reportes
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class, inversedBy="reportes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuario;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaRegistro;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $criterio = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $resultado = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getFechaRegistro(): ?\DateTimeInterface
    {
        return $this->fechaRegistro;
    }

    public function setFechaRegistro(\DateTimeInterface $fechaRegistro): self
    {
        $this->fechaRegistro = $fechaRegistro;

        return $this;
    }

    public function getCriterio(): ?array
    {
        return $this->criterio;
    }

    public function setCriterio(?array $criterio): self
    {
        $this->criterio = $criterio;

        return $this;
    }

    public function getResultado(): ?array
    {
        return $this->resultado;
    }

    public function setResultado(?array $resultado): self
    {
        $this->resultado = $resultado;

        return $this;
    }
}
