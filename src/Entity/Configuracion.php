<?php

namespace App\Entity;

use App\Repository\ConfiguracionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConfiguracionRepository::class)]
class Configuracion
{
    #[ORM\Id()]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "integer")]
    private $diaFondoFijo;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $host;

    #[ORM\Column(type: "text", nullable: true)]
    private $accessToken;

    #[ORM\Column(type: "text", nullable: true)]
    private $verifyToken;

    #[ORM\Column(type: "integer", nullable: true)]
    private $lotes;

    #[ORM\Column(type: "integer", nullable: true)]
    private $valorMulta;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $tokuId;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $tokuSecret;

    #[ORM\Column(type: "integer")]
    private $maxDiasComision;
    #[ORM\Column(type: "integer", nullable: true)]
    private $passwordHistorial;

    #[ORM\Column(type: "integer", nullable: true)]
    private $passwordDiasExpiracion;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiaFondoFijo(): ?int
    {
        return $this->diaFondoFijo;
    }

    public function setDiaFondoFijo(int $diaFondoFijo): self
    {
        $this->diaFondoFijo = $diaFondoFijo;

        return $this;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(?string $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getVerifyToken(): ?string
    {
        return $this->verifyToken;
    }

    public function setVerifyToken(?string $verifyToken): self
    {
        $this->verifyToken = $verifyToken;

        return $this;
    }

    public function getLotes(): ?int
    {
        return $this->lotes;
    }

    public function setLotes(?int $lotes): self
    {
        $this->lotes = $lotes;

        return $this;
    }

    public function getValorMulta(): ?int
    {
        return $this->valorMulta;
    }

    public function setValorMulta(?int $valorMulta): self
    {
        $this->valorMulta = $valorMulta;

        return $this;
    }

    public function getTokuId(): ?string
    {
        return $this->tokuId;
    }

    public function setTokuId(?string $tokuId): self
    {
        $this->tokuId = $tokuId;

        return $this;
    }

    public function getTokuSecret(): ?string
    {
        return $this->tokuSecret;
    }

    public function setTokuSecret(?string $tokuSecret): self
    {
        $this->tokuSecret = $tokuSecret;

        return $this;
    }

    public function getMaxDiasComision(): ?int
    {
        return $this->maxDiasComision;
    }

    public function setMaxDiasComision(int $maxDiasComision): self
    {
        $this->maxDiasComision = $maxDiasComision;

        return $this;
    }
    
    public function getPaswordHistorial(): ?int
    {
        return $this->passwordHistorial;
    }

    public function setPaswordHistorial(?int $passwordHistorial): self
    {
        $this->passwordHistorial = $passwordHistorial;

        return $this;
    }

    public function getPasswordDiasExpiracion(): ?int
    {
        return $this->passwordDiasExpiracion;
    }

    public function setPasswordDiasExpiracion(?int $passwordDiasExpiracion): self
    {
        $this->passwordDiasExpiracion = $passwordDiasExpiracion;

        return $this;
    }
}
