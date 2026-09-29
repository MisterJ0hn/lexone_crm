<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MenuRepository::class)
 */
class Menu
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="menus")
     * @ORM\JoinColumn(nullable=false)
     */
    private $empresa;



    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\ManyToOne(targetEntity=Menu::class, inversedBy="menus")
     */
    private $dependeDe;

    /**
     * @ORM\OneToMany(targetEntity=Menu::class, mappedBy="dependeDe")
     */
    private $menus;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $icono;

    

    /**
     * @ORM\ManyToOne(targetEntity=MenuCabezera::class, inversedBy="menus")
     */
    private $menuCabezera;

    /**
     * @ORM\ManyToOne(targetEntity=Modulo::class, inversedBy="menus")
     */
    private $modulo;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $orden;

    public function __construct()
    {
        $this->menus = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

   

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getDependeDe(): ?self
    {
        return $this->dependeDe;
    }

    public function setDependeDe(?self $dependeDe): self
    {
        $this->dependeDe = $dependeDe;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    public function addMenu(self $menu): self
    {
        if (!$this->menus->contains($menu)) {
            $this->menus[] = $menu;
            $menu->setDependeDe($this);
        }

        return $this;
    }

    public function removeMenu(self $menu): self
    {
        if ($this->menus->removeElement($menu)) {
            // set the owning side to null (unless already changed)
            if ($menu->getDependeDe() === $this) {
                $menu->setDependeDe(null);
            }
        }

        return $this;
    }
    public function __toString()
    {
        return $this->getNombre();
    }

    public function getIcono(): ?string
    {
        return $this->icono;
    }

    public function setIcono(?string $icono): self
    {
        $this->icono = $icono;

        return $this;
    }

   

    public function getMenuCabezera(): ?MenuCabezera
    {
        return $this->menuCabezera;
    }

    public function setMenuCabezera(?MenuCabezera $menuCabezera): self
    {
        $this->menuCabezera = $menuCabezera;

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

    public function getOrden(): ?int
    {
        return $this->orden;
    }

    public function setOrden(?int $orden): self
    {
        $this->orden = $orden;

        return $this;
    }
}
