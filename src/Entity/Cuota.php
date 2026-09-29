<?php

namespace App\Entity;

use App\Repository\CuotaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CuotaRepository::class)
 */
class Cuota
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $numero;

    /**
     * @ORM\Column(type="date")
     */
    private $fechaPago;

    /**
     * @ORM\Column(type="integer")
     */
    private $monto;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $pagado;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $anular;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaAnulacion;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     */
    private $usuarioAnulacion;

    /**
     * @ORM\ManyToOne(targetEntity=Contrato::class, inversedBy="detalleCuotas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $contrato;

    /**
     * @ORM\OneToMany(targetEntity=PagoCuotas::class, mappedBy="cuota")
     */
    private $pagoCuotas;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isMulta;

    /**
     * @ORM\ManyToOne(targetEntity=ContratoAnexo::class, inversedBy="cuotas")
     */
    private $anexo;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $invoiceId;


    public function __construct()
    {
        $this->pagoCuotas = new ArrayCollection();
        
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): self
    {
        $this->numero = $numero;

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

    public function getMonto(): ?int
    {
        return $this->monto;
    }

    public function setMonto(int $monto): self
    {
        $this->monto = $monto;

        return $this;
    }

    public function getPagado(): ?int
    {
        return $this->pagado;
    }

    public function setPagado(?int $pagado): self
    {
        $this->pagado = $pagado;

        return $this;
    }

    public function getAnular(): ?bool
    {
        return $this->anular;
    }

    public function setAnular(?bool $anular): self
    {
        $this->anular = $anular;

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

    public function getUsuarioAnulacion(): ?Usuario
    {
        return $this->usuarioAnulacion;
    }

    public function setUsuarioAnulacion(?Usuario $usuarioAnulacion): self
    {
        $this->usuarioAnulacion = $usuarioAnulacion;

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
            $pagoCuota->setCuota($this);
        }

        return $this;
    }

    public function removePagoCuota(PagoCuotas $pagoCuota): self
    {
        if ($this->pagoCuotas->removeElement($pagoCuota)) {
            // set the owning side to null (unless already changed)
            if ($pagoCuota->getCuota() === $this) {
                $pagoCuota->setCuota(null);
            }
        }

        return $this;
    }

    public function getIsMulta(): ?bool
    {
        return $this->isMulta;
    }

    public function setIsMulta(?bool $isMulta): self
    {
        $this->isMulta = $isMulta;

        return $this;
    }

    public function getAnexo(): ?ContratoAnexo
    {
        return $this->anexo;
    }

    public function setAnexo(?ContratoAnexo $anexo): self
    {
        $this->anexo = $anexo;

        return $this;
    }

    public function getInvoiceId(): ?string
    {
        return $this->invoiceId;
    }

    public function setInvoiceId(?string $invoiceId): self
    {
        $this->invoiceId = $invoiceId;

        return $this;
    }

    
}
