<?php

namespace App\Entity;

use App\Repository\ModuloPerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ModuloPerRepository::class)
 */
class ModuloPer
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
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="moduloPers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $empresa;

    /**
     * @ORM\ManyToOne(targetEntity=Modulo::class, inversedBy="moduloPers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $modulo;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $descripcion;

    /**
     * @ORM\OneToMany(targetEntity=Privilegio::class, mappedBy="moduloPer")
     */
    private $privilegios;

    /**
     * @ORM\OneToMany(targetEntity=PrivilegioTipousuario::class, mappedBy="moduloPer")
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

    public function getEmpresa(): ?Empresa
    {
        return $this->empresa;
    }

    public function setEmpresa(?Empresa $empresa): self
    {
        $this->empresa = $empresa;

        return $this;
    }

    public function getModulo(): ?Modulo
    {
        return $this->modulo;
    }

    public function setModulo(?Modulo $modulo): self
    {
        $this->modulo = $modulo;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;

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
            $privilegio->setModuloPer($this);
        }

        return $this;
    }

    public function removePrivilegio(Privilegio $privilegio): self
    {
        if ($this->privilegios->removeElement($privilegio)) {
            // set the owning side to null (unless already changed)
            if ($privilegio->getModuloPer() === $this) {
                $privilegio->setModuloPer(null);
            }
        }

        return $this;
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
            $privilegioTipousuario->setModuloPer($this);
        }

        return $this;
    }

    public function removePrivilegioTipousuario(PrivilegioTipousuario $privilegioTipousuario): self
    {
        if ($this->privilegioTipousuarios->removeElement($privilegioTipousuario)) {
            // set the owning side to null (unless already changed)
            if ($privilegioTipousuario->getModuloPer() === $this) {
                $privilegioTipousuario->setModuloPer(null);
            }
        }

        return $this;
    }

   
    public function __toString()
    {
        return $this->getNombre();
    }
}
