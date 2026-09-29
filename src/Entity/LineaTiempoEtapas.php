<?php

namespace App\Entity;

use App\Repository\LineaTiempoEtapasRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LineaTiempoEtapasRepository::class)]
class LineaTiempoEtapas
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255)]
    private $nombre;

    #[ORM\ManyToOne(targetEntity: LineaTiempo::class, inversedBy: "lineaTiempoEtapas")]
    #[ORM\JoinColumn(nullable: false)]
    private $lineaTiempo;

    #[ORM\OneToMany(targetEntity: LineaTiempoTerminada::class, mappedBy: "lineaTiempoEtapas")]
    private $lineaTiempoTerminadas;

    

    public function __construct()
    {
        $this->lineaTiempoTerminadas = new ArrayCollection();
        
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

    public function getLineaTiempo(): ?LineaTiempo
    {
        return $this->lineaTiempo;
    }

    public function setLineaTiempo(?LineaTiempo $lineaTiempo): self
    {
        $this->lineaTiempo = $lineaTiempo;

        return $this;
    }

    /**
     * @return Collection<int, LineaTiempoTerminada>
     */
    public function getLineaTiempoTerminadas(): Collection
    {
        return $this->lineaTiempoTerminadas;
    }

    public function addLineaTiempoTerminada(LineaTiempoTerminada $lineaTiempoTerminada): self
    {
        if (!$this->lineaTiempoTerminadas->contains($lineaTiempoTerminada)) {
            $this->lineaTiempoTerminadas[] = $lineaTiempoTerminada;
            $lineaTiempoTerminada->setLineaTiempoEtapas($this);
        }

        return $this;
    }

    public function removeLineaTiempoTerminada(LineaTiempoTerminada $lineaTiempoTerminada): self
    {
        if ($this->lineaTiempoTerminadas->removeElement($lineaTiempoTerminada)) {
            // set the owning side to null (unless already changed)
            if ($lineaTiempoTerminada->getLineaTiempoEtapas() === $this) {
                $lineaTiempoTerminada->setLineaTiempoEtapas(null);
            }
        }

        return $this;
    }

   
}
