<?php

namespace App\Entity;

use App\Repository\PjudInformacionReceptorRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PjudInformacionReceptorRepository::class)
 */
class PjudInformacionReceptor
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $Cuaderno;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $datosRetiro;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $fechaRetiro;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $estado;

    /**
     * @ORM\ManyToOne(targetEntity=PjudCausa::class, inversedBy="pjudInformacionReceptors")
     */
    private $pjudCausa;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCuaderno(): ?string
    {
        return $this->Cuaderno;
    }

    public function setCuaderno(?string $Cuaderno): self
    {
        $this->Cuaderno = $Cuaderno;

        return $this;
    }

    public function getDatosRetiro(): ?string
    {
        return $this->datosRetiro;
    }

    public function setDatosRetiro(?string $datosRetiro): self
    {
        $this->datosRetiro = $datosRetiro;

        return $this;
    }

    public function getFechaRetiro(): ?string
    {
        return $this->fechaRetiro;
    }

    public function setFechaRetiro(?string $fechaRetiro): self
    {
        $this->fechaRetiro = $fechaRetiro;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(?string $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    public function getPjudCausa(): ?PjudCausa
    {
        return $this->pjudCausa;
    }

    public function setPjudCausa(?PjudCausa $pjudCausa): self
    {
        $this->pjudCausa = $pjudCausa;

        return $this;
    }
}
