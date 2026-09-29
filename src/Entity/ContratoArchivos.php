<?php

namespace App\Entity;

use App\Repository\ContratoArchivosRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContratoArchivosRepository::class)]
class ContratoArchivos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: "contratoArchivos")]
    #[ORM\JoinColumn(nullable: false)]
    private $usuarioRegistro;

    #[ORM\ManyToOne(targetEntity: Contrato::class, inversedBy: "contratoArchivos")]
    #[ORM\JoinColumn(nullable: false)]
    private $contrato;

    #[ORM\Column(type: "string", length: 255)]
    private $url;

    #[ORM\Column(type: "datetime")]
    private $fechaSubida;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getContrato(): ?Contrato
    {
        return $this->contrato;
    }

    public function setContrato(?Contrato $contrato): self
    {
        $this->contrato = $contrato;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getFechaSubida(): ?\DateTimeInterface
    {
        return $this->fechaSubida;
    }

    public function setFechaSubida(\DateTimeInterface $fechaSubida): self
    {
        $this->fechaSubida = $fechaSubida;

        return $this;
    }
}
