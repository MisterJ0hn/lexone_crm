<?php

namespace App\Entity;

use App\Repository\LineaTiempoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LineaTiempoRepository::class)
 */
class LineaTiempo
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $codigo;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $orden;

    /**
     * @ORM\OneToMany(targetEntity=LineaTiempoEtapas::class, mappedBy="lineaTiempo")
     */
    private $lineaTiempoEtapas;

    /**
     * @ORM\OneToMany(targetEntity=EstrategiaJuridica::class, mappedBy="lineaTiempo")
     */
    private $estrategiaJuridicas;

    public function __construct()
    {
        $this->lineaTiempoEtapas = new ArrayCollection();
        $this->estrategiaJuridicas = new ArrayCollection();
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

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): self
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getOrden(): ?int
    {
        return $this->orden;
    }

    public function setOrden(?int $orden): self
    {
        $this->orden = $orden;

        return $this;
    }

    /**
     * @return Collection<int, LineaTiempoEtapas>
     */
    public function getLineaTiempoEtapas(): Collection
    {
        return $this->lineaTiempoEtapas;
    }

    public function addLineaTiempoEtapa(LineaTiempoEtapas $lineaTiempoEtapa): self
    {
        if (!$this->lineaTiempoEtapas->contains($lineaTiempoEtapa)) {
            $this->lineaTiempoEtapas[] = $lineaTiempoEtapa;
            $lineaTiempoEtapa->setLineaTiempo($this);
        }

        return $this;
    }

    public function removeLineaTiempoEtapa(LineaTiempoEtapas $lineaTiempoEtapa): self
    {
        if ($this->lineaTiempoEtapas->removeElement($lineaTiempoEtapa)) {
            // set the owning side to null (unless already changed)
            if ($lineaTiempoEtapa->getLineaTiempo() === $this) {
                $lineaTiempoEtapa->setLineaTiempo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EstrategiaJuridica>
     */
    public function getEstrategiaJuridicas(): Collection
    {
        return $this->estrategiaJuridicas;
    }

    public function addEstrategiaJuridica(EstrategiaJuridica $estrategiaJuridica): self
    {
        if (!$this->estrategiaJuridicas->contains($estrategiaJuridica)) {
            $this->estrategiaJuridicas[] = $estrategiaJuridica;
            $estrategiaJuridica->setLineaTiempo($this);
        }

        return $this;
    }

    public function removeEstrategiaJuridica(EstrategiaJuridica $estrategiaJuridica): self
    {
        if ($this->estrategiaJuridicas->removeElement($estrategiaJuridica)) {
            // set the owning side to null (unless already changed)
            if ($estrategiaJuridica->getLineaTiempo() === $this) {
                $estrategiaJuridica->setLineaTiempo(null);
            }
        }

        return $this;
    }

}
