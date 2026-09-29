<?php

namespace App\Entity;

use App\Repository\AgendaSubStatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AgendaSubStatusRepository::class)
 */
class AgendaSubStatus
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=AgendaStatus::class, inversedBy="agendaSubStatuses")
     */
    private $agendaStatus;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $nombre;

    /**
     * @ORM\OneToMany(targetEntity=AgendaObservacion::class, mappedBy="subStatus")
     */
    private $agendaObservacions;

    public function __construct()
    {
        $this->agendaObservacions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAgendaStatus(): ?AgendaStatus
    {
        return $this->agendaStatus;
    }

    public function setAgendaStatus(?AgendaStatus $agendaStatus): self
    {
        $this->agendaStatus = $agendaStatus;

        return $this;
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
            $agendaObservacion->setSubStatus($this);
        }

        return $this;
    }

    public function removeAgendaObservacion(AgendaObservacion $agendaObservacion): self
    {
        if ($this->agendaObservacions->removeElement($agendaObservacion)) {
            // set the owning side to null (unless already changed)
            if ($agendaObservacion->getSubStatus() === $this) {
                $agendaObservacion->setSubStatus(null);
            }
        }

        return $this;
    }
    public function __toString()
    {
        return $this->nombre;
    }
}
