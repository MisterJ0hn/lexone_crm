<?php

namespace App\Entity;

use App\Repository\PagoCuotasRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PagoCuotasRepository::class)
 */
class PagoCuotas
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Pago::class, inversedBy="pagoCuotas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pago;

    /**
     * @ORM\ManyToOne(targetEntity=Cuota::class, inversedBy="pagoCuotas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cuota;

    /**
     * @ORM\Column(type="integer")
     */
    private $monto;

    public function getId(): ?int
    {
        return $this->id;
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
