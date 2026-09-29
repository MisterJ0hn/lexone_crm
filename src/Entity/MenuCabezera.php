<?php

namespace App\Entity;

use App\Repository\MenuCabezeraRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MenuCabezeraRepository::class)
 */
class MenuCabezera
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
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="menuCabezeras")
     */
    private $empresa;

    /**
     * @ORM\OneToMany(targetEntity=Menu::class, mappedBy="menuCabezera")
     */
    private $menus;

    /**
     * @ORM\OneToMany(targetEntity=UsuarioTipo::class, mappedBy="menuCabezera")
     */
    private $usuarioTipos;

    public function __construct()
    {
        $this->menus = new ArrayCollection();
        $this->usuarioTipos = new ArrayCollection();
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
     * @return Collection|Menu[]
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    public function addMenu(Menu $menu): self
    {
        if (!$this->menus->contains($menu)) {
            $this->menus[] = $menu;
            $menu->setMenuCabezera($this);
        }

        return $this;
    }

    public function removeMenu(Menu $menu): self
    {
        if ($this->menus->removeElement($menu)) {
            // set the owning side to null (unless already changed)
            if ($menu->getMenuCabezera() === $this) {
                $menu->setMenuCabezera(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UsuarioTipo[]
     */
    public function getUsuarioTipos(): Collection
    {
        return $this->usuarioTipos;
    }

    public function addUsuarioTipo(UsuarioTipo $usuarioTipo): self
    {
        if (!$this->usuarioTipos->contains($usuarioTipo)) {
            $this->usuarioTipos[] = $usuarioTipo;
            $usuarioTipo->setMenuCabezera($this);
        }

        return $this;
    }

    public function removeUsuarioTipo(UsuarioTipo $usuarioTipo): self
    {
        if ($this->usuarioTipos->removeElement($usuarioTipo)) {
            // set the owning side to null (unless already changed)
            if ($usuarioTipo->getMenuCabezera() === $this) {
                $usuarioTipo->setMenuCabezera(null);
            }
        }

        return $this;
    }
    public function __toString(){
        return $this->getNombre();
    }
}
