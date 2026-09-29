<?php

namespace App\Entity;

use App\Repository\ImportacionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ImportacionRepository::class)
 */
class Importacion
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
     * @ORM\Column(type="datetime")
     */
    private $fechaCarga;

    /**
     * @ORM\Column(type="text")
     */
    private $url;

    /**
     * @ORM\ManyToOne(targetEntity=Cuenta::class, inversedBy="importacions")
     */
    private $cuenta;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class, inversedBy="importacions")
     */
    private $usuarioCarga;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $estado;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tipoImportacion;

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

    public function getFechaCarga(): ?\DateTimeInterface
    {
        return $this->fechaCarga;
    }

    public function setFechaCarga(\DateTimeInterface $fechaCarga): self
    {
        $this->fechaCarga = $fechaCarga;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getCuenta(): ?Cuenta
    {
        return $this->cuenta;
    }

    public function setCuenta(?Cuenta $cuenta): self
    {
        $this->cuenta = $cuenta;

        return $this;
    }

    public function getUsuarioCarga(): ?Usuario
    {
        return $this->usuarioCarga;
    }

    public function setUsuarioCarga(?Usuario $usuarioCarga): self
    {
        $this->usuarioCarga = $usuarioCarga;

        return $this;
    }

    public function getEstado(): ?int
    {
        return $this->estado;
    }

    public function setEstado(?int $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    public function getTipoImportacion(): ?int
    {
        return $this->tipoImportacion;
    }

    public function setTipoImportacion(?int $tipoImportacion): self
    {
        $this->tipoImportacion = $tipoImportacion;

        return $this;
    }
}
