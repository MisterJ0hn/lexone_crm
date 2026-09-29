<?php

namespace App\Entity;

use App\Repository\PrivilegioRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PrivilegioRepository::class)
 */
class Privilegio
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class, inversedBy="privilegios")
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuario;


    /**
     * @ORM\ManyToOne(targetEntity=Accion::class, inversedBy="privilegios")
     * @ORM\JoinColumn(nullable=false)
     */
    private $accion;

    /**
     * @ORM\ManyToOne(targetEntity=ModuloPer::class, inversedBy="privilegios")
     */
    private $moduloPer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }


    public function getAccion(): ?Accion
    {
        return $this->accion;
    }

    public function setAccion(?Accion $accion): self
    {
        $this->accion = $accion;

        return $this;
    }

    public function getModuloPer(): ?ModuloPer
    {
        return $this->moduloPer;
    }

    public function setModuloPer(?ModuloPer $moduloPer): self
    {
        $this->moduloPer = $moduloPer;

        return $this;
    }
}
