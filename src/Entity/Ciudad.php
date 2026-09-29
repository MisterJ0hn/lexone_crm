<?php

namespace App\Entity;

use App\Repository\CiudadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CiudadRepository::class)
 */
class Ciudad
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $codigo;

    /**
     * @ORM\ManyToOne(targetEntity=Region::class, inversedBy="ciudades")
     * @ORM\JoinColumn(nullable=false)
     */
    private $region;

    /**
     * @ORM\OneToMany(targetEntity=Comuna::class, mappedBy="ciudad", orphanRemoval=true)
     */
    private $comunas;

    public function __construct()
    {
        $this->comunas = new ArrayCollection();
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

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): self
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return Collection|Comuna[]
     */
    public function getComunas(): Collection
    {
        return $this->comunas;
    }

    public function addComuna(Comuna $comuna): self
    {
        if (!$this->comunas->contains($comuna)) {
            $this->comunas[] = $comuna;
            $comuna->setCiudad($this);
        }

        return $this;
    }

    public function removeComuna(Comuna $comuna): self
    {
        if ($this->comunas->removeElement($comuna)) {
            // set the owning side to null (unless already changed)
            if ($comuna->getCiudad() === $this) {
                $comuna->setCiudad(null);
            }
        }

        return $this;
    }
}
