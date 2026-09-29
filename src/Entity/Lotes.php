<?php

namespace App\Entity;

use App\Repository\LotesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LotesRepository::class)
 */
class Lotes
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
     * @ORM\ManyToOne(targetEntity=Empresa::class)
     */
    private $empresa;

    /**
     * @ORM\Column(type="boolean")
     */
    private $estado;

    /**
     * @ORM\Column(type="integer")
     */
    private $orden;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isUtilizado;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAsignado;

    /**
     * @ORM\OneToMany(targetEntity=UsuarioLote::class, mappedBy="lote")
     */
    private $usuarioLotes;

    public function __construct()
    {
        $this->usuarioLotes = new ArrayCollection();
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

    public function getEstado(): ?bool
    {
        return $this->estado;
    }

    public function setEstado(bool $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    public function getOrden(): ?int
    {
        return $this->orden;
    }

    public function setOrden(int $orden): self
    {
        $this->orden = $orden;

        return $this;
    }

    public function getIsUtilizado(): ?bool
    {
        return $this->isUtilizado;
    }

    public function setIsUtilizado(bool $isUtilizado): self
    {
        $this->isUtilizado = $isUtilizado;

        return $this;
    }

    public function getIsAsignado(): ?bool
    {
        return $this->isAsignado;
    }

    public function setIsAsignado(bool $isAsignado): self
    {
        $this->isAsignado = $isAsignado;

        return $this;
    }

    /**
     * @return Collection|UsuarioLote[]
     */
    public function getUsuarioLotes(): Collection
    {
        return $this->usuarioLotes;
    }

    public function addUsuarioLote(UsuarioLote $usuarioLote): self
    {
        if (!$this->usuarioLotes->contains($usuarioLote)) {
            $this->usuarioLotes[] = $usuarioLote;
            $usuarioLote->setLote($this);
        }

        return $this;
    }

    public function removeUsuarioLote(UsuarioLote $usuarioLote): self
    {
        if ($this->usuarioLotes->removeElement($usuarioLote)) {
            // set the owning side to null (unless already changed)
            if ($usuarioLote->getLote() === $this) {
                $usuarioLote->setLote(null);
            }
        }

        return $this;
    }

   
}
