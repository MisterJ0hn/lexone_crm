<?php

namespace App\Entity;

use App\Repository\CuentaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CuentaRepository::class)
 */
class Cuenta
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
    private $nombre;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fechaCreacion;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaUltimamodificacion;

    /**
     * @ORM\ManyToOne(targetEntity=Empresa::class, inversedBy="cuentas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $empresa;

    /**
     * @ORM\OneToMany(targetEntity=UsuarioCuenta::class, mappedBy="cuenta")
     */
    private $usuarioCuentas;

    /**
     * @ORM\OneToMany(targetEntity=Agenda::class, mappedBy="cuenta")
     */
    private $agendas;

    /**
     * @ORM\OneToMany(targetEntity=Sucursal::class, mappedBy="cuenta")
     */
    private $sucursals;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $pageId;

    /**
     * @ORM\OneToMany(targetEntity=UsuarioUsuariocategoria::class, mappedBy="cuenta")
     */
    private $usuarioUsuariocategorias;

    /**
     * @ORM\OneToMany(targetEntity=Importacion::class, mappedBy="cuenta")
     */
    private $importacions;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $vigenciaContratos;

    /**
     * @ORM\OneToMany(targetEntity=JuzgadoCuenta::class, mappedBy="cuenta")
     */
    private $juzgadoCuentas;

  

    public function __construct()
    {
        $this->usuarioCuentas = new ArrayCollection();
        $this->agendas = new ArrayCollection();
        $this->sucursals = new ArrayCollection();
        $this->usuarioUsuariocategorias = new ArrayCollection();
        $this->importacions = new ArrayCollection();
        $this->juzgadoCuentas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFechaCreacion(): ?\DateTimeInterface
    {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(\DateTimeInterface $fechaCreacion): self
    {
        $this->fechaCreacion = $fechaCreacion;

        return $this;
    }

    public function getFechaUltimamodificacion(): ?\DateTimeInterface
    {
        return $this->fechaUltimamodificacion;
    }

    public function setFechaUltimamodificacion(?\DateTimeInterface $fechaUltimamodificacion): self
    {
        $this->fechaUltimamodificacion = $fechaUltimamodificacion;

        return $this;
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

    /**
     * @return Collection|UsuarioCuenta[]
     */
    public function getUsuarioCuentas(): Collection
    {
        return $this->usuarioCuentas;
    }

    public function addUsuarioCuenta(UsuarioCuenta $usuarioCuenta): self
    {
        if (!$this->usuarioCuentas->contains($usuarioCuenta)) {
            $this->usuarioCuentas[] = $usuarioCuenta;
            $usuarioCuenta->setCuenta($this);
        }

        return $this;
    }

    public function removeUsuarioCuenta(UsuarioCuenta $usuarioCuenta): self
    {
        if ($this->usuarioCuentas->removeElement($usuarioCuenta)) {
            // set the owning side to null (unless already changed)
            if ($usuarioCuenta->getCuenta() === $this) {
                $usuarioCuenta->setCuenta(null);
            }
        }

        return $this;
    }
    public function __toString()
    {
        return $this->getNombre();
    }

    /**
     * @return Collection|Agenda[]
     */
    public function getAgendas(): Collection
    {
        return $this->agendas;
    }

    public function addAgenda(Agenda $agenda): self
    {
        if (!$this->agendas->contains($agenda)) {
            $this->agendas[] = $agenda;
            $agenda->setCuenta($this);
        }

        return $this;
    }

    public function removeAgenda(Agenda $agenda): self
    {
        if ($this->agendas->removeElement($agenda)) {
            // set the owning side to null (unless already changed)
            if ($agenda->getCuenta() === $this) {
                $agenda->setCuenta(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Sucursal[]
     */
    public function getSucursals(): Collection
    {
        return $this->sucursals;
    }

    public function addSucursal(Sucursal $sucursal): self
    {
        if (!$this->sucursals->contains($sucursal)) {
            $this->sucursals[] = $sucursal;
            $sucursal->setCuenta($this);
        }

        return $this;
    }

    public function removeSucursal(Sucursal $sucursal): self
    {
        if ($this->sucursals->removeElement($sucursal)) {
            // set the owning side to null (unless already changed)
            if ($sucursal->getCuenta() === $this) {
                $sucursal->setCuenta(null);
            }
        }

        return $this;
    }

    public function getPageId(): ?float
    {
        return $this->pageId;
    }

    public function setPageId(?float $pageId): self
    {
        $this->pageId = $pageId;

        return $this;
    }

    /**
     * @return Collection|UsuarioUsuariocategoria[]
     */
    public function getUsuarioUsuariocategorias(): Collection
    {
        return $this->usuarioUsuariocategorias;
    }

    public function addUsuarioUsuariocategoria(UsuarioUsuariocategoria $usuarioUsuariocategoria): self
    {
        if (!$this->usuarioUsuariocategorias->contains($usuarioUsuariocategoria)) {
            $this->usuarioUsuariocategorias[] = $usuarioUsuariocategoria;
            $usuarioUsuariocategoria->setCuenta($this);
        }

        return $this;
    }

    public function removeUsuarioUsuariocategoria(UsuarioUsuariocategoria $usuarioUsuariocategoria): self
    {
        if ($this->usuarioUsuariocategorias->removeElement($usuarioUsuariocategoria)) {
            // set the owning side to null (unless already changed)
            if ($usuarioUsuariocategoria->getCuenta() === $this) {
                $usuarioUsuariocategoria->setCuenta(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Importacion[]
     */
    public function getImportacions(): Collection
    {
        return $this->importacions;
    }

    public function addImportacion(Importacion $importacion): self
    {
        if (!$this->importacions->contains($importacion)) {
            $this->importacions[] = $importacion;
            $importacion->setCuenta($this);
        }

        return $this;
    }

    public function removeImportacion(Importacion $importacion): self
    {
        if ($this->importacions->removeElement($importacion)) {
            // set the owning side to null (unless already changed)
            if ($importacion->getCuenta() === $this) {
                $importacion->setCuenta(null);
            }
        }

        return $this;
    }

    public function getVigenciaContratos(): ?int
    {
        return $this->vigenciaContratos;
    }

    public function setVigenciaContratos(?int $vigenciaContratos): self
    {
        $this->vigenciaContratos = $vigenciaContratos;

        return $this;
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
            $juzgadoCuenta->setCuenta($this);
        }

        return $this;
    }

    public function removeJuzgadoCuenta(JuzgadoCuenta $juzgadoCuenta): self
    {
        if ($this->juzgadoCuentas->removeElement($juzgadoCuenta)) {
            // set the owning side to null (unless already changed)
            if ($juzgadoCuenta->getCuenta() === $this) {
                $juzgadoCuenta->setCuenta(null);
            }
        }

        return $this;
    }

    
}
