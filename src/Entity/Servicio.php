<?php

namespace App\Entity;

use App\Repository\ServicioRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Servicio que presta la empresa dentro de una materia (p. ej. "Divorcio" en
 * Familia). Es independiente del estado procesal: solo se relaciona con la
 * materia y la empresa. Las causas apuntan a un servicio (Causa::$servicio).
 */
#[ORM\Entity(repositoryClass: ServicioRepository::class)]
class Servicio
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Empresa::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $empresa;

    #[ORM\ManyToOne(targetEntity: Materia::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $materia;

    #[ORM\Column(type: "string", length: 255)]
    private $nombre;

    #[ORM\Column(type: "float", nullable: true)]
    private $precio;

    #[ORM\Column(type: "boolean")]
    private $estado = true;

    public function getId(): ?int { return $this->id; }

    public function getEmpresa(): ?Empresa { return $this->empresa; }
    public function setEmpresa(?Empresa $empresa): self { $this->empresa = $empresa; return $this; }

    public function getMateria(): ?Materia { return $this->materia; }
    public function setMateria(?Materia $materia): self { $this->materia = $materia; return $this; }

    public function getNombre(): ?string { return $this->nombre; }
    public function setNombre(?string $nombre): self { $this->nombre = $nombre; return $this; }

    public function getPrecio(): ?float { return $this->precio; }
    public function setPrecio(?float $precio): self { $this->precio = $precio; return $this; }

    public function getEstado(): ?bool { return $this->estado; }
    public function setEstado(bool $estado): self { $this->estado = $estado; return $this; }

    public function __toString(): string
    {
        return (string) $this->nombre;
    }
}
