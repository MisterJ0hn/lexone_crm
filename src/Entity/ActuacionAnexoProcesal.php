<?php

namespace App\Entity;

use App\Repository\ActuacionAnexoProcesalRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ActuacionAnexoProcesalRepository::class)
 */
class ActuacionAnexoProcesal
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Actuacion::class, inversedBy="actuacionAnexoProcesales")
     * @ORM\JoinColumn(nullable=false)
     */
    private $actuacion;

    /**
     * @ORM\ManyToOne(targetEntity=AnexoProcesal::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $anexoProcesal;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getActuacion(): ?Actuacion
    {
        return $this->actuacion;
    }

    public function setActuacion(?Actuacion $actuacion): self
    {
        $this->actuacion = $actuacion;

        return $this;
    }

    public function getAnexoProcesal(): ?AnexoProcesal
    {
        return $this->anexoProcesal;
    }

    public function setAnexoProcesal(?AnexoProcesal $anexoProcesal): self
    {
        $this->anexoProcesal = $anexoProcesal;

        return $this;
    }
}
