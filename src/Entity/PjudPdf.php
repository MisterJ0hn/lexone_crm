<?php

namespace App\Entity;

use App\Repository\PjudPdfRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PjudPdfRepository::class)
 */
class PjudPdf
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=PjudMovimiento::class)
     */
    private $movimiento;

    /**
     * @ORM\ManyToOne(targetEntity=PjudMovimiento::class, inversedBy="pjudPdfs")
     */
    private $pjudMovimiento;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $tipo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nombreArchivo;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $contenidoBase64;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tamanoBytes;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $CreatedAt;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $archivoDescargado;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMovimiento(): ?PjudMovimiento
    {
        return $this->movimiento;
    }

    public function setMovimiento(?PjudMovimiento $movimiento): self
    {
        $this->movimiento = $movimiento;

        return $this;
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

    public function setTipo(string $tipo): self
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

    public function getContenidoBase64(): ?string
    {
        return $this->contenidoBase64;
    }

    public function setContenidoBase64(?string $contenidoBase64): self
    {
        $this->contenidoBase64 = $contenidoBase64;

        return $this;
    }

    public function getTamanoBytes(): ?int
    {
        return $this->tamanoBytes;
    }

    public function setTamanoBytes(?int $tamanoBytes): self
    {
        $this->tamanoBytes = $tamanoBytes;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(?\DateTimeInterface $CreatedAt): self
    {
        $this->CreatedAt = $CreatedAt;

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
