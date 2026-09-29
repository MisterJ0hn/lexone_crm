<?php

namespace App\Entity;

use App\Repository\PrivilegioTipousuarioRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PrivilegioTipousuarioRepository::class)
 */
class PrivilegioTipousuario
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Accion::class, inversedBy="privilegioTipousuarios")
     * @ORM\JoinColumn(nullable=false)
     */
    private $accion;

   
    /**
     * @ORM\ManyToOne(targetEntity=UsuarioTipo::class, inversedBy="privilegioTipousuarios")
     * @ORM\JoinColumn(nullable=false)
     */
    private $tipousuario;

    /**
     * @ORM\ManyToOne(targetEntity=ModuloPer::class, inversedBy="privilegioTipousuarios")
     */
    private $moduloPer;

    public function getId(): ?int
    {
        return $this->id;
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

   

    public function getTipousuario(): ?UsuarioTipo
    {
        return $this->tipousuario;
    }

    public function setTipousuario(?UsuarioTipo $tipousuario): self
    {
        $this->tipousuario = $tipousuario;

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
