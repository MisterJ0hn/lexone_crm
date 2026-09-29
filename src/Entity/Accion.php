<?php

namespace App\Entity;

use App\Repository\AccionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AccionRepository::class)
 */
class Accion
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
     * @ORM\Column(type="string", length=255)
     */
    private $accion;

    /**
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="acciones")
     */
    private $empresa;

    /**
     * @ORM\OneToMany(targetEntity=Privilegio::class, mappedBy="accion", orphanRemoval=true)
     */
    private $privilegios;

    /**
     * @ORM\OneToMany(targetEntity=PrivilegioTipousuario::class, mappedBy="accion", orphanRemoval=true)
     */
    private $privilegioTipousuarios;

    public function __construct()
    {
        $this->privilegios = new ArrayCollection();
        $this->privilegioTipousuarios = new ArrayCollection();
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

    public function getAccion(): ?string
    {
        return $this->accion;
    }

    public function setAccion(string $accion): self
    {
        $this->accion = $accion;

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
     * @return Collection|Privilegio[]
     */
    public function getPrivilegios(): Collection
    {
        return $this->privilegios;
    }

    public function addPrivilegio(Privilegio $privilegio): self
    {
        if (!$this->privilegios->contains($privilegio)) {
            $this->privilegios[] = $privilegio;
            $privilegio->setAccion($this);
        }

        return $this;
    }

    public function removePrivilegio(Privilegio $privilegio): self
    {
        if ($this->privilegios->removeElement($privilegio)) {
            // set the owning side to null (unless already changed)
            if ($privilegio->getAccion() === $this) {
                $privilegio->setAccion(null);
            }
        }

        return $this;
    }
    public function __toString()
    {
        return $this->getNombre();
    }

    /**
     * @return Collection|PrivilegioTipousuario[]
     */
    public function getPrivilegioTipousuarios(): Collection
    {
        return $this->privilegioTipousuarios;
    }

    public function addPrivilegioTipousuario(PrivilegioTipousuario $privilegioTipousuario): self
    {
        if (!$this->privilegioTipousuarios->contains($privilegioTipousuario)) {
            $this->privilegioTipousuarios[] = $privilegioTipousuario;
            $privilegioTipousuario->setAccion($this);
        }

        return $this;
    }

    public function removePrivilegioTipousuario(PrivilegioTipousuario $privilegioTipousuario): self
    {
        if ($this->privilegioTipousuarios->removeElement($privilegioTipousuario)) {
            // set the owning side to null (unless already changed)
            if ($privilegioTipousuario->getAccion() === $this) {
                $privilegioTipousuario->setAccion(null);
            }
        }

        return $this;
    }
}
