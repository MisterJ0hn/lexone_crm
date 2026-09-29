<?php

namespace App\Entity;

use App\Repository\MateriaCorteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MateriaCorteRepository::class)
 */
class MateriaCorte
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Materia::class, inversedBy="materiaCortes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $materia;

    /**
     * @ORM\ManyToOne(targetEntity=Corte::class, inversedBy="materiaCortes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $corte;

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

    public function getCorte(): ?Corte
    {
        return $this->corte;
    }

    public function setCorte(?Corte $corte): self
    {
        $this->corte = $corte;

        return $this;
    }
}
