<?php

namespace App\Entity;

use App\Repository\UsuarioTipoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UsuarioTipoRepository::class)
 */
class UsuarioTipo
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $nombre;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $orden;

    /**
     * @ORM\OneToMany(targetEntity=Usuario::class, mappedBy="usuarioTipo")
     */
    private $usuarios;

    /**
     * @ORM\OneToMany(targetEntity=PrivilegioTipousuario::class, mappedBy="tipousuario", orphanRemoval=true)
     */
    private $privilegioTipousuarios;

    /**
     * @ORM\Column(type="boolean")
     */
    private $fijar;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombreInterno;

    /**
     * @ORM\ManyToOne(targetEntity=MenuCabezera::class, inversedBy="usuarioTipos")
     */
    private $menuCabezera;

    /**
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="usuarioTipos")
     */
    private $empresa;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $statues = [];

    /**
     * @ORM\OneToMany(targetEntity=Ticket::class, mappedBy="destino")
     */
    private $tickets;


    public function __construct()
    {
        $this->usuarios = new ArrayCollection();
        $this->privilegioTipousuarios = new ArrayCollection();
        $this->tickets = new ArrayCollection();
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

    public function getOrden(): ?int
    {
        return $this->orden;
    }

    public function setOrden(?int $orden): self
    {
        $this->orden = $orden;

        return $this;
    }

    /**
     * @return Collection|Usuario[]
     */
    public function getUsuarios(): Collection
    {
        return $this->usuarios;
    }

    public function addUsuario(Usuario $usuario): self
    {
        if (!$this->usuarios->contains($usuario)) {
            $this->usuarios[] = $usuario;
            $usuario->setUsuarioTipo($this);
        }

        return $this;
    }

    public function removeUsuario(Usuario $usuario): self
    {
        if ($this->usuarios->contains($usuario)) {
            $this->usuarios->removeElement($usuario);
            // set the owning side to null (unless already changed)
            if ($usuario->getUsuarioTipo() === $this) {
                $usuario->setUsuarioTipo(null);
            }
        }

        return $this;
    }


    public function __toString(){
        return $this->nombre;
    }

    /**
     * @return Collection|PrivilegioTipousuario[]
     */
    public function getPrivilegioTipousuarios(): Collection
    {
        return $this->privilegioTipousuarios;
    }

    public function addPrivilegioTipousuario(PrivilegioTipousuario $privilegioTipousuario): self
    {
        if (!$this->privilegioTipousuarios->contains($privilegioTipousuario)) {
            $this->privilegioTipousuarios[] = $privilegioTipousuario;
            $privilegioTipousuario->setTipousuario($this);
        }

        return $this;
    }

    public function removePrivilegioTipousuario(PrivilegioTipousuario $privilegioTipousuario): self
    {
        if ($this->privilegioTipousuarios->removeElement($privilegioTipousuario)) {
            // set the owning side to null (unless already changed)
            if ($privilegioTipousuario->getTipousuario() === $this) {
                $privilegioTipousuario->setTipousuario(null);
            }
        }

        return $this;
    }

    public function getFijar(): ?bool
    {
        return $this->fijar;
    }

    public function setFijar(bool $fijar): self
    {
        $this->fijar = $fijar;

        return $this;
    }

    public function getNombreInterno(): ?string
    {
        return $this->nombreInterno;
    }

    public function setNombreInterno(string $nombreInterno): self
    {
        $this->nombreInterno = $nombreInterno;

        return $this;
    }

    public function getMenuCabezera(): ?MenuCabezera
    {
        return $this->menuCabezera;
    }

    public function setMenuCabezera(?MenuCabezera $menuCabezera): self
    {
        $this->menuCabezera = $menuCabezera;

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

    public function getStatues(): ?array
    {
        return $this->statues;
    }

    public function setStatues(?array $statues): self
    {
        $this->statues = $statues;

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): self
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets[] = $ticket;
            $ticket->setDestino($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getDestino() === $this) {
                $ticket->setDestino(null);
            }
        }

        return $this;
    }

}
