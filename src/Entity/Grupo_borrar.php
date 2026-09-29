<?php

namespace App\Entity;

use App\Repository\GrupoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GrupoRepository::class)
 */
class Grupo
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $utilizado;

    /**
     * @ORM\Column(type="boolean")
     */
    private $asignado;

    /**
     * @ORM\OneToMany(targetEntity=UsuarioGrupo::class, mappedBy="grupo")
     */
    private $usuarioGrupos;

    /**
     * @ORM\OneToMany(targetEntity=Contrato::class, mappedBy="grupo")
     */
    private $contratos;

    /**
     * @ORM\Column(type="boolean")
     */
    private $estado;

    public function __construct()
    {
        $this->usuarioGrupos = new ArrayCollection();
        $this->contratos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilizado(): ?bool
    {
        return $this->utilizado;
    }

    public function setUtilizado(bool $utilizado): self
    {
        $this->utilizado = $utilizado;

        return $this;
    }

    public function getAsignado(): ?bool
    {
        return $this->asignado;
    }

    public function setAsignado(bool $asignado): self
    {
        $this->asignado = $asignado;

        return $this;
    }

    /**
     * @return Collection<int, UsuarioGrupo>
     */
    public function getUsuarioGrupos(): Collection
    {
        return $this->usuarioGrupos;
    }

    public function addUsuarioGrupo(UsuarioGrupo $usuarioGrupo): self
    {
        if (!$this->usuarioGrupos->contains($usuarioGrupo)) {
            $this->usuarioGrupos[] = $usuarioGrupo;
            $usuarioGrupo->setGrupo($this);
        }

        return $this;
    }

    public function removeUsuarioGrupo(UsuarioGrupo $usuarioGrupo): self
    {
        if ($this->usuarioGrupos->removeElement($usuarioGrupo)) {
            // set the owning side to null (unless already changed)
            if ($usuarioGrupo->getGrupo() === $this) {
                $usuarioGrupo->setGrupo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Contrato>
     */
    public function getContratos(): Collection
    {
        return $this->contratos;
    }

    public function addContrato(Contrato $contrato): self
    {
        if (!$this->contratos->contains($contrato)) {
            $this->contratos[] = $contrato;
            $contrato->setGrupo($this);
        }

        return $this;
    }

    public function removeContrato(Contrato $contrato): self
    {
        if ($this->contratos->removeElement($contrato)) {
            // set the owning side to null (unless already changed)
            if ($contrato->getGrupo() === $this) {
                $contrato->setGrupo(null);
            }
        }

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
}
