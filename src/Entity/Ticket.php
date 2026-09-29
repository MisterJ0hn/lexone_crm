<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TicketRepository::class)
 */
class Ticket
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Contrato::class, inversedBy="tickets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $contrato;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class, inversedBy="tickets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $origen;

    /**
     * @ORM\ManyToOne(targetEntity=UsuarioTipo::class, inversedBy="tickets")
     * @ORM\JoinColumn(nullable=true)
     */
    private $destino;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class, inversedBy="tickets")
     * @ORM\JoinColumn(nullable=true)
     */
    private $encargado;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $motivo;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $respuesta;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $detalleCierre;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaNuevo;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaAsignado;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaRespuesta;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaCierre;

    /**
     * @ORM\Column(type="integer")
     */
    private $folio;

    /**
     * @ORM\OneToMany(targetEntity=TicketHistorial::class, mappedBy="ticket")
     */
    private $ticketHistorials;

    /**
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="tickets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $empresa;

    /**
     * @ORM\ManyToOne(targetEntity=TicketEstado::class)
     */
    private $estado;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $folioSac;

    public function __construct()
    {
        $this->ticketHistorials = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOrigen(): ?Usuario
    {
        return $this->origen;
    }

    public function setOrigen(?Usuario $origen): self
    {
        $this->origen = $origen;

        return $this;
    }

    public function getDestino(): ?UsuarioTipo
    {
        return $this->destino;
    }

    public function setDestino(?UsuarioTipo $destino): self
    {
        $this->destino = $destino;

        return $this;
    }

    public function getEncargado(): ?Usuario
    {
        return $this->encargado;
    }

    public function setEncargado(?Usuario $encargado): self
    {
        $this->encargado = $encargado;

        return $this;
    }

    public function getMotivo(): ?string
    {
        return $this->motivo;
    }

    public function setMotivo(?string $motivo): self
    {
        $this->motivo = $motivo;

        return $this;
    }

    public function getRespuesta(): ?string
    {
        return $this->respuesta;
    }

    public function setRespuesta(?string $respuesta): self
    {
        $this->respuesta = $respuesta;

        return $this;
    }

    public function getDetalleCierre(): ?string
    {
        return $this->detalleCierre;
    }

    public function setDetalleCierre(?string $detalleCierre): self
    {
        $this->detalleCierre = $detalleCierre;

        return $this;
    }

    public function getFechaNuevo(): ?\DateTimeInterface
    {
        return $this->fechaNuevo;
    }

    public function setFechaNuevo(\DateTimeInterface $fechaNuevo): self
    {
        $this->fechaNuevo = $fechaNuevo;

        return $this;
    }

    public function getFechaAsignado(): ?\DateTimeInterface
    {
        return $this->fechaAsignado;
    }

    public function setFechaAsignado(?\DateTimeInterface $fechaAsignado): self
    {
        $this->fechaAsignado = $fechaAsignado;

        return $this;
    }

    public function getFechaRespuesta(): ?\DateTimeInterface
    {
        return $this->fechaRespuesta;
    }

    public function setFechaRespuesta(?\DateTimeInterface $fechaRespuesta): self
    {
        $this->fechaRespuesta = $fechaRespuesta;

        return $this;
    }

    public function getFechaCierre(): ?\DateTimeInterface
    {
        return $this->fechaCierre;
    }

    public function setFechaCierre(?\DateTimeInterface $fechaCierre): self
    {
        $this->fechaCierre = $fechaCierre;

        return $this;
    }

    public function getFolio(): ?int
    {
        return $this->folio;
    }

    public function setFolio(int $folio): self
    {
        $this->folio = $folio;

        return $this;
    }

    /**
     * @return Collection<int, TicketHistorial>
     */
    public function getTicketHistorials(): Collection
    {
        return $this->ticketHistorials;
    }

    public function addTicketHistorial(TicketHistorial $ticketHistorial): self
    {
        if (!$this->ticketHistorials->contains($ticketHistorial)) {
            $this->ticketHistorials[] = $ticketHistorial;
            $ticketHistorial->setTicket($this);
        }

        return $this;
    }

    public function removeTicketHistorial(TicketHistorial $ticketHistorial): self
    {
        if ($this->ticketHistorials->removeElement($ticketHistorial)) {
            // set the owning side to null (unless already changed)
            if ($ticketHistorial->getTicket() === $this) {
                $ticketHistorial->setTicket(null);
            }
        }

        return $this;
    }

    public function getEmpresa(): ?Empresa
    {
        return $this->empresa;
    }

    public function setEmpresa(?Empresa $empresa): self
    {
        $this->empresa = $empresa;

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

    public function getFolioSac(): ?string
    {
        return $this->folioSac;
    }

    public function setFolioSac(?string $folioSac): self
    {
        $this->folioSac = $folioSac;

        return $this;
    }
}
