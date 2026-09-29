<?php

namespace App\Entity;

use App\Repository\PjudCausaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PjudCausaRepository::class)
 */
class PjudCausa
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $rit;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $caratulado;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tribunalNombre;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaIngreso;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $estado;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $etapa;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalMovimientos;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalPdfs;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=PjudEbook::class, mappedBy="pjudCausa")
     */
    private $pjudEbooks;

    /**
     * @ORM\OneToMany(targetEntity=PjudMovimiento::class, mappedBy="pjudCausa")
     */
    private $pjudMovimientos;

    /**
     * @ORM\ManyToOne(targetEntity=Causa::class, inversedBy="pjudCausas")
     */
    private $causa;

    /**
     * @ORM\OneToMany(targetEntity=PjudLitigantes::class, mappedBy="pjudCausa")
     */
    private $pjudLitigantes;

    /**
     * @ORM\OneToMany(targetEntity=PjudNotificaciones::class, mappedBy="pjudCausa")
     */
    private $pjudNotificaciones;

    /**
     * @ORM\OneToMany(targetEntity=PjudEscritos::class, mappedBy="pjudCausa")
     */
    private $pjudEscritos;

    /**
     * @ORM\OneToMany(targetEntity=PjudInformacionReceptor::class, mappedBy="pjudCausa")
     */
    private $pjudInformacionReceptors;

    /**
     * @ORM\OneToMany(targetEntity=PjudAnexoCausa::class, mappedBy="pjudCausa")
     */
    private $pjudAnexoCausas;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $docEbook;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $docEbookBase64;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $docDemanda;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $docDemandaBase64;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $docCertificadoEnvio;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $docCertificadoEnvioBase64;

    /**
     * @ORM\OneToMany(targetEntity=PjudExhortos::class, mappedBy="pjudCausa")
     */
    private $pjudExhortos;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $estadoAdministracion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $proceso;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ubicacion;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $docEbookDescargado;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $docDemandaDescargado;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $docCertificadoEnvioDescargado;

   

    public function __construct()
    {
        $this->pjudEbooks = new ArrayCollection();
        $this->pjudMovimientos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRit(): ?string
    {
        return $this->rit;
    }

    public function setRit(string $rit): self
    {
        $this->rit = $rit;

        return $this;
    }

    public function getCaratulado(): ?string
    {
        return $this->caratulado;
    }

    public function setCaratulado(?string $caratulado): self
    {
        $this->caratulado = $caratulado;

        return $this;
    }

    public function getTribunalNombre(): ?string
    {
        return $this->tribunalNombre;
    }

    public function setTribunalNombre(?string $tribunalNombre): self
    {
        $this->tribunalNombre = $tribunalNombre;

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

    public function getEstado(): ?bool
    {
        return $this->estado;
    }

    public function setEstado(?bool $estado): self
    {
        $this->estado = $estado;

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

    public function getTotalMovimientos(): ?int
    {
        return $this->totalMovimientos;
    }

    public function setTotalMovimientos(int $totalMovimientos): self
    {
        $this->totalMovimientos = $totalMovimientos;

        return $this;
    }

    public function getTotalPdfs(): ?int
    {
        return $this->totalPdfs;
    }

    public function setTotalPdfs(int $totalPdfs): self
    {
        $this->totalPdfs = $totalPdfs;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, PjudEbook>
     */
    public function getPjudEbooks(): Collection
    {
        return $this->pjudEbooks;
    }

    public function addPjudEbook(PjudEbook $pjudEbook): self
    {
        if (!$this->pjudEbooks->contains($pjudEbook)) {
            $this->pjudEbooks[] = $pjudEbook;
            $pjudEbook->setPjudCausa($this);
        }

        return $this;
    }

    public function removePjudEbook(PjudEbook $pjudEbook): self
    {
        if ($this->pjudEbooks->removeElement($pjudEbook)) {
            // set the owning side to null (unless already changed)
            if ($pjudEbook->getPjudCausa() === $this) {
                $pjudEbook->setPjudCausa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PjudMovimiento>
     */
    public function getPjudMovimientos(): Collection
    {
        return $this->pjudMovimientos;
    }

    public function addPjudMovimiento(PjudMovimiento $pjudMovimiento): self
    {
        if (!$this->pjudMovimientos->contains($pjudMovimiento)) {
            $this->pjudMovimientos[] = $pjudMovimiento;
            $pjudMovimiento->setPjudCausa($this);
        }

        return $this;
    }

    public function removePjudMovimiento(PjudMovimiento $pjudMovimiento): self
    {
        if ($this->pjudMovimientos->removeElement($pjudMovimiento)) {
            // set the owning side to null (unless already changed)
            if ($pjudMovimiento->getPjudCausa() === $this) {
                $pjudMovimiento->setPjudCausa(null);
            }
        }

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
     * @return Collection<int, PjudLitigantes>
     */
    public function getPjudLitigantes(): Collection
    {
        return $this->pjudLitigantes;
    }

    public function addPjudLitigante(PjudLitigantes $pjudLitigante): self
    {
        if (!$this->pjudLitigantes->contains($pjudLitigante)) {
            $this->pjudLitigantes[] = $pjudLitigante;
            $pjudLitigante->setPjudCausa($this);
        }

        return $this;
    }

    public function removePjudLitigante(PjudLitigantes $pjudLitigante): self
    {
        if ($this->pjudLitigantes->removeElement($pjudLitigante)) {
            // set the owning side to null (unless already changed)
            if ($pjudLitigante->getPjudCausa() === $this) {
                $pjudLitigante->setPjudCausa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PjudNotificaciones>
     */
    public function getPjudNotificaciones(): Collection
    {
        return $this->pjudNotificaciones;
    }

    public function addPjudNotificacione(PjudNotificaciones $pjudNotificacione): self
    {
        if (!$this->pjudNotificaciones->contains($pjudNotificacione)) {
            $this->pjudNotificaciones[] = $pjudNotificacione;
            $pjudNotificacione->setPjudCausa($this);
        }

        return $this;
    }

    public function removePjudNotificacione(PjudNotificaciones $pjudNotificacione): self
    {
        if ($this->pjudNotificaciones->removeElement($pjudNotificacione)) {
            // set the owning side to null (unless already changed)
            if ($pjudNotificacione->getPjudCausa() === $this) {
                $pjudNotificacione->setPjudCausa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PjudEscritos>
     */
    public function getPjudEscritos(): Collection
    {
        return $this->pjudEscritos;
    }

    public function addPjudEscrito(PjudEscritos $pjudEscrito): self
    {
        if (!$this->pjudEscritos->contains($pjudEscrito)) {
            $this->pjudEscritos[] = $pjudEscrito;
            $pjudEscrito->setPjudCausa($this);
        }

        return $this;
    }

    public function removePjudEscrito(PjudEscritos $pjudEscrito): self
    {
        if ($this->pjudEscritos->removeElement($pjudEscrito)) {
            // set the owning side to null (unless already changed)
            if ($pjudEscrito->getPjudCausa() === $this) {
                $pjudEscrito->setPjudCausa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PjudInformacionReceptor>
     */
    public function getPjudInformacionReceptors(): Collection
    {
        return $this->pjudInformacionReceptors;
    }

    public function addPjudInformacionReceptor(PjudInformacionReceptor $pjudInformacionReceptor): self
    {
        if (!$this->pjudInformacionReceptors->contains($pjudInformacionReceptor)) {
            $this->pjudInformacionReceptors[] = $pjudInformacionReceptor;
            $pjudInformacionReceptor->setPjudCausa($this);
        }

        return $this;
    }

    public function removePjudInformacionReceptor(PjudInformacionReceptor $pjudInformacionReceptor): self
    {
        if ($this->pjudInformacionReceptors->removeElement($pjudInformacionReceptor)) {
            // set the owning side to null (unless already changed)
            if ($pjudInformacionReceptor->getPjudCausa() === $this) {
                $pjudInformacionReceptor->setPjudCausa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PjudAnexoCausa>
     */
    public function getPjudAnexoCausas(): Collection
    {
        return $this->pjudAnexoCausas;
    }

    public function addPjudAnexoCausa(PjudAnexoCausa $pjudAnexoCausa): self
    {
        if (!$this->pjudAnexoCausas->contains($pjudAnexoCausa)) {
            $this->pjudAnexoCausas[] = $pjudAnexoCausa;
            $pjudAnexoCausa->setPjudCausa($this);
        }

        return $this;
    }

    public function removePjudAnexoCausa(PjudAnexoCausa $pjudAnexoCausa): self
    {
        if ($this->pjudAnexoCausas->removeElement($pjudAnexoCausa)) {
            // set the owning side to null (unless already changed)
            if ($pjudAnexoCausa->getPjudCausa() === $this) {
                $pjudAnexoCausa->setPjudCausa(null);
            }
        }

        return $this;
    }

    public function getDocEbook(): ?string
    {
        return $this->docEbook;
    }

    public function setDocEbook(?string $docEbook): self
    {
        $this->docEbook = $docEbook;

        return $this;
    }

    public function getDocEbookBase64(): ?string
    {
        return $this->docEbookBase64;
    }

    public function setDocEbookBase64(?string $docEbookBase64): self
    {
        $this->docEbookBase64 = $docEbookBase64;

        return $this;
    }

    public function getDocDemanda(): ?string
    {
        return $this->docDemanda;
    }

    public function setDocDemanda(?string $docDemanda): self
    {
        $this->docDemanda = $docDemanda;

        return $this;
    }

    public function getDocDemandaBase64(): ?string
    {
        return $this->docDemandaBase64;
    }

    public function setDocDemandaBase64(?string $docDemandaBase64): self
    {
        $this->docDemandaBase64 = $docDemandaBase64;

        return $this;
    }

    public function getDocCertificadoEnvio(): ?string
    {
        return $this->docCertificadoEnvio;
    }

    public function setDocCertificadoEnvio(?string $docCertificadoEnvio): self
    {
        $this->docCertificadoEnvio = $docCertificadoEnvio;

        return $this;
    }

    public function getDocCertificadoEnvioBase64(): ?string
    {
        return $this->docCertificadoEnvioBase64;
    }

    public function setDocCertificadoEnvioBase64(?string $docCertificadoEnvioBase64): self
    {
        $this->docCertificadoEnvioBase64 = $docCertificadoEnvioBase64;

        return $this;
    }

    /**
     * @return Collection<int, PjudExhortos>
     */
    public function getPjudExhortos(): Collection
    {
        return $this->pjudExhortos;
    }

    public function addPjudExhorto(PjudExhortos $pjudExhorto): self
    {
        if (!$this->pjudExhortos->contains($pjudExhorto)) {
            $this->pjudExhortos[] = $pjudExhorto;
            $pjudExhorto->setPjudCausa($this);
        }

        return $this;
    }

    public function removePjudExhorto(PjudExhortos $pjudExhorto): self
    {
        if ($this->pjudExhortos->removeElement($pjudExhorto)) {
            // set the owning side to null (unless already changed)
            if ($pjudExhorto->getPjudCausa() === $this) {
                $pjudExhorto->setPjudCausa(null);
            }
        }

        return $this;
    }

    public function getEstadoAdministracion(): ?string
    {
        return $this->estadoAdministracion;
    }

    public function setEstadoAdministracion(?string $estadoAdministracion): self
    {
        $this->estadoAdministracion = $estadoAdministracion;

        return $this;
    }

    public function getProceso(): ?string
    {
        return $this->proceso;
    }

    public function setProceso(?string $proceso): self
    {
        $this->proceso = $proceso;

        return $this;
    }

    public function getUbicacion(): ?string
    {
        return $this->ubicacion;
    }

    public function setUbicacion(?string $ubicacion): self
    {
        $this->ubicacion = $ubicacion;

        return $this;
    }

    public function getDocEbookDescargado(): ?bool
    {
        return $this->docEbookDescargado;
    }

    public function setDocEbookDescargado(?bool $docEbookDescargado): self
    {
        $this->docEbookDescargado = $docEbookDescargado;

        return $this;
    }

    public function getDocDemandaDescargado(): ?bool
    {
        return $this->docDemandaDescargado;
    }

    public function setDocDemandaDescargado(?bool $docDemandaDescargado): self
    {
        $this->docDemandaDescargado = $docDemandaDescargado;

        return $this;
    }

    public function getDocCertificadoEnvioDescargado(): ?bool
    {
        return $this->docCertificadoEnvioDescargado;
    }

    public function setDocCertificadoEnvioDescargado(?bool $docCertificadoEnvioDescargado): self
    {
        $this->docCertificadoEnvioDescargado = $docCertificadoEnvioDescargado;

        return $this;
    }


}
