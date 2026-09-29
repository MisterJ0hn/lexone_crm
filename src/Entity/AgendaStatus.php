<?php

namespace App\Entity;

use App\Repository\AgendaStatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AgendaStatusRepository::class)
 */
class AgendaStatus
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
    private $nombre;

    /**
     * @ORM\OneToMany(targetEntity=Agenda::class, mappedBy="status")
     */
    private $agendas;

    /**
     * @ORM\Column(type="integer")
     */
    private $perfil;

    /**
     * @ORM\OneToMany(targetEntity=AgendaObservacion::class, mappedBy="status")
     */
    private $agendaObservacions;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $orden;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $icon;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $color;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $vermas;

    /**
     * @ORM\OneToMany(targetEntity=AgendaSubStatus::class, mappedBy="agendaStatus")
     */
    private $agendaSubStatuses;

    public function __construct()
    {
        $this->agendas = new ArrayCollection();
        $this->agendaObservacions = new ArrayCollection();
        $this->agendaSubStatuses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * @return Collection|Agenda[]
     */
    public function getAgendas(): Collection
    {
        return $this->agendas;
    }

    public function addAgenda(Agenda $agenda): self
    {
        if (!$this->agendas->contains($agenda)) {
            $this->agendas[] = $agenda;
            $agenda->setStatus($this);
        }

        return $this;
    }

    public function removeAgenda(Agenda $agenda): self
    {
        if ($this->agendas->removeElement($agenda)) {
            // set the owning side to null (unless already changed)
            if ($agenda->getStatus() === $this) {
                $agenda->setStatus(null);
            }
        }

        return $this;
    }
    public function __toString()
    {
        return $this->getNombre();
    }

    public function getPerfil(): ?int
    {
        return $this->perfil;
    }

    public function setPerfil(int $perfil): self
    {
        $this->perfil = $perfil;

        return $this;
    }

    /**
     * @return Collection|AgendaObservacion[]
     */
    public function getAgendaObservacions(): Collection
    {
        return $this->agendaObservacions;
    }

    public function addAgendaObservacion(AgendaObservacion $agendaObservacion): self
    {
        if (!$this->agendaObservacions->contains($agendaObservacion)) {
            $this->agendaObservacions[] = $agendaObservacion;
            $agendaObservacion->setStatus($this);
        }

        return $this;
    }

    public function removeAgendaObservacion(AgendaObservacion $agendaObservacion): self
    {
        if ($this->agendaObservacions->removeElement($agendaObservacion)) {
            // set the owning side to null (unless already changed)
            if ($agendaObservacion->getStatus() === $this) {
                $agendaObservacion->setStatus(null);
            }
        }

        return $this;
    }

    public function getOrden(): ?int
    {
        return $this->orden;
    }

    public function setOrden(?int $orden): self
    {
        $this->orden = $orden;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getVermas(): ?bool
    {
        return $this->vermas;
    }

    public function setVermas(?bool $vemas): self
    {
        $this->vermas = $vemas;

        return $this;
    }

    /**
     * @return Collection|AgendaSubStatus[]
     */
    public function getAgendaSubStatuses(): Collection
    {
        return $this->agendaSubStatuses;
    }

    public function addAgendaSubStatus(AgendaSubStatus $agendaSubStatus): self
    {
        if (!$this->agendaSubStatuses->contains($agendaSubStatus)) {
            $this->agendaSubStatuses[] = $agendaSubStatus;
            $agendaSubStatus->setAgendaStatus($this);
        }

        return $this;
    }

    public function removeAgendaSubStatus(AgendaSubStatus $agendaSubStatus): self
    {
        if ($this->agendaSubStatuses->removeElement($agendaSubStatus)) {
            // set the owning side to null (unless already changed)
            if ($agendaSubStatus->getAgendaStatus() === $this) {
                $agendaSubStatus->setAgendaStatus(null);
            }
        }

        return $this;
    }
}
