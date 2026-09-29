<?php

namespace App\Entity;

use App\Repository\PjudExhortosRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PjudExhortosRepository::class)
 */
class PjudExhortos
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
    private $rolOrigen;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tipoExhorto;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rolDestino;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fechaOrden;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fechaIngreso;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tribunalDestino;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $estadoExhorto;

    /**
     * @ORM\ManyToOne(targetEntity=PjudCausa::class, inversedBy="pjudExhortos")
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

    public function getRolOrigen(): ?string
    {
        return $this->rolOrigen;
    }

    public function setRolOrigen(?string $rolOrigen): self
    {
        $this->rolOrigen = $rolOrigen;

        return $this;
    }

    public function getTipoExhorto(): ?string
    {
        return $this->tipoExhorto;
    }

    public function setTipoExhorto(?string $tipoExhorto): self
    {
        $this->tipoExhorto = $tipoExhorto;

        return $this;
    }

    public function getRolDestino(): ?string
    {
        return $this->rolDestino;
    }

    public function setRolDestino(?string $rolDestino): self
    {
        $this->rolDestino = $rolDestino;

        return $this;
    }

    public function getFechaOrden(): ?string
    {
        return $this->fechaOrden;
    }

    public function setFechaOrden(?string $fechaOrden): self
    {
        $this->fechaOrden = $fechaOrden;

        return $this;
    }

    public function getFechaIngreso(): ?string
    {
        return $this->fechaIngreso;
    }

    public function setFechaIngreso(?string $fechaIngreso): self
    {
        $this->fechaIngreso = $fechaIngreso;

        return $this;
    }

    public function getTribunalDestino(): ?string
    {
        return $this->tribunalDestino;
    }

    public function setTribunalDestino(?string $tribunalDestino): self
    {
        $this->tribunalDestino = $tribunalDestino;

        return $this;
    }

    public function getEstadoExhorto(): ?string
    {
        return $this->estadoExhorto;
    }

    public function setEstadoExhorto(?string $estadoExhorto): self
    {
        $this->estadoExhorto = $estadoExhorto;

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
