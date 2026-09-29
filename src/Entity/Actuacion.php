<?php

namespace App\Entity;

use App\Repository\ActuacionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ActuacionRepository::class)
 */
class Actuacion
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
    private $Nombre;

    /**
     * @ORM\ManyToOne(targetEntity=Cuaderno::class, inversedBy="actuacions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cuaderno;

    /**
     * @ORM\OneToMany(targetEntity=AnexoProcesal::class, mappedBy="Actuacion")
     */
    private $anexoProcesales;

    /**
     * @ORM\OneToMany(targetEntity=ActuacionAnexoProcesal::class, mappedBy="actuacion")
     */
    private $actuacionAnexoProcesales;

    public function __construct()
    {
        $this->anexoProcesales = new ArrayCollection();
        $this->actuacionAnexoProcesales = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->Nombre;
    }

    public function setNombre(string $Nombre): self
    {
        $this->Nombre = $Nombre;

        return $this;
    }

    public function getCuaderno(): ?Cuaderno
    {
        return $this->cuaderno;
    }

    public function setCuaderno(?Cuaderno $cuaderno): self
    {
        $this->cuaderno = $cuaderno;

        return $this;
    }

    /**
     * @return Collection<int, AnexoProcesal>
     */
    public function getAnexoProcesales(): Collection
    {
        return $this->anexoProcesales;
    }

    public function addAnexoProcesale(AnexoProcesal $anexoProcesale): self
    {
        if (!$this->anexoProcesales->contains($anexoProcesale)) {
            $this->anexoProcesales[] = $anexoProcesale;
            $anexoProcesale->setActuacion($this);
        }

        return $this;
    }

    public function removeAnexoProcesale(AnexoProcesal $anexoProcesale): self
    {
        if ($this->anexoProcesales->removeElement($anexoProcesale)) {
            // set the owning side to null (unless already changed)
            if ($anexoProcesale->getActuacion() === $this) {
                $anexoProcesale->setActuacion(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ActuacionAnexoProcesal>
     */
    public function getActuacionAnexoProcesales(): Collection
    {
        return $this->actuacionAnexoProcesales;
    }

    public function addActuacionAnexoProcesale(ActuacionAnexoProcesal $actuacionAnexoProcesale): self
    {
        if (!$this->actuacionAnexoProcesales->contains($actuacionAnexoProcesale)) {
            $this->actuacionAnexoProcesales[] = $actuacionAnexoProcesale;
            $actuacionAnexoProcesale->setActuacion($this);
        }

        return $this;
    }

    public function removeActuacionAnexoProcesale(ActuacionAnexoProcesal $actuacionAnexoProcesale): self
    {
        if ($this->actuacionAnexoProcesales->removeElement($actuacionAnexoProcesale)) {
            // set the owning side to null (unless already changed)
            if ($actuacionAnexoProcesale->getActuacion() === $this) {
                $actuacionAnexoProcesale->setActuacion(null);
            }
        }

        return $this;
    }
}
