<?php

namespace App\Entity;

use App\Repository\PjudLitigantesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PjudLitigantesRepository::class)
 */
class PjudLitigantes
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=PjudCausa::class, inversedBy="pjudLitigantes")
     */
    private $pjudCausa;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $participante;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rut;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $persona;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $razonSocial;

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


    public function getParticipante(): ?string
    {
        return $this->participante;
    }

    public function setParticipante(?string $participante): self
    {
        $this->participante = $participante;

        return $this;
    }

    public function getRut(): ?string
    {
        return $this->rut;
    }

    public function setRut(?string $rut): self
    {
        $this->rut = $rut;

        return $this;
    }

    public function getPersona(): ?string
    {
        return $this->persona;
    }

    public function setPersona(?string $persona): self
    {
        $this->persona = $persona;

        return $this;
    }

    public function getRazonSocial(): ?string
    {
        return $this->razonSocial;
    }

    public function setRazonSocial(?string $razonSocial): self
    {
        $this->razonSocial = $razonSocial;

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
