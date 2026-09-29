<?php

namespace App\Entity;

use App\Repository\InfComisionCobradoresRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InfComisionCobradoresRepository::class)
 */
class InfComisionCobradores
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
    private $sesion;

    /**
     * @ORM\ManyToOne(targetEntity=Contrato::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $contrato;

    /**
     * @ORM\ManyToOne(targetEntity=Cobranza::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $cobranza;

    /**
     * @ORM\ManyToOne(targetEntity=Pago::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $pago;

    /**
     * @ORM\ManyToOne(targetEntity=Cuota::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $cuota;

    /**
     * @ORM\Column(type="float")
     */
    private $tiempoGestion;

    /**
     * @ORM\Column(type="integer")
     */
    private $diasMora;

    /**
     * @ORM\Column(type="integer")
     */
    private $monto;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSesion(): ?string
    {
        return $this->sesion;
    }

    public function setSesion(string $sesion): self
    {
        $this->sesion = $sesion;

        return $this;
    }

    public function getContrato(): ?Contrato
    {
        return $this->contrato;
    }

    public function setContrato(?Contrato $contrato): self
    {
        $this->contrato = $contrato;

        return $this;
    }

    public function getCobranza(): ?Cobranza
    {
        return $this->cobranza;
    }

    public function setCobranza(?Cobranza $cobranza): self
    {
        $this->cobranza = $cobranza;

        return $this;
    }

    public function getPago(): ?Pago
    {
        return $this->pago;
    }

    public function setPago(?Pago $pago): self
    {
        $this->pago = $pago;

        return $this;
    }

    public function getCuota(): ?Cuota
    {
        return $this->cuota;
    }

    public function setCuota(?Cuota $cuota): self
    {
        $this->cuota = $cuota;

        return $this;
    }

    public function getTiempoGestion(): ?float
    {
        return $this->tiempoGestion;
    }

    public function setTiempoGestion(float $tiempoGestion): self
    {
        $this->tiempoGestion = $tiempoGestion;

        return $this;
    }

    public function getDiasMora(): ?int
    {
        return $this->diasMora;
    }

    public function setDiasMora(int $diasMora): self
    {
        $this->diasMora = $diasMora;

        return $this;
    }

    public function getMonto(): ?int
    {
        return $this->monto;
    }

    public function setMonto(int $monto): self
    {
        $this->monto = $monto;

        return $this;
    }
}
