<?php

namespace App\Entity;

use App\Repository\JuzgadoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=JuzgadoRepository::class)
 */
class Juzgado
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="juzgados")
     */
    private $empresa;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\OneToMany(targetEntity=ContratoRol::class, mappedBy="juzgado")
     */
    private $contratoRols;

    /**
     * @ORM\OneToMany(targetEntity=JuzgadoCuenta::class, mappedBy="juzgado")
     */
    private $juzgadoCuentas;

    /**
     * @ORM\ManyToOne(targetEntity=Corte::class, inversedBy="juzgados")
     */
    private $corte;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $pjudTribunalId;



    public function __construct()
    {
        $this->contratoRols = new ArrayCollection();
        $this->juzgadoCuentas = new ArrayCollection();
    }

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

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * @return Collection|ContratoRol[]
     */
    public function getContratoRols(): Collection
    {
        return $this->contratoRols;
    }

    public function addContratoRol(ContratoRol $contratoRol): self
    {
        if (!$this->contratoRols->contains($contratoRol)) {
            $this->contratoRols[] = $contratoRol;
            $contratoRol->setJuzgado($this);
        }

        return $this;
    }

    public function removeContratoRol(ContratoRol $contratoRol): self
    {
        if ($this->contratoRols->removeElement($contratoRol)) {
            // set the owning side to null (unless already changed)
            if ($contratoRol->getJuzgado() === $this) {
                $contratoRol->setJuzgado(null);
            }
        }

        return $this;
    }
    
    public function __toString(){
        return $this->getNombre();
    }

    /**
     * @return Collection|JuzgadoCuenta[]
     */
    public function getJuzgadoCuentas(): Collection
    {
        return $this->juzgadoCuentas;
    }

    public function addJuzgadoCuenta(JuzgadoCuenta $juzgadoCuenta): self
    {
        if (!$this->juzgadoCuentas->contains($juzgadoCuenta)) {
            $this->juzgadoCuentas[] = $juzgadoCuenta;
            $juzgadoCuenta->setJuzgado($this);
        }

        return $this;
    }

    public function removeJuzgadoCuenta(JuzgadoCuenta $juzgadoCuenta): self
    {
        if ($this->juzgadoCuentas->removeElement($juzgadoCuenta)) {
            // set the owning side to null (unless already changed)
            if ($juzgadoCuenta->getJuzgado() === $this) {
                $juzgadoCuenta->setJuzgado(null);
            }
        }

        return $this;
    }

    public function getCorte(): ?Corte
    {
        return $this->corte;
    }

    public function setCorte(?Corte $corte): self
    {
        $this->corte = $corte;

        return $this;
    }

    public function getPjudTribunalId(): ?string
    {
        return $this->pjudTribunalId;
    }

    public function setPjudTribunalId(?string $pjudTribunalId): self
    {
        $this->pjudTribunalId = $pjudTribunalId;

        return $this;
    }
    
}
