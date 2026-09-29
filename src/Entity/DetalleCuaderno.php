<?php

namespace App\Entity;

use App\Repository\DetalleCuadernoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DetalleCuadernoRepository::class)
 */
class DetalleCuaderno
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Cuaderno::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $cuaderno;

    /**
     * @ORM\ManyToOne(targetEntity=Actuacion::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $actuacion;

    /**
     * @ORM\ManyToOne(targetEntity=AnexoProcesal::class)
     */
    private $anexoProcesal;

    /**
     * @ORM\ManyToOne(targetEntity=Causa::class, inversedBy="detalleCuadernos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $causa;

    /**
     * @ORM\OneToMany(targetEntity=CausaObservacion::class, mappedBy="detalleCuaderno")
     */
    private $causaObservaciones;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuarioCreacion;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaCreacion;

    public function __construct()
    {
        $this->causaObservaciones = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCuaderno(): ?Cuaderno
    {
        return $this->cuaderno;
    }

    public function setCuaderno(?Cuaderno $cuaderno): self
    {
        $this->cuaderno = $cuaderno;

        return $this;
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

    public function getCausa(): ?Causa
    {
        return $this->causa;
    }

    public function setCausa(?Causa $causa): self
    {
        $this->causa = $causa;

        return $this;
    }

    /**
     * @return Collection<int, CausaObservacion>
     */
    public function getCausaObservaciones(): Collection
    {
        return $this->causaObservaciones;
    }

    public function addCausaObservacione(CausaObservacion $causaObservacione): self
    {
        if (!$this->causaObservaciones->contains($causaObservacione)) {
            $this->causaObservaciones[] = $causaObservacione;
            $causaObservacione->setDetalleCuaderno($this);
        }

        return $this;
    }

    public function removeCausaObservacione(CausaObservacion $causaObservacione): self
    {
        if ($this->causaObservaciones->removeElement($causaObservacione)) {
            // set the owning side to null (unless already changed)
            if ($causaObservacione->getDetalleCuaderno() === $this) {
                $causaObservacione->setDetalleCuaderno(null);
            }
        }

        return $this;
    }

    public function getUsuarioCreacion(): ?Usuario
    {
        return $this->usuarioCreacion;
    }

    public function setUsuarioCreacion(?Usuario $usuarioCreacion): self
    {
        $this->usuarioCreacion = $usuarioCreacion;

        return $this;
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
}
