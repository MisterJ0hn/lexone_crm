<?php

namespace App\Entity;

use App\Repository\JuzgadoCuentaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=JuzgadoCuentaRepository::class)
 */
class JuzgadoCuenta
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Cuenta::class, inversedBy="juzgadoCuentas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cuenta;

    /**
     * @ORM\ManyToOne(targetEntity=Juzgado::class, inversedBy="juzgadoCuentas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $juzgado;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCuenta(): ?Cuenta
    {
        return $this->cuenta;
    }

    public function setCuenta(?Cuenta $cuenta): self
    {
        $this->cuenta = $cuenta;

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
}
