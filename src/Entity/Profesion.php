<?php

namespace App\Entity;

use App\Repository\ProfesionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Catálogo (por empresa) de profesiones u oficios para el representante legal
 * de clientes Empresa/Convenio. Cliente::$repLegalProfesion guarda el texto elegido.
 */
#[ORM\Entity(repositoryClass: ProfesionRepository::class)]
class Profesion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Empresa::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $empresa;

    #[ORM\Column(type: "string", length: 255)]
    private $nombre;

    #[ORM\Column(type: "boolean")]
    private $estado = true;

    public function getId(): ?int { return $this->id; }

    public function getEmpresa(): ?Empresa { return $this->empresa; }
    public function setEmpresa(?Empresa $empresa): self { $this->empresa = $empresa; return $this; }

    public function getNombre(): ?string { return $this->nombre; }
    public function setNombre(?string $nombre): self { $this->nombre = $nombre; return $this; }

    public function getEstado(): ?bool { return $this->estado; }
    public function setEstado(bool $estado): self { $this->estado = $estado; return $this; }

    public function __toString(): string
    {
        return (string) $this->nombre;
    }
}
