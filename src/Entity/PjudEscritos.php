<?php

namespace App\Entity;

use App\Repository\PjudEscritosRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PjudEscritosRepository::class)
 */
class PjudEscritos
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=PjudCausa::class, inversedBy="pjudEscritos")
     */
    private $pjudCausa;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $doc;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $anexo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fechaIngreso;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tipoEscrito;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $solicitante;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $docDescargado;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $anexoDescargado;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cuadernoId;

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

    public function getAnexo(): ?string
    {
        return $this->anexo;
    }

    public function setAnexo(?string $anexo): self
    {
        $this->anexo = $anexo;

        return $this;
    }

    public function getFechaIngreso(): ?string
    {
        return $this->fechaIngreso;
    }

    public function setFechaIngreso(?string $fechaIngreso): self
    {
        $this->fechaIngreso = $fechaIngreso;

        return $this;
    }

    public function getTipoEscrito(): ?string
    {
        return $this->tipoEscrito;
    }

    public function setTipoEscrito(?string $tipoEscrito): self
    {
        $this->tipoEscrito = $tipoEscrito;

        return $this;
    }

    public function getSolicitante(): ?string
    {
        return $this->solicitante;
    }

    public function setSolicitante(?string $solicitante): self
    {
        $this->solicitante = $solicitante;

        return $this;
    }

    public function getDocDescargado(): ?bool
    {
        return $this->docDescargado;
    }

    public function setDocDescargado(?bool $docDescargado): self
    {
        $this->docDescargado = $docDescargado;

        return $this;
    }

    public function getAnexoDescargado(): ?bool
    {
        return $this->anexoDescargado;
    }

    public function setAnexoDescargado(?bool $anexoDescargado): self
    {
        $this->anexoDescargado = $anexoDescargado;

        return $this;
    }

    public function getCuadernoId(): ?int
    {
        return $this->cuadernoId;
    }

    public function setCuadernoId(?int $cuadernoId): self
    {
        $this->cuadernoId = $cuadernoId;

        return $this;
    }
}
