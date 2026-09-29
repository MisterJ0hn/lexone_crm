<?php

namespace App\Entity;

use App\Repository\PjudEbookRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PjudEbookRepository::class)
 */
class PjudEbook
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=PjudCausa::class, inversedBy="pjudEbooks")
     */
    private $pjudCausa;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nombreArchivo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rutaRelativa;

    /**
     * @ORM\Column(type="integer")
     */
    private $tamanoBytes;

    /**
     * @ORM\Column(type="boolean")
     */
    private $descargado;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNombreArchivo(): ?string
    {
        return $this->nombreArchivo;
    }

    public function setNombreArchivo(?string $nombreArchivo): self
    {
        $this->nombreArchivo = $nombreArchivo;

        return $this;
    }

    public function getRutaRelativa(): ?string
    {
        return $this->rutaRelativa;
    }

    public function setRutaRelativa(?string $rutaRelativa): self
    {
        $this->rutaRelativa = $rutaRelativa;

        return $this;
    }

    public function getTamanoBytes(): ?int
    {
        return $this->tamanoBytes;
    }

    public function setTamanoBytes(int $tamanoBytes): self
    {
        $this->tamanoBytes = $tamanoBytes;

        return $this;
    }

    public function getDescargado(): ?bool
    {
        return $this->descargado;
    }

    public function setDescargado(bool $descargado): self
    {
        $this->descargado = $descargado;

        return $this;
    }
}
