<?php

namespace App\Entity;

use App\Repository\ModuloRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ModuloRepository::class)
 */
class Modulo
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
    private $ruta;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nombreAlt;


    
    /**
     * @ORM\OneToMany(targetEntity=ModuloPer::class, mappedBy="modulo", orphanRemoval=true)
     */
    private $moduloPers;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\OneToMany(targetEntity=Menu::class, mappedBy="modulo")
     */
    private $menus;

    public function __construct()
    {
        
        
        $this->moduloPers = new ArrayCollection();
        $this->menus = new ArrayCollection();
       
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

    public function getRuta(): ?string
    {
        return $this->ruta;
    }

    public function setRuta(string $ruta): self
    {
        $this->ruta = $ruta;

        return $this;
    }

    public function getNombreAlt(): ?string
    {
        return $this->nombreAlt;
    }

    public function setNombreAlt(?string $nombreAlt): self
    {
        $this->nombreAlt = $nombreAlt;

        return $this;
    }

   

   
  
    public function __toString(){
        return $this->getNombre();
    }

    /**
     * @return Collection|ModuloPer[]
     */
    public function getModuloPers(): Collection
    {
        return $this->moduloPers;
    }

    public function addModuloPer(ModuloPer $moduloPer): self
    {
        if (!$this->moduloPers->contains($moduloPer)) {
            $this->moduloPers[] = $moduloPer;
            $moduloPer->setModulo($this);
        }

        return $this;
    }

    public function removeModuloPer(ModuloPer $moduloPer): self
    {
        if ($this->moduloPers->removeElement($moduloPer)) {
            // set the owning side to null (unless already changed)
            if ($moduloPer->getModulo() === $this) {
                $moduloPer->setModulo(null);
            }
        }

        return $this;
    }

    

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

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
            $menu->setModulo($this);
        }

        return $this;
    }

    public function removeMenu(Menu $menu): self
    {
        if ($this->menus->removeElement($menu)) {
            // set the owning side to null (unless already changed)
            if ($menu->getModulo() === $this) {
                $menu->setModulo(null);
            }
        }

        return $this;
    }
}
