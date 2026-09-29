<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UsuarioRepository::class)
 */
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;
    
    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $usernameOriginal;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $nombre;

    /**
     * @ORM\Column(type="boolean")
     */
    private $estado;

    /**
     * @ORM\Column(type="datetime")
     */
    private $fecha_activacion;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $correo;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $token;

    /**
     * @ORM\ManyToOne(targetEntity=UsuarioTipo::class, inversedBy="usuarios")
     * @ORM\JoinColumn(nullable=false)
     */
    private $usuarioTipo;

    /**
     * Empresa a la que pertenece el usuario (membresia directa). Fuente de verdad
     * de getEmpresaActual(). El super-admin (usuarioTipo=8) puede ademas operar
     * otras empresas via el override de sesion (ver TenantSubscriber).
     *
     * @ORM\ManyToOne(targetEntity=Empresa::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $empresa;


    /**
     * @ORM\OneToMany(targetEntity=UsuarioCuenta::class, mappedBy="usuario")
     */
    private $usuarioCuentas;

    /**
     * @ORM\OneToMany(targetEntity=Privilegio::class, mappedBy="usuario", orphanRemoval=true)
     */
    private $privilegios;

    /**
     * @deprecated Valor histórico persistido. La "empresa actual" ya no se decide
     * escribiendo esta columna (eso mezclaba "a qué empresas puedo entrar" con
     * "cuál estoy viendo ahora" y se rompía con sesiones/pestañas concurrentes).
     * Ver $empresaActualOverride y App\EventListener\TenantSubscriber.
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $empresaActual;

    /**
     * Empresa "actual" resuelta para el request en curso. Deliberadamente sin
     * anotacion de mapeo: TenantSubscriber la fija en cada request desde la
     * sesion, asi que nunca puede colarse en un UPDATE ni persistirse por
     * accidente cuando el usuario se guarda por otro motivo. getEmpresaActual()
     * la prefiere sobre el valor historico de BD; todo el codigo existente que ya
     * llama a getEmpresaActual() se beneficia sin cambios.
     */
    private $empresaActualOverride;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaNoDisponible;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $whatsapp;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $telefono;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rut;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $direccion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sexo;

    /**
     * @ORM\ManyToOne(targetEntity=UsuarioCategoria::class, inversedBy="usuarios")
     */
    private $categoria;

    /**
     * @ORM\ManyToOne(targetEntity=UsuarioStatus::class, inversedBy="usuarios")
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity=Agenda::class, mappedBy="agendador")
     */
    private $agendas;

    /**
     * @ORM\ManyToOne(targetEntity=UsuarioTipoDocumento::class, inversedBy="usuarios")
     */
    private $tipoDocumento;

    /**
     * @ORM\OneToMany(targetEntity=Agenda::class, mappedBy="abogado")
     */
    private $agendaAbogados;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $color;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $passwordAnt;

    /**
     * @ORM\OneToMany(targetEntity=UsuarioUsuariocategoria::class, mappedBy="usuario", orphanRemoval=true)
     */
    private $usuarioUsuariocategorias;

    /**
     * @ORM\OneToMany(targetEntity=ContratoRol::class, mappedBy="abogado")
     */
    private $contratoRols;

    /**
     * @ORM\OneToMany(targetEntity=Importacion::class, mappedBy="usuarioCarga")
     */
    private $importacions;

    /**
     * @ORM\OneToMany(targetEntity=AgendaObservacion::class, mappedBy="usuarioRegistro")
     */
    private $agendaObservacions;

    /**
     * @ORM\OneToMany(targetEntity=Contrato::class, mappedBy="tramitador")
     */
    private $contratos;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $lunes;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $lunesStart;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $lunesEnd;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $martes;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $martesStart;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $martesEnd;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $miercoles;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $miercolesStart;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $miercolesEnd;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $jueves;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $juevesStart;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $juevesEnd;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $viernes;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $viernesStart;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $viernesEnd;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $sabado;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $sabadoStart;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $sabadoEnd;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $domingo;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $domingoStart;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $domingoEnd;

    /**
     * @ORM\OneToMany(targetEntity=UsuarioNoDisponible::class, mappedBy="usuario")
     */
    private $usuarioNoDisponibles;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sobrecupo;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $lotes = [];

    /**
     * @ORM\OneToMany(targetEntity=UsuarioLote::class, mappedBy="usuario")
     */
    private $usuarioLotes;

    /**
     * @ORM\OneToMany(targetEntity=Reportes::class, mappedBy="usuario", orphanRemoval=true)
     */
    private $reportes;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $tokuId;

    /**
     * @ORM\OneToMany(targetEntity=ContratoAudios::class, mappedBy="usuarioRegistro")
     */
    private $contratoAudios;

    /**
     * @ORM\OneToMany(targetEntity=Ticket::class, mappedBy="origen")
     */
    private $tickets;

    /**
     * @ORM\OneToMany(targetEntity=TicketHistorial::class, mappedBy="usuarioRegistro")
     */
    private $ticketHistorials;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fechaNacimiento;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $fechaAviso;

    /**
     * @ORM\OneToMany(targetEntity=CausaObservacion::class, mappedBy="usuarioRegistro")
     */
    private $causaObservacions;

    /**
     * @ORM\OneToMany(targetEntity=Canal::class, mappedBy="usuarioRegistro")
     */
    private $canals;

    /**
     * @ORM\OneToMany(targetEntity=Recordatorio::class, mappedBy="usuarioRegistro")
     */
    private $recordatorios;

    /**
     * @ORM\OneToMany(targetEntity=ContratoArchivos::class, mappedBy="usuarioRegistro")
     */
    private $contratoArchivos;

        /**
     * Fecha en que caduca la contraseña. NULL = sin caducidad configurada.
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $passwordExpiracion;

    /**
     * @ORM\OneToMany(targetEntity=PasswordHistorial::class, mappedBy="usuario", orphanRemoval=true)
     */
    private $passwordHistorials;
    
    public function __construct()
    {
        $this->usuarioCuentas = new ArrayCollection();
        $this->privilegios = new ArrayCollection();
        $this->agendas = new ArrayCollection();
        $this->agendaAbogados = new ArrayCollection();
        $this->usuarioUsuariocategorias = new ArrayCollection();
        $this->contratoRols = new ArrayCollection();
        $this->importacions = new ArrayCollection();
        $this->agendaObservacions = new ArrayCollection();
        $this->contratos = new ArrayCollection();
        $this->usuarioNoDisponibles = new ArrayCollection();
        $this->usuarioLotes = new ArrayCollection();
        $this->reportes = new ArrayCollection();
        $this->contratoAudios = new ArrayCollection();
        $this->tickets = new ArrayCollection();
        $this->ticketHistorials = new ArrayCollection();
        $this->causaObservacions = new ArrayCollection();
        $this->canals = new ArrayCollection();
        $this->recordatorios = new ArrayCollection();
        $this->contratoArchivos = new ArrayCollection();
         $this->passwordHistorials = new ArrayCollection();
        $this->passwordExpiracion= new  \DateTime(date("Y-m-d H:i:s"));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getUsernameOriginal(): string
    {
        return (string) $this->usernameOriginal;
    }

    public function setUsernameOriginal(string $usernameOriginal): self
    {
        $this->usernameOriginal = $usernameOriginal;
        return $this;
    }
    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        //$roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[]= 'ROLE_USER';

        return $roles;
    }

   

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getEstado(): ?bool
    {
        return $this->estado;
    }

    public function setEstado(bool $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    public function getFechaActivacion(): ?\DateTimeInterface
    {
        return $this->fecha_activacion;
    }

    public function setFechaActivacion(\DateTimeInterface $fecha_activacion): self
    {
        $this->fecha_activacion = $fecha_activacion;

        return $this;
    }

    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    public function setCorreo(string $correo): self
    {
        $this->correo = $correo;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getUsuarioTipo(): ?UsuarioTipo
    {
        return $this->usuarioTipo;
    }

    public function setUsuarioTipo(?UsuarioTipo $usuarioTipo): self
    {
        $this->usuarioTipo = $usuarioTipo;

        return $this;
    }

    public function __toString()
    {
        return $this->nombre;
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
            $usuarioCuenta->setUsuario($this);
        }

        return $this;
    }

    public function removeUsuarioCuenta(UsuarioCuenta $usuarioCuenta): self
    {
        if ($this->usuarioCuentas->removeElement($usuarioCuenta)) {
            // set the owning side to null (unless already changed)
            if ($usuarioCuenta->getUsuario() === $this) {
                $usuarioCuenta->setUsuario(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Privilegio[]
     */
    public function getPrivilegios(): Collection
    {
        return $this->privilegios;
    }

    public function addPrivilegio(Privilegio $privilegio): self
    {
        if (!$this->privilegios->contains($privilegio)) {
            $this->privilegios[] = $privilegio;
            $privilegio->setUsuario($this);
        }

        return $this;
    }

    public function removePrivilegio(Privilegio $privilegio): self
    {
        if ($this->privilegios->removeElement($privilegio)) {
            // set the owning side to null (unless already changed)
            if ($privilegio->getUsuario() === $this) {
                $privilegio->setUsuario(null);
            }
        }

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
     * Id de la empresa "actual" del usuario. Prioridad:
     *  1. override de sesion (solo lo usa el super-admin al cambiar de empresa)
     *  2. la empresa de membresia directa ($this->empresa)
     *  3. el valor historico de la columna (fallback de transicion)
     */
    public function getEmpresaActual(): ?int
    {
        if ($this->empresaActualOverride !== null) {
            return $this->empresaActualOverride;
        }
        if ($this->empresa !== null) {
            return $this->empresa->getId();
        }

        return $this->empresaActual;
    }

    /**
     * @deprecated Escribe el valor historico persistido. La membresia real es
     * $this->empresa (setEmpresa). Se mantiene por compatibilidad de transicion.
     */
    public function setEmpresaActual(?int $empresaActual): self
    {
        $this->empresaActual = $empresaActual;

        return $this;
    }

    /**
     * Fijado por TenantSubscriber en cada request desde la sesión. No persiste.
     */
    public function setEmpresaActualOverride(?int $empresaActual): self
    {
        $this->empresaActualOverride = $empresaActual;

        return $this;
    }

    public function getFechaNoDisponible(): ?\DateTimeInterface
    {
        return $this->fechaNoDisponible;
    }

    public function setFechaNoDisponible(?\DateTimeInterface $fechaNoDisponible): self
    {
        $this->fechaNoDisponible = $fechaNoDisponible;

        return $this;
    }

    public function getWhatsapp(): ?string
    {
        return $this->whatsapp;
    }

    public function setWhatsapp(?string $whatsapp): self
    {
        $this->whatsapp = $whatsapp;

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

    public function getRut(): ?string
    {
        return $this->rut;
    }

    public function setRut(?string $rut): self
    {
        $this->rut = $rut;

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

    public function getSexo(): ?string
    {
        return $this->sexo;
    }

    public function setSexo(string $sexo): self
    {
        $this->sexo = $sexo;

        return $this;
    }

    public function getCategoria(): ?UsuarioCategoria
    {
        return $this->categoria;
    }

    public function setCategoria(?UsuarioCategoria $categoria): self
    {
        $this->categoria = $categoria;

        return $this;
    }

    public function getStatus(): ?UsuarioStatus
    {
        return $this->status;
    }

    public function setStatus(?UsuarioStatus $status): self
    {
        $this->status = $status;

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
            $agenda->setAgendador($this);
        }

        return $this;
    }

    public function removeAgenda(Agenda $agenda): self
    {
        if ($this->agendas->removeElement($agenda)) {
            // set the owning side to null (unless already changed)
            if ($agenda->getAgendador() === $this) {
                $agenda->setAgendador(null);
            }
        }

        return $this;
    }

    public function getTipoDocumento(): ?UsuarioTipoDocumento
    {
        return $this->tipoDocumento;
    }

    public function setTipoDocumento(?UsuarioTipoDocumento $tipoDocumento): self
    {
        $this->tipoDocumento = $tipoDocumento;

        return $this;
    }

    /**
     * @return Collection|Agenda[]
     */
    public function getAgendaAbogados(): Collection
    {
        return $this->agendaAbogados;
    }

    public function addAgendaAbogado(Agenda $agendaAbogado): self
    {
        if (!$this->agendaAbogados->contains($agendaAbogado)) {
            $this->agendaAbogados[] = $agendaAbogado;
            $agendaAbogado->setAbogado($this);
        }

        return $this;
    }

    public function removeAgendaAbogado(Agenda $agendaAbogado): self
    {
        if ($this->agendaAbogados->removeElement($agendaAbogado)) {
            // set the owning side to null (unless already changed)
            if ($agendaAbogado->getAbogado() === $this) {
                $agendaAbogado->setAbogado(null);
            }
        }

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getPasswordAnt(): ?string
    {
        return $this->passwordAnt;
    }

    public function setPasswordAnt(?string $passwordAnt): self
    {
        $this->passwordAnt = $passwordAnt;

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
            $usuarioUsuariocategoria->setUsuario($this);
        }

        return $this;
    }

    public function removeUsuarioUsuariocategoria(UsuarioUsuariocategoria $usuarioUsuariocategoria): self
    {
        if ($this->usuarioUsuariocategorias->removeElement($usuarioUsuariocategoria)) {
            // set the owning side to null (unless already changed)
            if ($usuarioUsuariocategoria->getUsuario() === $this) {
                $usuarioUsuariocategoria->setUsuario(null);
            }
        }

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
            $contratoRol->setAbogado($this);
        }

        return $this;
    }

    public function removeContratoRol(ContratoRol $contratoRol): self
    {
        if ($this->contratoRols->removeElement($contratoRol)) {
            // set the owning side to null (unless already changed)
            if ($contratoRol->getAbogado() === $this) {
                $contratoRol->setAbogado(null);
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
            $importacion->setUsuarioCarga($this);
        }

        return $this;
    }

    public function removeImportacion(Importacion $importacion): self
    {
        if ($this->importacions->removeElement($importacion)) {
            // set the owning side to null (unless already changed)
            if ($importacion->getUsuarioCarga() === $this) {
                $importacion->setUsuarioCarga(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|AgendaObservacion[]
     */
    public function getAgendaObservacions(): Collection
    {
        return $this->agendaObservacions;
    }

    public function addAgendaObservacion(AgendaObservacion $agendaObservacion): self
    {
        if (!$this->agendaObservacions->contains($agendaObservacion)) {
            $this->agendaObservacions[] = $agendaObservacion;
            $agendaObservacion->setUsuarioRegistro($this);
        }

        return $this;
    }

    public function removeAgendaObservacion(AgendaObservacion $agendaObservacion): self
    {
        if ($this->agendaObservacions->removeElement($agendaObservacion)) {
            // set the owning side to null (unless already changed)
            if ($agendaObservacion->getUsuarioRegistro() === $this) {
                $agendaObservacion->setUsuarioRegistro(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Contrato[]
     */
    public function getContratos(): Collection
    {
        return $this->contratos;
    }

    public function addContrato(Contrato $contrato): self
    {
        if (!$this->contratos->contains($contrato)) {
            $this->contratos[] = $contrato;
            $contrato->setTramitador($this);
        }

        return $this;
    }

    public function removeContrato(Contrato $contrato): self
    {
        if ($this->contratos->removeElement($contrato)) {
            // set the owning side to null (unless already changed)
            if ($contrato->getTramitador() === $this) {
                $contrato->setTramitador(null);
            }
        }

        return $this;
    }

   

    public function getLunes(): ?bool
    {
        return $this->lunes;
    }

    public function setLunes(?bool $lunes): self
    {
        $this->lunes = $lunes;

        return $this;
    }

    public function getLunesStart(): ?\DateTimeInterface
    {
        return $this->lunesStart;
    }

    public function setLunesStart(?\DateTimeInterface $lunesStart): self
    {
        $this->lunesStart = $lunesStart;

        return $this;
    }

    public function getLunesEnd(): ?\DateTimeInterface
    {
        return $this->lunesEnd;
    }

    public function setLunesEnd(?\DateTimeInterface $lunesEnd): self
    {
        $this->lunesEnd = $lunesEnd;

        return $this;
    }

    public function getMartes(): ?bool
    {
        return $this->martes;
    }

    public function setMartes(?bool $martes): self
    {
        $this->martes = $martes;

        return $this;
    }

    public function getMartesStart(): ?\DateTimeInterface
    {
        return $this->martesStart;
    }

    public function setMartesStart(?\DateTimeInterface $martesStart): self
    {
        $this->martesStart = $martesStart;

        return $this;
    }

    public function getMartesEnd(): ?\DateTimeInterface
    {
        return $this->martesEnd;
    }

    public function setMartesEnd(?\DateTimeInterface $martesEnd): self
    {
        $this->martesEnd = $martesEnd;

        return $this;
    }

    public function getMiercoles(): ?bool
    {
        return $this->miercoles;
    }

    public function setMiercoles(?bool $miercoles): self
    {
        $this->miercoles = $miercoles;

        return $this;
    }

    public function getMiercolesStart(): ?\DateTimeInterface
    {
        return $this->miercolesStart;
    }

    public function setMiercolesStart(?\DateTimeInterface $miercolesStart): self
    {
        $this->miercolesStart = $miercolesStart;

        return $this;
    }

    public function getMiercolesEnd(): ?\DateTimeInterface
    {
        return $this->miercolesEnd;
    }

    public function setMiercolesEnd(?\DateTimeInterface $miercolesEnd): self
    {
        $this->miercolesEnd = $miercolesEnd;

        return $this;
    }

    public function getJueves(): ?bool
    {
        return $this->jueves;
    }

    public function setJueves(?bool $jueves): self
    {
        $this->jueves = $jueves;

        return $this;
    }

    public function getJuevesStart(): ?\DateTimeInterface
    {
        return $this->juevesStart;
    }

    public function setJuevesStart(?\DateTimeInterface $juevesStart): self
    {
        $this->juevesStart = $juevesStart;

        return $this;
    }

    public function getJuevesEnd(): ?\DateTimeInterface
    {
        return $this->juevesEnd;
    }

    public function setJuevesEnd(?\DateTimeInterface $juevesEnd): self
    {
        $this->juevesEnd = $juevesEnd;

        return $this;
    }

    public function getViernes(): ?bool
    {
        return $this->viernes;
    }

    public function setViernes(?bool $viernes): self
    {
        $this->viernes = $viernes;

        return $this;
    }

    public function getViernesStart(): ?\DateTimeInterface
    {
        return $this->viernesStart;
    }

    public function setViernesStart(?\DateTimeInterface $viernesStart): self
    {
        $this->viernesStart = $viernesStart;

        return $this;
    }

    public function getViernesEnd(): ?\DateTimeInterface
    {
        return $this->viernesEnd;
    }

    public function setViernesEnd(?\DateTimeInterface $viernesEnd): self
    {
        $this->viernesEnd = $viernesEnd;

        return $this;
    }

    public function getSabado(): ?bool
    {
        return $this->sabado;
    }

    public function setSabado(?bool $sabado): self
    {
        $this->sabado = $sabado;

        return $this;
    }

    public function getSabadoStart(): ?\DateTimeInterface
    {
        return $this->sabadoStart;
    }

    public function setSabadoStart(?\DateTimeInterface $sabadoStart): self
    {
        $this->sabadoStart = $sabadoStart;

        return $this;
    }

    public function getSabadoEnd(): ?\DateTimeInterface
    {
        return $this->sabadoEnd;
    }

    public function setSabadoEnd(?\DateTimeInterface $sabadoEnd): self
    {
        $this->sabadoEnd = $sabadoEnd;

        return $this;
    }

    public function getDomingo(): ?bool
    {
        return $this->domingo;
    }

    public function setDomingo(?bool $domingo): self
    {
        $this->domingo = $domingo;

        return $this;
    }

    public function getDomingoStart(): ?\DateTimeInterface
    {
        return $this->domingoStart;
    }

    public function setDomingoStart(?\DateTimeInterface $domingoStart): self
    {
        $this->domingoStart = $domingoStart;

        return $this;
    }

    public function getDomingoEnd(): ?\DateTimeInterface
    {
        return $this->domingoEnd;
    }

    public function setDomingoEnd(?\DateTimeInterface $domingoEnd): self
    {
        $this->domingoEnd = $domingoEnd;

        return $this;
    }

    /**
     * @return Collection|UsuarioNoDisponible[]
     */
    public function getUsuarioNoDisponibles(): Collection
    {
        return $this->usuarioNoDisponibles;
    }

    public function addUsuarioNoDisponible(UsuarioNoDisponible $usuarioNoDisponible): self
    {
        if (!$this->usuarioNoDisponibles->contains($usuarioNoDisponible)) {
            $this->usuarioNoDisponibles[] = $usuarioNoDisponible;
            $usuarioNoDisponible->setUsuario($this);
        }

        return $this;
    }

    public function removeUsuarioNoDisponible(UsuarioNoDisponible $usuarioNoDisponible): self
    {
        if ($this->usuarioNoDisponibles->removeElement($usuarioNoDisponible)) {
            // set the owning side to null (unless already changed)
            if ($usuarioNoDisponible->getUsuario() === $this) {
                $usuarioNoDisponible->setUsuario(null);
            }
        }

        return $this;
    }

    public function getSobrecupo(): ?int
    {
        return $this->sobrecupo;
    }

    public function setSobrecupo(?int $sobrecupo): self
    {
        $this->sobrecupo = $sobrecupo;

        return $this;
    }

    public function getLotes(): ?array
    {
        return $this->lotes;
    }

    public function setLotes(?array $lotes): self
    {
        $this->lotes = $lotes;

        return $this;
    }

    /**
     * @return Collection|UsuarioLote[]
     */
    public function getUsuarioLotes(): Collection
    {
        return $this->usuarioLotes;
    }

    public function addUsuarioLote(UsuarioLote $usuarioLote): self
    {
        if (!$this->usuarioLotes->contains($usuarioLote)) {
            $this->usuarioLotes[] = $usuarioLote;
            $usuarioLote->setUsuario($this);
        }

        return $this;
    }

    public function removeUsuarioLote(UsuarioLote $usuarioLote): self
    {
        if ($this->usuarioLotes->removeElement($usuarioLote)) {
            // set the owning side to null (unless already changed)
            if ($usuarioLote->getUsuario() === $this) {
                $usuarioLote->setUsuario(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Reportes[]
     */
    public function getReportes(): Collection
    {
        return $this->reportes;
    }

    public function addReporte(Reportes $reporte): self
    {
        if (!$this->reportes->contains($reporte)) {
            $this->reportes[] = $reporte;
            $reporte->setUsuario($this);
        }

        return $this;
    }

    public function removeReporte(Reportes $reporte): self
    {
        if ($this->reportes->removeElement($reporte)) {
            // set the owning side to null (unless already changed)
            if ($reporte->getUsuario() === $this) {
                $reporte->setUsuario(null);
            }
        }

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

    /**
     * @return Collection<int, ContratoAudios>
     */
    public function getContratoAudios(): Collection
    {
        return $this->contratoAudios;
    }

    public function addContratoAudio(ContratoAudios $contratoAudio): self
    {
        if (!$this->contratoAudios->contains($contratoAudio)) {
            $this->contratoAudios[] = $contratoAudio;
            $contratoAudio->setUsuarioRegistro($this);
        }

        return $this;
    }

    public function removeContratoAudio(ContratoAudios $contratoAudio): self
    {
        if ($this->contratoAudios->removeElement($contratoAudio)) {
            // set the owning side to null (unless already changed)
            if ($contratoAudio->getUsuarioRegistro() === $this) {
                $contratoAudio->setUsuarioRegistro(null);
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
            $ticket->setOrigen($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getOrigen() === $this) {
                $ticket->setOrigen(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TicketHistorial>
     */
    public function getTicketHistorials(): Collection
    {
        return $this->ticketHistorials;
    }

    public function addTicketHistorial(TicketHistorial $ticketHistorial): self
    {
        if (!$this->ticketHistorials->contains($ticketHistorial)) {
            $this->ticketHistorials[] = $ticketHistorial;
            $ticketHistorial->setUsuarioRegistro($this);
        }

        return $this;
    }

    public function removeTicketHistorial(TicketHistorial $ticketHistorial): self
    {
        if ($this->ticketHistorials->removeElement($ticketHistorial)) {
            // set the owning side to null (unless already changed)
            if ($ticketHistorial->getUsuarioRegistro() === $this) {
                $ticketHistorial->setUsuarioRegistro(null);
            }
        }

        return $this;
    }

    public function getFechaNacimiento(): ?\DateTimeInterface
    {
        return $this->fechaNacimiento;
    }

    public function setFechaNacimiento(?\DateTimeInterface $fechaNacimiento): self
    {
        $this->fechaNacimiento = $fechaNacimiento;

        return $this;
    }

    public function getFechaAviso(): ?\DateTimeInterface
    {
        return $this->fechaAviso;
    }

    public function setFechaAviso(?\DateTimeInterface $fechaAviso): self
    {
        $this->fechaAviso = $fechaAviso;

        return $this;
    }

    public function getDiaCumpleanio(): string
    {
        $diaCumpleanio="";
        if($this->fechaNacimiento != null){
            $diaCumpleanio=$this->fechaNacimiento->format("d");
        }
        return  $diaCumpleanio;
    }
    public function getMesCumpleanio(): string
    {
        $mesCumpleanio="";
        if($this->fechaNacimiento != null){
            $mesCumpleanio=$this->fechaNacimiento->format("m");
        }
        return  $mesCumpleanio;
    }


    /**
     * @return Collection|CausaObservacion[]
     */
    public function getCausaObservacions(): Collection
    {
        return $this->causaObservacions;
    }

    public function addCausaObservacion(CausaObservacion $causaObservacion): self
    {
        if (!$this->causaObservacions->contains($causaObservacion)) {
            $this->causaObservacions[] = $causaObservacion;
            $causaObservacion->setUsuarioRegistro($this);
        }

        return $this;
    }

    public function removeCausaObservacion(CausaObservacion $causaObservacion): self
    {
        if ($this->causaObservacions->removeElement($causaObservacion)) {
            // set the owning side to null (unless already changed)
            if ($causaObservacion->getUsuarioRegistro() === $this) {
                $causaObservacion->setUsuarioRegistro(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Canal[]
     */
    public function getCanals(): Collection
    {
        return $this->canals;
    }

    public function addCanal(Canal $canal): self
    {
        if (!$this->canals->contains($canal)) {
            $this->canals[] = $canal;
            $canal->setUsuarioRegistro($this);
        }

        return $this;
    }

    public function removeCanal(Canal $canal): self
    {
        if ($this->canals->removeElement($canal)) {
            // set the owning side to null (unless already changed)
            if ($canal->getUsuarioRegistro() === $this) {
                $canal->setUsuarioRegistro(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Recordatorio[]
     */
    public function getRecordatorios(): Collection
    {
        return $this->recordatorios;
    }

    public function addRecordatorio(Recordatorio $recordatorio): self
    {
        if (!$this->recordatorios->contains($recordatorio)) {
            $this->recordatorios[] = $recordatorio;
            $recordatorio->setUsuarioRegistro($this);
        }

        return $this;
    }

    public function removeRecordatorio(Recordatorio $recordatorio): self
    {
        if ($this->recordatorios->removeElement($recordatorio)) {
            // set the owning side to null (unless already changed)
            if ($recordatorio->getUsuarioRegistro() === $this) {
                $recordatorio->setUsuarioRegistro(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ContratoArchivos[]
     */
    public function getContratoArchivos(): Collection
    {
        return $this->contratoArchivos;
    }

    public function addContratoArchivo(ContratoArchivos $contratoArchivo): self
    {
        if (!$this->contratoArchivos->contains($contratoArchivo)) {
            $this->contratoArchivos[] = $contratoArchivo;
            $contratoArchivo->setUsuarioRegistro($this);
        }

        return $this;
    }

    public function removeContratoArchivo(ContratoArchivos $contratoArchivo): self
    {
        if ($this->contratoArchivos->removeElement($contratoArchivo)) {
            // set the owning side to null (unless already changed)
            if ($contratoArchivo->getUsuarioRegistro() === $this) {
                $contratoArchivo->setUsuarioRegistro(null);
            }
        }

        return $this;
    }

     public function getPasswordExpiracion(): ?\DateTimeInterface
    {
        return $this->passwordExpiracion;
    }

    public function setPasswordExpiracion(?\DateTimeInterface $passwordExpiracion): self
    {
        $this->passwordExpiracion = $passwordExpiracion;

        return $this;
    }

    public function isPasswordExpirado(): bool
    {
        if ($this->passwordExpiracion === null) {
            return true;
        }

        return $this->passwordExpiracion < new \DateTime();
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|PasswordHistorial[]
     */
    public function getPasswordHistorials(): \Doctrine\Common\Collections\Collection
    {
        return $this->passwordHistorials;
    }

    
}
