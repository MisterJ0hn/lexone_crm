<?php

namespace App\Entity;

use App\Repository\TipoClienteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Catálogo de tipos de cliente de una Agenda: 'Persona' (comportamiento
 * histórico: el Contrato lleva un único Cliente), 'Convenio' o 'Empresa'
 * (el Contrato agrupa varios Clientes propios, repartidos entre sus Causas
 * desde la línea de tiempo del contrato).
 *
 * @ORM\Entity(repositoryClass=TipoClienteRepository::class)
 */
class TipoCliente
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20, unique=true)
     */
    private $nombre;

    /**
     * @ORM\OneToMany(targetEntity=Agenda::class, mappedBy="tipoCliente")
     */
    private $agendas;

    public function __construct()
    {
        $this->agendas = new ArrayCollection();
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
            $agenda->setTipoCliente($this);
        }

        return $this;
    }

    public function removeAgenda(Agenda $agenda): self
    {
        if ($this->agendas->removeElement($agenda)) {
            // set the owning side to null (unless already changed)
            if ($agenda->getTipoCliente() === $this) {
                $agenda->setTipoCliente(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return (string) $this->nombre;
    }
}
