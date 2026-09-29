<?php

namespace App\Entity;

use App\Repository\CobranzaRespuestaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CobranzaRespuestaRepository::class)
 */
class CobranzaRespuesta
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
     * @ORM\ManyToOne(targetEntity=Empresa::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $empresa;

    /**
     * @ORM\OneToMany(targetEntity=Cobranza::class, mappedBy="respuesta")
     */
    private $cobranzas;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isFechaCompromiso;

    public function __construct()
    {
        $this->cobranzas = new ArrayCollection();
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
     * @return Collection|Cobranza[]
     */
    public function getCobranzas(): Collection
    {
        return $this->cobranzas;
    }

    public function addCobranza(Cobranza $cobranza): self
    {
        if (!$this->cobranzas->contains($cobranza)) {
            $this->cobranzas[] = $cobranza;
            $cobranza->setRespuesta($this);
        }

        return $this;
    }

    public function removeCobranza(Cobranza $cobranza): self
    {
        if ($this->cobranzas->removeElement($cobranza)) {
            // set the owning side to null (unless already changed)
            if ($cobranza->getRespuesta() === $this) {
                $cobranza->setRespuesta(null);
            }
        }

        return $this;
    }
    public function __toString(){
        return $this->nombre;
    }

    public function getIsFechaCompromiso(): ?bool
    {
        return $this->isFechaCompromiso;
    }

    public function setIsFechaCompromiso(?bool $isFechaCompromiso): self
    {
        $this->isFechaCompromiso = $isFechaCompromiso;

        return $this;
    }
}
