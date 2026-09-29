<?php

namespace App\Entity;

use App\Repository\MateriaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MateriaRepository::class)]
class Materia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 50)]
    private $nombre;

    #[ORM\ManyToOne(targetEntity: Empresa::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $empresa;

    #[ORM\OneToMany(targetEntity: MateriaEstrategia::class, mappedBy: "materia")]
    private $materiaEstrategias;

    #[ORM\OneToMany(targetEntity: CausaLetra::class, mappedBy: "materia")]
    private $causaLetras;

    #[ORM\OneToMany(targetEntity: Causa::class, mappedBy: "materia")]
    private $causas;

    public function __construct()
    {
        $this->materiaEstrategias = new ArrayCollection();
        $this->causaLetras = new ArrayCollection();
        $this->causas = new ArrayCollection();
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

    public function getEmpresa(): ?Empresa
    {
        return $this->empresa;
    }

    public function setEmpresa(?Empresa $empresa): self
    {
        $this->empresa = $empresa;

        return $this;
    }

    /**
     * @return Collection|MateriaEstrategia[]
     */
    public function getMateriaEstrategias(): Collection
    {
        return $this->materiaEstrategias;
    }

    public function addMateriaEstrategia(MateriaEstrategia $materiaEstrategia): self
    {
        if (!$this->materiaEstrategias->contains($materiaEstrategia)) {
            $this->materiaEstrategias[] = $materiaEstrategia;
            $materiaEstrategia->setMateria($this);
        }

        return $this;
    }

    public function removeMateriaEstrategia(MateriaEstrategia $materiaEstrategia): self
    {
        if ($this->materiaEstrategias->removeElement($materiaEstrategia)) {
            // set the owning side to null (unless already changed)
            if ($materiaEstrategia->getMateria() === $this) {
                $materiaEstrategia->setMateria(null);
            }
        }

        return $this;
    }
    public function __toString()
    {
        return $this->getNombre();
    }

    /**
     * @return Collection<int, CausaLetra>
     */
    public function getCausaLetras(): Collection
    {
        return $this->causaLetras;
    }

    public function addCausaLetra(CausaLetra $causaLetra): self
    {
        if (!$this->causaLetras->contains($causaLetra)) {
            $this->causaLetras[] = $causaLetra;
            $causaLetra->setMateria($this);
        }

        return $this;
    }

    public function removeCausaLetra(CausaLetra $causaLetra): self
    {
        if ($this->causaLetras->removeElement($causaLetra)) {
            // set the owning side to null (unless already changed)
            if ($causaLetra->getMateria() === $this) {
                $causaLetra->setMateria(null);
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
            $causa->setMateria($this);
        }

        return $this;
    }

    public function removeCausa(Causa $causa): self
    {
        if ($this->causas->removeElement($causa)) {
            if ($causa->getMateria() === $this) {
                $causa->setMateria(null);
            }
        }

        return $this;
    }
}
