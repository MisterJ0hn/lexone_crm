<?php

namespace App\Entity;

use App\Repository\AgendaObservacionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgendaObservacionRepository::class)]
class AgendaObservacion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Agenda::class, inversedBy: "agendaObservacions")]
    #[ORM\JoinColumn(nullable: false)]
    private $agenda;

    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: "agendaObservacions")]
    #[ORM\JoinColumn(nullable: false)]
    private $usuarioRegistro;

    #[ORM\ManyToOne(targetEntity: AgendaStatus::class, inversedBy: "agendaObservacions")]
    private $status;

    #[ORM\Column(type: "datetime")]
    private $fechaRegistro;

    #[ORM\Column(type: "text")]
    private $observacion;

    #[ORM\ManyToOne(targetEntity: AgendaSubStatus::class, inversedBy: "agendaObservacions")]
    private $subStatus;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAgenda(): ?Agenda
    {
        return $this->agenda;
    }

    public function setAgenda(?Agenda $agenda): self
    {
        $this->agenda = $agenda;

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

    public function getStatus(): ?AgendaStatus
    {
        return $this->status;
    }

    public function setStatus(?AgendaStatus $status): self
    {
        $this->status = $status;

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

    public function getObservacion(): ?string
    {
        return $this->observacion;
    }

    public function setObservacion(string $observacion): self
    {
        $this->observacion = $observacion;

        return $this;
    }

    public function getSubStatus(): ?AgendaSubStatus
    {
        return $this->subStatus;
    }

    public function setSubStatus(?AgendaSubStatus $subStatus): self
    {
        $this->subStatus = $subStatus;

        return $this;
    }
}
