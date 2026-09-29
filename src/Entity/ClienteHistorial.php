<?php

namespace App\Entity;

use App\Repository\ClienteHistorialRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClienteHistorialRepository::class)]
#[ORM\Table(name: "cliente_historial")]
class ClienteHistorial
{
    #[ORM\Id()]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Cliente::class, inversedBy: "clienteHistorials")]
    #[ORM\JoinColumn(nullable: false)]
    private $cliente;

    /**
     * Todos los campos de datos son nullable: en cada modificación se guarda solo
     * el dato que cambió (con su valor anterior) y los demás quedan en null.
     */
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $nombre;

    /**
     * Cifrado (AES-256-GCM, ver App\Doctrine\EncryptedStringType). Sin columna hash:
     * esta tabla es solo un historial de auditoría, no se busca por estos campos.
     */
    #[ORM\Column(type: "encrypted_string", length: 255, nullable: true)]
    private $rut;

    #[ORM\Column(type: "encrypted_string", length: 255)]
    private $correo;

    #[ORM\Column(type: "encrypted_string", length: 255)]
    private $telefono;

    #[ORM\Column(type: "string", length: 20)]
    private $sexo;

    #[ORM\Column(type: "encrypted_string", length: 255, nullable: true)]
    private $claveUnica;

    #[ORM\Column(type: "datetime")]
    private $fechaModificacion;

    #[ORM\Column(type: "string", length: 255)]
    private $usuarioModificacion;

    #[ORM\Column(type: "encrypted_string", length: 255, nullable: true)]
    private $direccion;
    #[ORM\Column(type: "encrypted_string", length: 255)]
    private $telefonoRecado;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCliente(): ?Cliente
    {
        return $this->cliente;
    }

    public function setCliente(?Cliente $cliente): self
    {
        $this->cliente = $cliente;

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(?string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getRut(): ?string
    {
        return $this->rut;
    }

    public function setRut(?string $rut): self
    {
        $this->rut = $rut;

        return $this;
    }

    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    public function setCorreo(?string $correo): self
    {
        $this->correo = $correo;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): self
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getSexo(): ?string
    {
        return $this->sexo;
    }

    public function setSexo(?string $sexo): self
    {
        $this->sexo = $sexo;

        return $this;
    }

    public function getClaveUnica(): ?string
    {
        return $this->claveUnica;
    }

    public function setClaveUnica(?string $claveUnica): self
    {
        $this->claveUnica = $claveUnica;

        return $this;
    }

    public function getFechaModificacion(): ?\DateTimeInterface
    {
        return $this->fechaModificacion;
    }

    public function setFechaModificacion(\DateTimeInterface $fechaModificacion): self
    {
        $this->fechaModificacion = $fechaModificacion;

        return $this;
    }

    public function getUsuarioModificacion(): ?string
    {
        return $this->usuarioModificacion;
    }

    public function setUsuarioModificacion(?string $usuarioModificacion): self
    {
        $this->usuarioModificacion = $usuarioModificacion;

        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(?string $direccion): self
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getTelefonoRecado(): ?string
    {
        return $this->telefonoRecado;
    }

    public function setTelefonoRecado(?string $telefonoRecado): self
    {
        $this->telefonoRecado = $telefonoRecado;

        return $this;
    }
}
