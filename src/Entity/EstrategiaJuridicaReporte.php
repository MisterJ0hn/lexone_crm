<?php

namespace App\Entity;

use App\Repository\EstrategiaJuridicaReporteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EstrategiaJuridicaReporteRepository::class)
 */
class EstrategiaJuridicaReporte
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=EstrategiaJuridica::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $estrategiaJuridica;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\OneToMany(targetEntity=EstrategiaJuridicaReporteArchivos::class, mappedBy="estrategiaJuridicaReporte")
     */
    private $estrategiaJuridicaReporteArchivos;

    public function __construct()
    {
        $this->estrategiaJuridicaReporteArchivos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
     * @return Collection<int, EstrategiaJuridicaReporteArchivos>
     */
    public function getEstrategiaJuridicaReporteArchivos(): Collection
    {
        return $this->estrategiaJuridicaReporteArchivos;
    }

    public function addEstrategiaJuridicaReporteArchivo(EstrategiaJuridicaReporteArchivos $estrategiaJuridicaReporteArchivo): self
    {
        if (!$this->estrategiaJuridicaReporteArchivos->contains($estrategiaJuridicaReporteArchivo)) {
            $this->estrategiaJuridicaReporteArchivos[] = $estrategiaJuridicaReporteArchivo;
            $estrategiaJuridicaReporteArchivo->setEstrategiaJuridicaReporte($this);
        }

        return $this;
    }

    public function removeEstrategiaJuridicaReporteArchivo(EstrategiaJuridicaReporteArchivos $estrategiaJuridicaReporteArchivo): self
    {
        if ($this->estrategiaJuridicaReporteArchivos->removeElement($estrategiaJuridicaReporteArchivo)) {
            // set the owning side to null (unless already changed)
            if ($estrategiaJuridicaReporteArchivo->getEstrategiaJuridicaReporte() === $this) {
                $estrategiaJuridicaReporteArchivo->setEstrategiaJuridicaReporte(null);
            }
        }

        return $this;
    }
}
