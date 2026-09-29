<?php

namespace App\Entity;

use App\Repository\TicketHistorialRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketHistorialRepository::class)]
class TicketHistorial
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Ticket::class, inversedBy: "ticketHistorials")]
    #[ORM\JoinColumn(nullable: false)]
    private $ticket;

    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: "ticketHistorials")]
    #[ORM\JoinColumn(nullable: false)]
    private $usuarioRegistro;

    #[ORM\Column(type: "string", length: 200)]
    private $observacion;

    #[ORM\ManyToOne(targetEntity: TicketEstado::class)]
    private $estado;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $fecha;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): self
    {
        $this->ticket = $ticket;

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

    public function getObservacion(): ?string
    {
        return $this->observacion;
    }

    public function setObservacion(string $observacion): self
    {
        $this->observacion = $observacion;

        return $this;
    }

    public function getEstado(): ?TicketEstado
    {
        return $this->estado;
    }

    public function setEstado(?TicketEstado $estado): self
    {
        $this->estado = $estado;

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
}
