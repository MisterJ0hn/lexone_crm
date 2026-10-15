<?php

namespace App\Entity;

use App\Repository\MateriaJuzgadoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Juzgados que atienden una materia. Las cortes se comparten entre materias
 * (ver MateriaCorte), pero cada materia tiene sus propios juzgados.
 */
#[ORM\Entity(repositoryClass: MateriaJuzgadoRepository::class)]
#[ORM\UniqueConstraint(name: "uniq_materia_juzgado", columns: ["materia_id", "juzgado_id"])]
class MateriaJuzgado
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Materia::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $materia;

    #[ORM\ManyToOne(targetEntity: Juzgado::class, inversedBy: "materiaJuzgados")]
    #[ORM\JoinColumn(nullable: false)]
    private $juzgado;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMateria(): ?Materia
    {
        return $this->materia;
    }

    public function setMateria(?Materia $materia): self
    {
        $this->materia = $materia;

        return $this;
    }

    public function getJuzgado(): ?Juzgado
    {
        return $this->juzgado;
    }

    public function setJuzgado(?Juzgado $juzgado): self
    {
        $this->juzgado = $juzgado;

        return $this;
    }
}
