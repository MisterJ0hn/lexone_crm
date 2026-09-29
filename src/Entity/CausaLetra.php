<?php

namespace App\Entity;

use App\Repository\CausaLetraRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CausaLetraRepository::class)
 */
class CausaLetra
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Materia::class, inversedBy="causaLetras")
     * @ORM\JoinColumn(nullable=false)
     */
    private $materia;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $nombre;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMateria(): ?Materia
    {
        return $this->materia;
    }

    public function setMateria(?Materia $materia): self
    {
        $this->materia = $materia;

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
}
