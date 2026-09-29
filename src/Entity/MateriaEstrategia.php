<?php

namespace App\Entity;

use App\Repository\MateriaEstrategiaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MateriaEstrategiaRepository::class)
 */
class MateriaEstrategia
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Materia::class, inversedBy="materiaEstrategias")
     * @ORM\JoinColumn(nullable=false)
     */
    private $materia;

    /**
     * @ORM\ManyToOne(targetEntity=EstrategiaJuridica::class, inversedBy="materiaEstrategias")
     * @ORM\JoinColumn(nullable=false)
     */
    private $estrategiaJuridica;

    
    /**
     * @ORM\Column(type="boolean")
     */
    private $estado;

    public function __construct()
    {
        
    }

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

    public function getEstrategiaJuridica(): ?EstrategiaJuridica
    {
        return $this->estrategiaJuridica;
    }

    public function setEstrategiaJuridica(?EstrategiaJuridica $estrategiaJuridica): self
    {
        $this->estrategiaJuridica = $estrategiaJuridica;

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
}
