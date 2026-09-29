<?php

namespace App\Entity;

use App\Repository\ContratoAnexoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContratoAnexoRepository::class)]
class ContratoAnexo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "datetime")]
    private $fechaCreacion;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $pdf;

    #[ORM\ManyToOne(targetEntity: Contrato::class, inversedBy: "contratoAnexos")]
    #[ORM\JoinColumn(nullable: false)]
    private $contrato;

    #[ORM\OneToMany(targetEntity: Cuota::class, mappedBy: "anexo")]
    private $cuotas;

    #[ORM\Column(type: "boolean")]
    private $isDesiste;

    #[ORM\Column(type: "integer", nullable: true)]
    private $folio;

    #[ORM\Column(type: "float", nullable: true)]
    private $MontoContrato;

    #[ORM\Column(type: "boolean", nullable: true)]
    private $isAbono;

    #[ORM\Column(type: "float", nullable: true)]
    private $abono;

    #[ORM\Column(type: "boolean", nullable: true)]
    private $isTotal;

    #[ORM\Column(type: "integer", nullable: true)]
    private $nCuotas;

    #[ORM\Column(type: "float", nullable: true)]
    private $valorCuota;

    #[ORM\Column(type: "integer", nullable: true)]
    private $diasPago;

    #[ORM\Column(type: "date", nullable: true)]
    private $fechaPrimerPago;

    #[ORM\Column(type: "integer", nullable: true)]
    private $vigencia;

    #[ORM\Column(type: "text", nullable: true)]
    private $observacion;

    #[ORM\Column(type: "smallint", nullable: true)]
    private $tipoAnexo;

    #[ORM\OneToMany(targetEntity: Causa::class, mappedBy: "anexo")]
    private $causas;

    #[ORM\Column(type: "boolean", nullable: true)]
    private $estado;

    public function __construct()
    {
        $this->cuotas = new ArrayCollection();
        $this->causas = new ArrayCollection();
    }
    public function setId(?int $id):self 
    {
        $this->id=$id;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFechaCreacion(): ?\DateTimeInterface
    {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(\DateTimeInterface $fechaCreacion): self
    {
        $this->fechaCreacion = $fechaCreacion;

        return $this;
    }

    public function getPdf(): ?string
    {
        return $this->pdf;
    }

    public function setPdf(?string $pdf): self
    {
        $this->pdf = $pdf;

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
     * @return Collection|Cuota[]
     */
    public function getCuotas(): Collection
    {
        return $this->cuotas;
    }

    public function addCuota(Cuota $cuota): self
    {
        if (!$this->cuotas->contains($cuota)) {
            $this->cuotas[] = $cuota;
            $cuota->setAnexo($this);
        }

        return $this;
    }

    public function removeCuota(Cuota $cuota): self
    {
        if ($this->cuotas->removeElement($cuota)) {
            // set the owning side to null (unless already changed)
            if ($cuota->getAnexo() === $this) {
                $cuota->setAnexo(null);
            }
        }

        return $this;
    }

    public function getIsDesiste(): ?bool
    {
        return $this->isDesiste;
    }

    public function setIsDesiste(bool $isDesiste): self
    {
        $this->isDesiste = $isDesiste;

        return $this;
    }

    public function getFolio(): ?int
    {
        return $this->folio;
    }

    public function setFolio(?int $folio): self
    {
        $this->folio = $folio;

        return $this;
    }

    public function getMontoContrato(): ?float
    {
        return $this->MontoContrato;
    }

    public function setMontoContrato(?float $MontoContrato): self
    {
        $this->MontoContrato = $MontoContrato;

        return $this;
    }

    public function getIsAbono(): ?bool
    {
        return $this->isAbono;
    }

    public function setIsAbono(?bool $isAbono): self
    {
        $this->isAbono = $isAbono;

        return $this;
    }

    public function getAbono(): ?float
    {
        return $this->abono;
    }

    public function setAbono(?float $abono): self
    {
        $this->abono = $abono;

        return $this;
    }

    public function getIsTotal(): ?bool
    {
        return $this->isTotal;
    }

    public function setIsTotal(?bool $isTotal): self
    {
        $this->isTotal = $isTotal;

        return $this;
    }

    public function getNCuotas(): ?int
    {
        return $this->nCuotas;
    }

    public function setNCuotas(?int $nCuotas): self
    {
        $this->nCuotas = $nCuotas;

        return $this;
    }

    public function getValorCuota(): ?float
    {
        return $this->valorCuota;
    }

    public function setValorCuota(?float $valorCuota): self
    {
        $this->valorCuota = $valorCuota;

        return $this;
    }

    public function getDiasPago(): ?int
    {
        return $this->diasPago;
    }

    public function setDiasPago(?int $diasPago): self
    {
        $this->diasPago = $diasPago;

        return $this;
    }

    public function getFechaPrimerPago(): ?\DateTimeInterface
    {
        return $this->fechaPrimerPago;
    }

    public function setFechaPrimerPago(?\DateTimeInterface $fechaPrimerPago): self
    {
        $this->fechaPrimerPago = $fechaPrimerPago;

        return $this;
    }

    public function getVigencia(): ?int
    {
        return $this->vigencia;
    }

    public function setVigencia(?int $vigencia): self
    {
        $this->vigencia = $vigencia;

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

    public function getTipoAnexo(): ?int
    {
        return $this->tipoAnexo;
    }

    public function setTipoAnexo(?int $tipoAnexo): self
    {
        $this->tipoAnexo = $tipoAnexo;

        return $this;
    }

    /**
     * @return Collection|Causa[]
     */
    public function getCausas(): Collection
    {
        return $this->causas;
    }

    public function addCausa(Causa $causa): self
    {
        if (!$this->causas->contains($causa)) {
            $this->causas[] = $causa;
            $causa->setAnexo($this);
        }

        return $this;
    }

    public function removeCausa(Causa $causa): self
    {
        if ($this->causas->removeElement($causa)) {
            // set the owning side to null (unless already changed)
            if ($causa->getAnexo() === $this) {
                $causa->setAnexo(null);
            }
        }

        return $this;
    }

    public function getEstado(): ?bool
    {
        return $this->estado;
    }

    public function setEstado(?bool $estado): self
    {
        $this->estado = $estado;

        return $this;
    }
}
