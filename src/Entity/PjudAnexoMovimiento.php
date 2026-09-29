<?php

namespace App\Entity;

use App\Repository\PjudAnexoMovimientoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PjudAnexoMovimientoRepository::class)
 */
class PjudAnexoMovimiento
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=PjudMovimiento::class, inversedBy="pjudAnexoMovimientos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pjudMovimiento;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tipo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nombreArchivo;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $mimeType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $referencia;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fecha;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $archivoDescargado;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPjudMovimiento(): ?PjudMovimiento
    {
        return $this->pjudMovimiento;
    }

    public function setPjudMovimiento(?PjudMovimiento $pjudMovimiento): self
    {
        $this->pjudMovimiento = $pjudMovimiento;

        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(?string $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getNombreArchivo(): ?string
    {
        return $this->nombreArchivo;
    }

    public function setNombreArchivo(?string $nombreArchivo): self
    {
        $this->nombreArchivo = $nombreArchivo;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getReferencia(): ?string
    {
        return $this->referencia;
    }

    public function setReferencia(?string $referencia): self
    {
        $this->referencia = $referencia;

        return $this;
    }

    public function getFecha(): ?string
    {
        return $this->fecha;
    }

    public function setFecha(string $fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getArchivoDescargado(): ?bool
    {
        return $this->archivoDescargado;
    }

    public function setArchivoDescargado(?bool $archivoDescargado): self
    {
        $this->archivoDescargado = $archivoDescargado;

        return $this;
    }
}
