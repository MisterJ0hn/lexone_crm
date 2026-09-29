<?php

namespace App\Entity;

use App\Repository\PjudNotificacionesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PjudNotificacionesRepository::class)
 */
class PjudNotificaciones
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rol;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $estadoNotificacion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tipoNotificacion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fechaTramite;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tipoPart;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tramite;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $observacion;

    /**
     * @ORM\ManyToOne(targetEntity=PjudCausa::class, inversedBy="pjudNotificaciones")
     */
    private $pjudCausa;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cuadernoId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRol(): ?string
    {
        return $this->rol;
    }

    public function setRol(?string $rol): self
    {
        $this->rol = $rol;

        return $this;
    }

    public function getEstadoNotificacion(): ?string
    {
        return $this->estadoNotificacion;
    }

    public function setEstadoNotificacion(?string $estadoNotificacion): self
    {
        $this->estadoNotificacion = $estadoNotificacion;

        return $this;
    }

    public function getTipoNotificacion(): ?string
    {
        return $this->tipoNotificacion;
    }

    public function setTipoNotificacion(?string $tipoNotificacion): self
    {
        $this->tipoNotificacion = $tipoNotificacion;

        return $this;
    }

    public function getFechaTramite(): ?string
    {
        return $this->fechaTramite;
    }

    public function setFechaTramite(?string $fechaTramite): self
    {
        $this->fechaTramite = $fechaTramite;

        return $this;
    }

    public function getTipoPart(): ?string
    {
        return $this->tipoPart;
    }

    public function setTipoPart(?string $tipoPart): self
    {
        $this->tipoPart = $tipoPart;

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

    public function getTramite(): ?string
    {
        return $this->tramite;
    }

    public function setTramite(?string $tramite): self
    {
        $this->tramite = $tramite;

        return $this;
    }

    public function getObservacion(): ?string
    {
        return $this->observacion;
    }

    public function setObservacion(?string $observacion): self
    {
        $this->observacion = $observacion;

        return $this;
    }

    public function getPjudCausa(): ?PjudCausa
    {
        return $this->pjudCausa;
    }

    public function setPjudCausa(?PjudCausa $pjudCausa): self
    {
        $this->pjudCausa = $pjudCausa;

        return $this;
    }

    public function getCuadernoId(): ?int
    {
        return $this->cuadernoId;
    }

    public function setCuadernoId(?int $cuadernoId): self
    {
        $this->cuadernoId = $cuadernoId;

        return $this;
    }
}
