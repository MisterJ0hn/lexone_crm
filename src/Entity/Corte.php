<?php

namespace App\Entity;

use App\Repository\CorteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CorteRepository::class)
 */
class Corte
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
     * @ORM\OneToMany(targetEntity=Juzgado::class, mappedBy="corte")
     */
    private $juzgados;

    /**
     * @ORM\OneToMany(targetEntity=Causa::class, mappedBy="corte")
     */
    private $causas;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $pjudCorteId;

    /**
     * @ORM\OneToMany(targetEntity=MateriaCorte::class, mappedBy="corte")
     */
    private $materiaCortes;

    public function __construct()
    {
        $this->juzgados = new ArrayCollection();
        $this->causas = new ArrayCollection();
        $this->materiaCortes = new ArrayCollection();
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
     * @return Collection<int, Juzgado>
     */
    public function getJuzgados(): Collection
    {
        return $this->juzgados;
    }

    public function addJuzgado(Juzgado $juzgado): self
    {
        if (!$this->juzgados->contains($juzgado)) {
            $this->juzgados[] = $juzgado;
            $juzgado->setCorte($this);
        }

        return $this;
    }

    public function removeJuzgado(Juzgado $juzgado): self
    {
        if ($this->juzgados->removeElement($juzgado)) {
            // set the owning side to null (unless already changed)
            if ($juzgado->getCorte() === $this) {
                $juzgado->setCorte(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Causa>
     */
    public function getCausas(): Collection
    {
        return $this->causas;
    }

    public function addCausa(Causa $causa): self
    {
        if (!$this->causas->contains($causa)) {
            $this->causas[] = $causa;
            $causa->setCorte($this);
        }

        return $this;
    }

    public function removeCausa(Causa $causa): self
    {
        if ($this->causas->removeElement($causa)) {
            // set the owning side to null (unless already changed)
            if ($causa->getCorte() === $this) {
                $causa->setCorte(null);
            }
        }

        return $this;
    }

    public function getPjudCorteId(): ?string
    {
        return $this->pjudCorteId;
    }

    public function setPjudCorteId(?string $pjudCorteId): self
    {
        $this->pjudCorteId = $pjudCorteId;

        return $this;
    }

    /**
     * @return Collection<int, MateriaCorte>
     */
    public function getMateriaCortes(): Collection
    {
        return $this->materiaCortes;
    }

    public function addMateriaCorte(MateriaCorte $materiaCorte): self
    {
        if (!$this->materiaCortes->contains($materiaCorte)) {
            $this->materiaCortes[] = $materiaCorte;
            $materiaCorte->setCorte($this);
        }

        return $this;
    }

    public function removeMateriaCorte(MateriaCorte $materiaCorte): self
    {
        if ($this->materiaCortes->removeElement($materiaCorte)) {
            // set the owning side to null (unless already changed)
            if ($materiaCorte->getCorte() === $this) {
                $materiaCorte->setCorte(null);
            }
        }

        return $this;
    }
}
