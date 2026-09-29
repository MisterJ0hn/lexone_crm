<?php

namespace App\Entity;

use App\Repository\EstrategiaJuridicaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EstrategiaJuridicaRepository::class)]
class EstrategiaJuridica
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255)]
    private $Nombre;

    #[ORM\ManyToOne(targetEntity: Empresa::class, inversedBy: "estrategiaJuridicas")]
    private $empresa;

    #[ORM\OneToMany(targetEntity: Contrato::class, mappedBy: "estrategiaJuridica")]
    private $contratos;

    #[ORM\OneToMany(targetEntity: MateriaEstrategia::class, mappedBy: "estrategiaJuridica")]
    private $materiaEstrategias;

    #[ORM\ManyToOne(targetEntity: LineaTiempo::class, inversedBy: "estrategiaJuridicas")]
    private $lineaTiempo;

    #[ORM\Column(type: "float", nullable: true)]
    private $precio;

    public function __construct()
    {
        $this->contratos = new ArrayCollection();
        $this->materiaEstrategias = new ArrayCollection();
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
     * @return Collection|Contrato[]
     */
    public function getContratos(): Collection
    {
        return $this->contratos;
    }

    public function addContrato(Contrato $contrato): self
    {
        if (!$this->contratos->contains($contrato)) {
            $this->contratos[] = $contrato;
            $contrato->setEstrategiaJuridica($this);
        }

        return $this;
    }

    public function removeContrato(Contrato $contrato): self
    {
        if ($this->contratos->removeElement($contrato)) {
            // set the owning side to null (unless already changed)
            if ($contrato->getEstrategiaJuridica() === $this) {
                $contrato->setEstrategiaJuridica(null);
            }
        }

        return $this;
    }
    public function __toString(){
        return $this->getNombre();
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
            $materiaEstrategia->setEstrategiaJuridica($this);
        }

        return $this;
    }

    public function removeMateriaEstrategia(MateriaEstrategia $materiaEstrategia): self
    {
        if ($this->materiaEstrategias->removeElement($materiaEstrategia)) {
            // set the owning side to null (unless already changed)
            if ($materiaEstrategia->getEstrategiaJuridica() === $this) {
                $materiaEstrategia->setEstrategiaJuridica(null);
            }
        }

        return $this;
    }

    public function getLineaTiempo(): ?LineaTiempo
    {
        return $this->lineaTiempo;
    }

    public function setLineaTiempo(?LineaTiempo $lineaTiempo): self
    {
        $this->lineaTiempo = $lineaTiempo;

        return $this;
    }

    public function getPrecio(): ?float
    {
        return $this->precio;
    }

    public function setPrecio(?float $precio): self
    {
        $this->precio = $precio;

        return $this;
    }
}
