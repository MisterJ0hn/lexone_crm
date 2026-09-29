<?php

namespace App\Entity;

use App\Repository\PjudAnexoCausaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PjudAnexoCausaRepository::class)
 */
class PjudAnexoCausa
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=PjudCausa::class, inversedBy="pjudAnexoCausas")
     */
    private $pjudCausa;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $doc;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fecha;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $referencia;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $archivoDescargado;

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

    public function getDoc(): ?string
    {
        return $this->doc;
    }

    public function setDoc(?string $doc): self
    {
        $this->doc = $doc;

        return $this;
    }

    public function getFecha(): ?string
    {
        return $this->fecha;
    }

    public function setFecha(?string $fecha): self
    {
        $this->fecha = $fecha;

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
