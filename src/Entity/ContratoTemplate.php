<?php

namespace App\Entity;

use App\Repository\ContratoTemplateRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Plantilla de contrato editable por tenant (Empresa) y por TipoCliente
 * (Persona/Empresa/Convenio). Su contenido admite variables (ver
 * ContratoTemplateRenderer) que se reemplazan al generar el PDF del contrato.
 *
 * @ORM\Entity(repositoryClass=ContratoTemplateRepository::class)
 */
class ContratoTemplate
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Empresa::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $empresa;

    /**
     * @ORM\ManyToOne(targetEntity=TipoCliente::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $tipoCliente;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $nombre;

    /**
     * Cuerpo HTML de la plantilla, con variables tipo {{cliente_nombre}}.
     *
     * @ORM\Column(type="text")
     */
    private $contenido;

    /**
     * Solo una plantilla puede estar activa por (empresa, tipoCliente): es la
     * que usa ContratoController::pdf() para generar el contrato.
     *
     * @ORM\Column(type="boolean")
     */
    private $activo = true;

    /**
     * @ORM\ManyToOne(targetEntity=Usuario::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $usuarioRegistro;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaCreacion;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaModificacion;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getTipoCliente(): ?TipoCliente
    {
        return $this->tipoCliente;
    }

    public function setTipoCliente(?TipoCliente $tipoCliente): self
    {
        $this->tipoCliente = $tipoCliente;

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

    public function getContenido(): ?string
    {
        return $this->contenido;
    }

    public function setContenido(string $contenido): self
    {
        $this->contenido = $contenido;

        return $this;
    }

    public function getActivo(): ?bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): self
    {
        $this->activo = $activo;

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

    public function getFechaCreacion(): ?\DateTimeInterface
    {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(\DateTimeInterface $fechaCreacion): self
    {
        $this->fechaCreacion = $fechaCreacion;

        return $this;
    }

    public function getFechaModificacion(): ?\DateTimeInterface
    {
        return $this->fechaModificacion;
    }

    public function setFechaModificacion(?\DateTimeInterface $fechaModificacion): self
    {
        $this->fechaModificacion = $fechaModificacion;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->nombre;
    }
}
