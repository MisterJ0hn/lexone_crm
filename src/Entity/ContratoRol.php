<?php

namespace App\Entity;

use App\Repository\ContratoRolRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ContratoRolRepository::class)
 */
class ContratoRol
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
    private $nombreRol;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $institucionAcreedora;

    /**
     * @ORM\ManyToOne(targetEntity=Juzgado::class, inversedBy="contratoRols")
     */
    private $juzgado;

    /**
     * @ORM\ManyToOne(targetEntity=Contrato::class, inversedBy="contratoRols")
     * @ORM\JoinColumn(nullable=true)
     */
    private $contrato;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class, inversedBy="contratoRols")
     * @ORM\JoinColumn(nullable=false)
     */
    private $abogado;

    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreRol(): ?string
    {
        return $this->nombreRol;
    }

    public function setNombreRol(string $nombreRol): self
    {
        $this->nombreRol = $nombreRol;

        return $this;
    }

    public function getInstitucionAcreedora(): ?string
    {
        return $this->institucionAcreedora;
    }

    public function setInstitucionAcreedora(string $institucionAcreedora): self
    {
        $this->institucionAcreedora = $institucionAcreedora;

        return $this;
    }

    public function getJuzgado(): ?Juzgado
    {
        return $this->juzgado;
    }

    public function setJuzgado(?Juzgado $juzgado): self
    {
        $this->juzgado = $juzgado;

        return $this;
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

    public function getAbogado(): ?Usuario
    {
        return $this->abogado;
    }

    public function setAbogado(?Usuario $abogado): self
    {
        $this->abogado = $abogado;

        return $this;
    }

}
