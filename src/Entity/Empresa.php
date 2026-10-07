<?php

namespace App\Entity;

use App\Repository\EmpresaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmpresaRepository::class)]
class Empresa
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255)]
    private $nombre;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $rol;

    #[ORM\Column(type: "string", length: 20)]
    private $rut;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $logo;

    #[ORM\Column(type: "datetime")]
    private $fechaIngreso;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $fechaUltimamodificacion;

    #[ORM\Column(type: "datetime")]
    private $fechaVigencia;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $edapiKey;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $edapiClienteGuid;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private bool $lexflowHabilitado = false;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private bool $lexflowSoloCrm = false;

    /** Habilita el botón "Detalle PJUD" para la empresa (además de tener las credenciales). */
    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private bool $pjudHabilitado = false;

    /** Credenciales de la integración api-pjud (api-pjud.codifica.cl). */
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $pjudClientKey = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $pjudEmail = null;

    #[ORM\Column(type: "encrypted_string", length: 255, nullable: true)]
    private ?string $pjudPassword = null;

    /**
     * Credenciales de la empresa en la Oficina Judicial Virtual (las usa api-pjud para sincronizar
     * causas). Las edita el administrador desde Mantención → Clave Poder Judicial (PjudCredencialesController).
     */
    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $pjudRut = null;

    #[ORM\Column(type: "encrypted_string", length: 255, nullable: true)]
    private ?string $pjudClave = null;

    /** 1 = Clave del Poder Judicial, 2 = ClaveÚnica */
    #[ORM\Column(type: "smallint", options: ["default" => 1])]
    private int $pjudMetodoLogin = 1;

    #[ORM\OneToMany(targetEntity: Cuenta::class, mappedBy: "empresa")]
    private $cuentas;

    #[ORM\OneToMany(targetEntity: Modulo::class, mappedBy: "empresa", orphanRemoval: true)]
    private $modulos;

    #[ORM\OneToMany(targetEntity: Accion::class, mappedBy: "empresa")]
    private $acciones;

    #[ORM\OneToMany(targetEntity: Menu::class, mappedBy: "empresa", orphanRemoval: true)]
    private $menus;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $logoAlt;

    #[ORM\OneToMany(targetEntity: ModuloPer::class, mappedBy: "empresa", orphanRemoval: true)]
    private $moduloPers;

    #[ORM\OneToMany(targetEntity: MenuCabezera::class, mappedBy: "empresa")]
    private $menuCabezeras;

    #[ORM\OneToMany(targetEntity: UsuarioTipo::class, mappedBy: "empresa")]
    private $usuarioTipos;

    #[ORM\OneToMany(targetEntity: UsuarioCategoria::class, mappedBy: "empresa")]
    private $usuarioCategorias;

    #[ORM\OneToMany(targetEntity: EstadoCivil::class, mappedBy: "empresa")]
    private $estadoCivils;

    #[ORM\OneToMany(targetEntity: SituacionLaboral::class, mappedBy: "empresa")]
    private $situacionLaborals;

    #[ORM\OneToMany(targetEntity: Escritura::class, mappedBy: "empresa")]
    private $escrituras;

    #[ORM\OneToMany(targetEntity: Juzgado::class, mappedBy: "empresa")]
    private $juzgados;

    #[ORM\OneToMany(targetEntity: Reunion::class, mappedBy: "empresa")]
    private $reunions;

    #[ORM\OneToMany(targetEntity: Pais::class, mappedBy: "empresa")]
    private $pais;

    #[ORM\OneToMany(targetEntity: ContratoVivienda::class, mappedBy: "empresa")]
    private $contratoViviendas;

    #[ORM\OneToMany(targetEntity: ContratoVehiculo::class, mappedBy: "empresa")]
    private $contratoVehiculos;

    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: "empresa")]
    private $tickets;

    #[ORM\OneToMany(targetEntity: RecordatorioTipo::class, mappedBy: "empresa")]
    private $recordatorioTipos;

    #[ORM\OneToMany(targetEntity: Agenda::class, mappedBy: "empresa")]
    private $agendas;

    
    public function __construct()
    {
        $this->cuentas = new ArrayCollection();
        $this->acciones = new ArrayCollection();
        $this->menus = new ArrayCollection();
        $this->moduloPers = new ArrayCollection();
        $this->menuCabezeras = new ArrayCollection();
        $this->usuarioTipos = new ArrayCollection();
        $this->usuarioCategorias = new ArrayCollection();
        $this->estadoCivils = new ArrayCollection();
        $this->situacionLaborals = new ArrayCollection();
        $this->escrituras = new ArrayCollection();
        $this->juzgados = new ArrayCollection();
        $this->reunions = new ArrayCollection();
        $this->pais = new ArrayCollection();
        $this->contratoViviendas = new ArrayCollection();
        $this->contratoVehiculos = new ArrayCollection();
        $this->tickets = new ArrayCollection();
        $this->recordatorioTipos = new ArrayCollection();
        $this->agendas = new ArrayCollection();
        
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

    public function getRol(): ?string
    {
        return $this->rol;
    }

    public function setRol(?string $rol): self
    {
        $this->rol = $rol;

        return $this;
    }

    public function getRut(): ?string
    {
        return $this->rut;
    }

    public function setRut(string $rut): self
    {
        $this->rut = $rut;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getFechaIngreso(): ?\DateTimeInterface
    {
        return $this->fechaIngreso;
    }

    public function setFechaIngreso(\DateTimeInterface $fechaIngreso): self
    {
        $this->fechaIngreso = $fechaIngreso;

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

    public function getFechaVigencia(): ?\DateTimeInterface
    {
        return $this->fechaVigencia;
    }

    public function setFechaVigencia(\DateTimeInterface $fechaVigencia): self
    {
        $this->fechaVigencia = $fechaVigencia;

        return $this;
    }

    /**
     * @return Collection|Cuenta[]
     */
    public function getCuentas(): Collection
    {
        return $this->cuentas;
    }

    public function addCuenta(Cuenta $cuenta): self
    {
        if (!$this->cuentas->contains($cuenta)) {
            $this->cuentas[] = $cuenta;
            $cuenta->setEmpresa($this);
        }

        return $this;
    }

    public function removeCuenta(Cuenta $cuenta): self
    {
        if ($this->cuentas->removeElement($cuenta)) {
            // set the owning side to null (unless already changed)
            if ($cuenta->getEmpresa() === $this) {
                $cuenta->setEmpresa(null);
            }
        }

        return $this;
    }
    public function __toString(){
        return $this->getNombre();
    }

    

    /**
     * @return Collection|Accion[]
     */
    public function getAcciones(): Collection
    {
        return $this->acciones;
    }

    public function addAccione(Accion $accione): self
    {
        if (!$this->acciones->contains($accione)) {
            $this->acciones[] = $accione;
            $accione->setEmpresa($this);
        }

        return $this;
    }

    public function removeAccione(Accion $accione): self
    {
        if ($this->acciones->removeElement($accione)) {
            // set the owning side to null (unless already changed)
            if ($accione->getEmpresa() === $this) {
                $accione->setEmpresa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Menu[]
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    public function addMenu(Menu $menu): self
    {
        if (!$this->menus->contains($menu)) {
            $this->menus[] = $menu;
            $menu->setEmpresa($this);
        }

        return $this;
    }

    public function removeMenu(Menu $menu): self
    {
        if ($this->menus->removeElement($menu)) {
            // set the owning side to null (unless already changed)
            if ($menu->getEmpresa() === $this) {
                $menu->setEmpresa(null);
            }
        }

        return $this;
    }

    public function getLogoAlt(): ?string
    {
        return $this->logoAlt;
    }

    public function setLogoAlt(?string $logoAlt): self
    {
        $this->logoAlt = $logoAlt;

        return $this;
    }

    /**
     * @return Collection|ModuloPer[]
     */
    public function getModuloPers(): Collection
    {
        return $this->moduloPers;
    }

    public function addModuloPer(ModuloPer $moduloPer): self
    {
        if (!$this->moduloPers->contains($moduloPer)) {
            $this->moduloPers[] = $moduloPer;
            $moduloPer->setEmpresa($this);
        }

        return $this;
    }

    public function removeModuloPer(ModuloPer $moduloPer): self
    {
        if ($this->moduloPers->removeElement($moduloPer)) {
            // set the owning side to null (unless already changed)
            if ($moduloPer->getEmpresa() === $this) {
                $moduloPer->setEmpresa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MenuCabezera[]
     */
    public function getMenuCabezeras(): Collection
    {
        return $this->menuCabezeras;
    }

    public function addMenuCabezera(MenuCabezera $menuCabezera): self
    {
        if (!$this->menuCabezeras->contains($menuCabezera)) {
            $this->menuCabezeras[] = $menuCabezera;
            $menuCabezera->setEmpresa($this);
        }

        return $this;
    }

    public function removeMenuCabezera(MenuCabezera $menuCabezera): self
    {
        if ($this->menuCabezeras->removeElement($menuCabezera)) {
            // set the owning side to null (unless already changed)
            if ($menuCabezera->getEmpresa() === $this) {
                $menuCabezera->setEmpresa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UsuarioTipo[]
     */
    public function getUsuarioTipos(): Collection
    {
        return $this->usuarioTipos;
    }

    public function addUsuarioTipo(UsuarioTipo $usuarioTipo): self
    {
        if (!$this->usuarioTipos->contains($usuarioTipo)) {
            $this->usuarioTipos[] = $usuarioTipo;
            $usuarioTipo->setEmpresa($this);
        }

        return $this;
    }

    public function removeUsuarioTipo(UsuarioTipo $usuarioTipo): self
    {
        if ($this->usuarioTipos->removeElement($usuarioTipo)) {
            // set the owning side to null (unless already changed)
            if ($usuarioTipo->getEmpresa() === $this) {
                $usuarioTipo->setEmpresa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UsuarioCategoria[]
     */
    public function getUsuarioCategorias(): Collection
    {
        return $this->usuarioCategorias;
    }

    public function addUsuarioCategoria(UsuarioCategoria $usuarioCategoria): self
    {
        if (!$this->usuarioCategorias->contains($usuarioCategoria)) {
            $this->usuarioCategorias[] = $usuarioCategoria;
            $usuarioCategoria->setEmpresa($this);
        }

        return $this;
    }

    public function removeUsuarioCategoria(UsuarioCategoria $usuarioCategoria): self
    {
        if ($this->usuarioCategorias->removeElement($usuarioCategoria)) {
            // set the owning side to null (unless already changed)
            if ($usuarioCategoria->getEmpresa() === $this) {
                $usuarioCategoria->setEmpresa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|EstadoCivil[]
     */
    public function getEstadoCivils(): Collection
    {
        return $this->estadoCivils;
    }

    public function addEstadoCivil(EstadoCivil $estadoCivil): self
    {
        if (!$this->estadoCivils->contains($estadoCivil)) {
            $this->estadoCivils[] = $estadoCivil;
            $estadoCivil->setEmpresa($this);
        }

        return $this;
    }

    public function removeEstadoCivil(EstadoCivil $estadoCivil): self
    {
        if ($this->estadoCivils->removeElement($estadoCivil)) {
            // set the owning side to null (unless already changed)
            if ($estadoCivil->getEmpresa() === $this) {
                $estadoCivil->setEmpresa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SituacionLaboral[]
     */
    public function getSituacionLaborals(): Collection
    {
        return $this->situacionLaborals;
    }

    public function addSituacionLaboral(SituacionLaboral $situacionLaboral): self
    {
        if (!$this->situacionLaborals->contains($situacionLaboral)) {
            $this->situacionLaborals[] = $situacionLaboral;
            $situacionLaboral->setEmpresa($this);
        }

        return $this;
    }

    public function removeSituacionLaboral(SituacionLaboral $situacionLaboral): self
    {
        if ($this->situacionLaborals->removeElement($situacionLaboral)) {
            // set the owning side to null (unless already changed)
            if ($situacionLaboral->getEmpresa() === $this) {
                $situacionLaboral->setEmpresa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Escritura[]
     */
    public function getEscrituras(): Collection
    {
        return $this->escrituras;
    }

    public function addEscritura(Escritura $escritura): self
    {
        if (!$this->escrituras->contains($escritura)) {
            $this->escrituras[] = $escritura;
            $escritura->setEmpresa($this);
        }

        return $this;
    }

    public function removeEscritura(Escritura $escritura): self
    {
        if ($this->escrituras->removeElement($escritura)) {
            // set the owning side to null (unless already changed)
            if ($escritura->getEmpresa() === $this) {
                $escritura->setEmpresa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Juzgado[]
     */
    public function getJuzgados(): Collection
    {
        return $this->juzgados;
    }

    public function addJuzgado(Juzgado $juzgado): self
    {
        if (!$this->juzgados->contains($juzgado)) {
            $this->juzgados[] = $juzgado;
            $juzgado->setEmpresa($this);
        }

        return $this;
    }

    public function removeJuzgado(Juzgado $juzgado): self
    {
        if ($this->juzgados->removeElement($juzgado)) {
            // set the owning side to null (unless already changed)
            if ($juzgado->getEmpresa() === $this) {
                $juzgado->setEmpresa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Reunion[]
     */
    public function getReunions(): Collection
    {
        return $this->reunions;
    }

    public function addReunion(Reunion $reunion): self
    {
        if (!$this->reunions->contains($reunion)) {
            $this->reunions[] = $reunion;
            $reunion->setEmpresa($this);
        }

        return $this;
    }

    public function removeReunion(Reunion $reunion): self
    {
        if ($this->reunions->removeElement($reunion)) {
            // set the owning side to null (unless already changed)
            if ($reunion->getEmpresa() === $this) {
                $reunion->setEmpresa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Pais[]
     */
    public function getPais(): Collection
    {
        return $this->pais;
    }

    public function addPai(Pais $pai): self
    {
        if (!$this->pais->contains($pai)) {
            $this->pais[] = $pai;
            $pai->setEmpresa($this);
        }

        return $this;
    }

    public function removePai(Pais $pai): self
    {
        if ($this->pais->removeElement($pai)) {
            // set the owning side to null (unless already changed)
            if ($pai->getEmpresa() === $this) {
                $pai->setEmpresa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ContratoVivienda[]
     */
    public function getContratoViviendas(): Collection
    {
        return $this->contratoViviendas;
    }

    public function addContratoVivienda(ContratoVivienda $contratoVivienda): self
    {
        if (!$this->contratoViviendas->contains($contratoVivienda)) {
            $this->contratoViviendas[] = $contratoVivienda;
            $contratoVivienda->setEmpresa($this);
        }

        return $this;
    }

    public function removeContratoVivienda(ContratoVivienda $contratoVivienda): self
    {
        if ($this->contratoViviendas->removeElement($contratoVivienda)) {
            // set the owning side to null (unless already changed)
            if ($contratoVivienda->getEmpresa() === $this) {
                $contratoVivienda->setEmpresa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ContratoVehiculo[]
     */
    public function getContratoVehiculos(): Collection
    {
        return $this->contratoVehiculos;
    }

    public function addContratoVehiculo(ContratoVehiculo $contratoVehiculo): self
    {
        if (!$this->contratoVehiculos->contains($contratoVehiculo)) {
            $this->contratoVehiculos[] = $contratoVehiculo;
            $contratoVehiculo->setEmpresa($this);
        }

        return $this;
    }

    public function removeContratoVehiculo(ContratoVehiculo $contratoVehiculo): self
    {
        if ($this->contratoVehiculos->removeElement($contratoVehiculo)) {
            // set the owning side to null (unless already changed)
            if ($contratoVehiculo->getEmpresa() === $this) {
                $contratoVehiculo->setEmpresa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): self
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets[] = $ticket;
            $ticket->setEmpresa($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getEmpresa() === $this) {
                $ticket->setEmpresa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|RecordatorioTipo[]
     */
    public function getRecordatorioTipos(): Collection
    {
        return $this->recordatorioTipos;
    }

    public function addRecordatorioTipo(RecordatorioTipo $recordatorioTipo): self
    {
        if (!$this->recordatorioTipos->contains($recordatorioTipo)) {
            $this->recordatorioTipos[] = $recordatorioTipo;
            $recordatorioTipo->setEmpresa($this);
        }

        return $this;
    }

    public function removeRecordatorioTipo(RecordatorioTipo $recordatorioTipo): self
    {
        if ($this->recordatorioTipos->removeElement($recordatorioTipo)) {
            // set the owning side to null (unless already changed)
            if ($recordatorioTipo->getEmpresa() === $this) {
                $recordatorioTipo->setEmpresa(null);
            }
        }

        return $this;
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
            $agenda->setEmpresa($this);
        }

        return $this;
    }

    public function removeAgenda(Agenda $agenda): self
    {
        if ($this->agendas->removeElement($agenda)) {
            // set the owning side to null (unless already changed)
            if ($agenda->getEmpresa() === $this) {
                $agenda->setEmpresa(null);
            }
        }

        return $this;
    }

    

    public function getEdapiKey(): ?string
    {
        return $this->edapiKey;
    }

    public function setEdapiKey(?string $edapiKey): self
    {
        $this->edapiKey = $edapiKey;
        return $this;
    }

    public function isLexflowHabilitado(): bool
    {
        return $this->lexflowHabilitado;
    }

    public function setLexflowHabilitado(bool $lexflowHabilitado): self
    {
        $this->lexflowHabilitado = $lexflowHabilitado;
        return $this;
    }

    public function getPjudClientKey(): ?string
    {
        return $this->pjudClientKey;
    }

    public function setPjudClientKey(?string $pjudClientKey): self
    {
        $this->pjudClientKey = $pjudClientKey;
        return $this;
    }

    public function getPjudEmail(): ?string
    {
        return $this->pjudEmail;
    }

    public function setPjudEmail(?string $pjudEmail): self
    {
        $this->pjudEmail = $pjudEmail;
        return $this;
    }

    public function getPjudPassword(): ?string
    {
        return $this->pjudPassword;
    }

    public function setPjudPassword(?string $pjudPassword): self
    {
        $this->pjudPassword = $pjudPassword;
        return $this;
    }

    public function isPjudHabilitado(): bool
    {
        return $this->pjudHabilitado;
    }

    public function setPjudHabilitado(bool $pjudHabilitado): self
    {
        $this->pjudHabilitado = $pjudHabilitado;
        return $this;
    }

    public function getPjudRut(): ?string
    {
        return $this->pjudRut;
    }

    public function setPjudRut(?string $pjudRut): self
    {
        $this->pjudRut = $pjudRut;
        return $this;
    }

    public function getPjudClave(): ?string
    {
        return $this->pjudClave;
    }

    public function setPjudClave(?string $pjudClave): self
    {
        $this->pjudClave = $pjudClave;
        return $this;
    }

    public function getPjudMetodoLogin(): int
    {
        return $this->pjudMetodoLogin;
    }

    public function setPjudMetodoLogin(int $pjudMetodoLogin): self
    {
        $this->pjudMetodoLogin = $pjudMetodoLogin;
        return $this;
    }

    /** Botón Detalle PJUD disponible: flag activo y credenciales completas. */
    public function isPjudConfigurado(): bool
    {
        return $this->pjudHabilitado && trim((string) $this->pjudEmail) !== '' && (string) $this->pjudPassword !== '';
    }

    public function isLexflowSoloCrm(): bool
    {
        return $this->lexflowSoloCrm;
    }

    public function setLexflowSoloCrm(bool $lexflowSoloCrm): self
    {
        $this->lexflowSoloCrm = $lexflowSoloCrm;
        return $this;
    }

    public function getEdapiClienteGuid(): ?string
    {
        return $this->edapiClienteGuid;
    }

    public function setEdapiClienteGuid(?string $edapiClienteGuid): self
    {
        $this->edapiClienteGuid = $edapiClienteGuid;
        return $this;
    }
}
