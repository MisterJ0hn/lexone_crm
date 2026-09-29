<?php

namespace App\Entity;

use App\Repository\CuadernoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CuadernoRepository::class)
 */
class Cuaderno
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\OneToMany(targetEntity=Actuacion::class, mappedBy="cuaderno")
     */
    private $actuacions;

    /**
     * @ORM\ManyToOne(targetEntity=EstrategiaJuridica::class, inversedBy="cuadernos")
     */
    private $estrategiaJuridica;

    /**
     * @ORM\ManyToOne(targetEntity=Cuaderno::class)
     */
    private $dependeCuaderno;

    public function __construct()
    {
        $this->actuacions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, Actuacion>
     */
    public function getActuacions(): Collection
    {
        return $this->actuacions;
    }

    public function addActuacion(Actuacion $actuacion): self
    {
        if (!$this->actuacions->contains($actuacion)) {
            $this->actuacions[] = $actuacion;
            $actuacion->setCuaderno($this);
        }

        return $this;
    }

    public function removeActuacion(Actuacion $actuacion): self
    {
        if ($this->actuacions->removeElement($actuacion)) {
            // set the owning side to null (unless already changed)
            if ($actuacion->getCuaderno() === $this) {
                $actuacion->setCuaderno(null);
            }
        }

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

    public function getDependeCuaderno(): ?self
    {
        return $this->dependeCuaderno;
    }

    public function setDependeCuaderno(?self $dependeCuaderno): self
    {
        $this->dependeCuaderno = $dependeCuaderno;

        return $this;
    }
}
