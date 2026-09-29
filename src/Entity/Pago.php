<?php

namespace App\Entity;

use App\Repository\PagoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PagoRepository::class)
 */
class Pago
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=PagoTipo::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $pagoTipo;

    /**
     * @ORM\ManyToOne(targetEntity=PagoCanal::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $pagoCanal;

    /**
     * @ORM\Column(type="integer")
     */
    private $monto;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $boleta;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $observacion;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaPago;

    /**
     * @ORM\Column(type="time")
     */
    private $horaPago;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaRegistro;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuarioRegistro;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comprobante;

    /**
     * @ORM\OneToMany(targetEntity=PagoCuotas::class, mappedBy="pago", orphanRemoval=true)
     */
    private $pagoCuotas;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fechaIngreso;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ncomprobante;

    /**
     * @ORM\ManyToOne(targetEntity=CuentaCorriente::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $cuentaCorriente;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $anulado;

    /**
     * @ORM\ManyToOne(targetEntity=usuario::class)
     */
    private $usuarioAnulacion;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaAnulacion;

    public function __construct()
    {
        $this->pagoCuotas = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPagoTipo(): ?PagoTipo
    {
        return $this->pagoTipo;
    }

    public function setPagoTipo(?PagoTipo $pagoTipo): self
    {
        $this->pagoTipo = $pagoTipo;

        return $this;
    }

    public function getPagoCanal(): ?PagoCanal
    {
        return $this->pagoCanal;
    }

    public function setPagoCanal(?PagoCanal $pagoCanal): self
    {
        $this->pagoCanal = $pagoCanal;

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

    public function getBoleta(): ?string
    {
        return $this->boleta;
    }

    public function setBoleta(?string $boleta): self
    {
        $this->boleta = $boleta;

        return $this;
    }

    public function getObservacion(): ?string
    {
        return $this->observacion;
    }

    public function setObservacion(?string $observacion): self
    {
        $this->observacion = $observacion;

        return $this;
    }

    public function getFechaPago(): ?\DateTimeInterface
    {
        return $this->fechaPago;
    }

    public function setFechaPago(\DateTimeInterface $fechaPago): self
    {
        $this->fechaPago = $fechaPago;

        return $this;
    }

    public function getHoraPago(): ?\DateTimeInterface
    {
        return $this->horaPago;
    }

    public function setHoraPago(\DateTimeInterface $horaPago): self
    {
        $this->horaPago = $horaPago;

        return $this;
    }

    public function getFechaRegistro(): ?\DateTimeInterface
    {
        return $this->fechaRegistro;
    }

    public function setFechaRegistro(\DateTimeInterface $fechaRegistro): self
    {
        $this->fechaRegistro = $fechaRegistro;

        return $this;
    }

    public function getUsuarioRegistro(): ?Usuario
    {
        return $this->usuarioRegistro;
    }

    public function setUsuarioRegistro(?Usuario $usuarioRegistro): self
    {
        $this->usuarioRegistro = $usuarioRegistro;

        return $this;
    }

    public function getComprobante(): ?string
    {
        return $this->comprobante;
    }

    public function setComprobante(?string $comprobante): self
    {
        $this->comprobante = $comprobante;

        return $this;
    }

    /**
     * @return Collection|PagoCuotas[]
     */
    public function getPagoCuotas(): Collection
    {
        return $this->pagoCuotas;
    }

    public function addPagoCuota(PagoCuotas $pagoCuota): self
    {
        if (!$this->pagoCuotas->contains($pagoCuota)) {
            $this->pagoCuotas[] = $pagoCuota;
            $pagoCuota->setPago($this);
        }

        return $this;
    }

    public function removePagoCuota(PagoCuotas $pagoCuota): self
    {
        if ($this->pagoCuotas->removeElement($pagoCuota)) {
            // set the owning side to null (unless already changed)
            if ($pagoCuota->getPago() === $this) {
                $pagoCuota->setPago(null);
            }
        }

        return $this;
    }

    public function getFechaIngreso(): ?\DateTimeInterface
    {
        return $this->fechaIngreso;
    }

    public function setFechaIngreso(?\DateTimeInterface $fechaIngreso): self
    {
        $this->fechaIngreso = $fechaIngreso;

        return $this;
    }

    public function getNcomprobante(): ?string
    {
        return $this->ncomprobante;
    }

    public function setNcomprobante(?string $ncomprobante): self
    {
        $this->ncomprobante = $ncomprobante;

        return $this;
    }

    public function getCuentaCorriente(): ?CuentaCorriente
    {
        return $this->cuentaCorriente;
    }

    public function setCuentaCorriente(?CuentaCorriente $cuentaCorriente): self
    {
        $this->cuentaCorriente = $cuentaCorriente;

        return $this;
    }

    public function getAnulado(): ?bool
    {
        return $this->anulado;
    }

    public function setAnulado(?bool $anulado): self
    {
        $this->anulado = $anulado;

        return $this;
    }

    public function getUsuarioAnulacion(): ?usuario
    {
        return $this->usuarioAnulacion;
    }

    public function setUsuarioAnulacion(?usuario $usuarioAnulacion): self
    {
        $this->usuarioAnulacion = $usuarioAnulacion;

        return $this;
    }

    public function getFechaAnulacion(): ?\DateTimeInterface
    {
        return $this->fechaAnulacion;
    }

    public function setFechaAnulacion(?\DateTimeInterface $fechaAnulacion): self
    {
        $this->fechaAnulacion = $fechaAnulacion;

        return $this;
    }

   
}
