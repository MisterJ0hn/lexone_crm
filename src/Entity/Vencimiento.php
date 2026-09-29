<?php

namespace App\Entity;

use App\Repository\VencimientoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VencimientoRepository::class)]
class Vencimiento
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer", nullable: true)]
    private $valMin;

    #[ORM\Column(type: "integer", nullable: true)]
    private $valMax;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $color;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $icono;

    #[ORM\ManyToOne(targetEntity: Empresa::class)]
    private $empresa;
    #[ORM\Column(type: "string", length: 255)]
    private $nombre;

    #[ORM\Column(type: "integer", nullable: true)]
    private $montoMax;

    #[ORM\Column(type: "boolean", nullable: true)]
    private $soloPorAdmin;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValMin(): ?int
    {
        return $this->valMin;
    }

    public function setValMin(?int $valMin): self
    {
        $this->valMin = $valMin;

        return $this;
    }

    public function getValMax(): ?int
    {
        return $this->valMax;
    }

    public function setValMax(?int $valMax): self
    {
        $this->valMax = $valMax;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getIcono(): ?string
    {
        return $this->icono;
    }

    public function setIcono(?string $icono): self
    {
        $this->icono = $icono;

        return $this;
    }

    public function getEmpresa(): ?Empresa
    {
        return $this->empresa;
    }

    public function setEmpresa(?Empresa $empresa): self
    {
        $this->empresa = $empresa;

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

    public function getMontoMax(): ?int
    {
        return $this->montoMax;
    }

    public function setMontoMax(?int $montoMax): self
    {
        $this->montoMax = $montoMax;

        return $this;
    }

    public function getSoloPorAdmin(): ?bool
    {
        return $this->soloPorAdmin;
    }

    public function setSoloPorAdmin(?bool $soloPorAdmin): self
    {
        $this->soloPorAdmin = $soloPorAdmin;

        return $this;
    }
}
