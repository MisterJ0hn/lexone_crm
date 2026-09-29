<?php

namespace App\Entity;

use App\Repository\PjudMovimientoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PjudMovimientoRepository::class)
 */
class PjudMovimiento
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=PjudCausa::class, inversedBy="pjudMovimientos")
     */
    private $pjudCausa;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $folio;

    /**
     * @ORM\Column(type="boolean")
     */
    private $tienePdf;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $etapa;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $tramite;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $descripcion;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fecha;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $foja;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $indice;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cuadernoId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cuadernoNombre;

    /**
     * @ORM\OneToMany(targetEntity=PjudPdf::class, mappedBy="pjudMovimiento")
     */
    private $pjudPdfs;

    /**
     * @ORM\OneToMany(targetEntity=PjudAnexoMovimiento::class, mappedBy="pjudMovimiento")
     */
    private $pjudAnexoMovimientos;

    public function __construct()
    {
        $this->pjudPdfs = new ArrayCollection();
        $this->pjudAnexoMovimientos = new ArrayCollection();
    }

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

    public function getFolio(): ?string
    {
        return $this->folio;
    }

    public function setFolio(?string $folio): self
    {
        $this->folio = $folio;

        return $this;
    }

    public function getTienePdf(): ?bool
    {
        return $this->tienePdf;
    }

    public function setTienePdf(bool $tienePdf): self
    {
        $this->tienePdf = $tienePdf;

        return $this;
    }

    public function getEtapa(): ?string
    {
        return $this->etapa;
    }

    public function setEtapa(?string $etapa): self
    {
        $this->etapa = $etapa;

        return $this;
    }

    public function getTramite(): ?string
    {
        return $this->tramite;
    }

    public function setTramite(string $tramite): self
    {
        $this->tramite = $tramite;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): self
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(?\DateTimeInterface $fecha): self
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getFoja(): ?string
    {
        return $this->foja;
    }

    public function setFoja(?string $foja): self
    {
        $this->foja = $foja;

        return $this;
    }

    public function getIndice(): ?int
    {
        return $this->indice;
    }

    public function setIndice(?int $indice): self
    {
        $this->indice = $indice;

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

    public function getCuadernoNombre(): ?string
    {
        return $this->cuadernoNombre;
    }

    public function setCuadernoNombre(?string $cuadernoNombre): self
    {
        $this->cuadernoNombre = $cuadernoNombre;

        return $this;
    }

    /**
     * @return Collection<int, PjudPdf>
     */
    public function getPjudPdfs(): Collection
    {
        return $this->pjudPdfs;
    }

    public function addPjudPdf(PjudPdf $pjudPdf): self
    {
        if (!$this->pjudPdfs->contains($pjudPdf)) {
            $this->pjudPdfs[] = $pjudPdf;
            $pjudPdf->setPjudMovimiento($this);
        }

        return $this;
    }

    public function removePjudPdf(PjudPdf $pjudPdf): self
    {
        if ($this->pjudPdfs->removeElement($pjudPdf)) {
            // set the owning side to null (unless already changed)
            if ($pjudPdf->getPjudMovimiento() === $this) {
                $pjudPdf->setPjudMovimiento(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PjudAnexoMovimiento>
     */
    public function getPjudAnexoMovimientos(): Collection
    {
        return $this->pjudAnexoMovimientos;
    }

    public function addPjudAnexoMovimiento(PjudAnexoMovimiento $pjudAnexoMovimiento): self
    {
        if (!$this->pjudAnexoMovimientos->contains($pjudAnexoMovimiento)) {
            $this->pjudAnexoMovimientos[] = $pjudAnexoMovimiento;
            $pjudAnexoMovimiento->setPjudMovimiento($this);
        }

        return $this;
    }

    public function removePjudAnexoMovimiento(PjudAnexoMovimiento $pjudAnexoMovimiento): self
    {
        if ($this->pjudAnexoMovimientos->removeElement($pjudAnexoMovimiento)) {
            // set the owning side to null (unless already changed)
            if ($pjudAnexoMovimiento->getPjudMovimiento() === $this) {
                $pjudAnexoMovimiento->setPjudMovimiento(null);
            }
        }

        return $this;
    }
}
