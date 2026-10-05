<?php

namespace App\Entity;

use App\Repository\CausaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CausaRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class Causa
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    
    /**
     * Materia (área de práctica: Civil, Laboral, Familia…) de esta causa.
     * La materia pertenece a la Empresa; un contrato puede abarcar varias materias,
     * una por causa.
     */
    #[ORM\ManyToOne(targetEntity: Materia::class, inversedBy: "causas")]
    #[ORM\JoinColumn(nullable: false)]
    private $materia;

    /**
     * Servicio concreto dentro de la materia. Opcional: se puede elegir después
     * de crear la causa. Si está seteado, su materia debe coincidir con $materia.
     */
    #[ORM\ManyToOne(targetEntity: Servicio::class)]
    #[ORM\JoinColumn(nullable: true)]
    private $servicio;

    #[ORM\Column(type: "string", length: 255)]
    private $idCausa;

    #[ORM\Column(type: "string", length: 255)]
    private $causaNombre;

    #[ORM\ManyToOne(targetEntity: Agenda::class, inversedBy: "causas")]
    #[ORM\JoinColumn(nullable: false)]
    private $agenda;

    /**
     * Estado procesal de la causa (ver EstadoProcesal). Parte con "Bienvenida".
     */
    #[ORM\OneToMany(targetEntity: EstadoProcesal::class, mappedBy: "causa", cascade: ["persist"])]
    #[ORM\OrderBy(["id" => "ASC"])]
    private $estadosProcesales;

    #[ORM\Column(type: "boolean", nullable: true)]
    private $estado;

    #[ORM\ManyToOne(targetEntity: ContratoAnexo::class, inversedBy: "causas")]
    private $anexo;

    #[ORM\OneToMany(targetEntity: CausaObservacion::class, mappedBy: "causa")]
    private $causaObservacions;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $fechaUltimoIngreso;

    #[ORM\Column(type: "boolean", nullable: true)]
    private $causaFinalizada;
    #[ORM\Column(type: "datetime", nullable: true)]
    private $fechaFinalizado;

    #[ORM\OneToMany(targetEntity: DetalleCuaderno::class, mappedBy: "causa", orphanRemoval: true)]
    private $detalleCuadernos;


    #[ORM\Column(type: "string", length: 10, nullable: true)]
    private $letra;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $rol;

    #[ORM\Column(type: "integer", nullable: true)]
    private $anio;

    #[ORM\ManyToOne(targetEntity: Corte::class, inversedBy: "causas")]
    private $corte;

    #[ORM\ManyToOne(targetEntity: Juzgado::class)]
    private $juzgado;


    /**
     * Cliente propio de esta causa, para Agendas de tipo Convenio/Empresa (donde el
     * Contrato agrupa varios clientes, cada uno con sus propias causas). Para
     * Agendas de tipo Persona queda en null: el único cliente del caso sigue
     * viviendo en Contrato::$cliente.
     */
    #[ORM\ManyToOne(targetEntity: Cliente::class)]
    #[ORM\JoinColumn(nullable: true)]
    private $cliente;

    public function __construct()
    {
        $this->estadosProcesales = new ArrayCollection();
        $this->causaObservacions = new ArrayCollection();
        $this->detalleCuadernos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMateria(): ?Materia
    {
        return $this->materia;
    }

    public function setMateria(?Materia $materia): self
    {
        $this->materia = $materia;

        return $this;
    }

    public function getServicio(): ?Servicio
    {
        return $this->servicio;
    }

    public function setServicio(?Servicio $servicio): self
    {
        $this->servicio = $servicio;

        return $this;
    }

    public function getIdCausa(): ?string
    {
        return $this->idCausa;
    }

    public function setIdCausa(string $idCausa): self
    {
        $this->idCausa = $idCausa;

        return $this;
    }
   
    public function getCausaNombre(): ?string
    {
        return $this->causaNombre;
    }

    public function setCausaNombre(string $causaNombre): self
    {
        $this->causaNombre = $causaNombre;

        return $this;
    }

    public function getAgenda(): ?Agenda
    {
        return $this->agenda;
    }

    public function setAgenda(?Agenda $agenda): self
    {
        $this->agenda = $agenda;

        return $this;
    }

    /**
     * @return Collection<int, EstadoProcesal>
     */
    public function getEstadosProcesales(): Collection
    {
        return $this->estadosProcesales;
    }

    public function addEstadoProcesal(EstadoProcesal $estadoProcesal): self
    {
        if (!$this->estadosProcesales->contains($estadoProcesal)) {
            $this->estadosProcesales[] = $estadoProcesal;
            $estadoProcesal->setCausa($this);
        }

        return $this;
    }

    /**
     * Toda causa nueva nace con el ítem "Bienvenida" ya completado (verde).
     */
    #[ORM\PrePersist()]
    public function crearEstadoBienvenida(): void
    {
        $empresa = $this->agenda?->getEmpresa();
        if ($empresa === null || !$this->estadosProcesales->isEmpty()) {
            return;
        }
        $bienvenida = (new EstadoProcesal())
            ->setEmpresa($empresa)
            ->setNombre(EstadoProcesal::BIENVENIDA)
            ->setCompletado(true);
        $this->addEstadoProcesal($bienvenida);
    }

    public function getEstado(): ?bool
    {
        return $this->estado;
    }

    public function setEstado(?bool $estado): self
    {
        $this->estado = $estado;

        return $this;
    }

    public function getAnexo(): ?ContratoAnexo
    {
        return $this->anexo;
    }

    public function setAnexo(?ContratoAnexo $anexo): self
    {
        $this->anexo = $anexo;

        return $this;
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
            $causaObservacion->setCausa($this);
        }

        return $this;
    }

    public function removeCausaObservacion(CausaObservacion $causaObservacion): self
    {
        if ($this->causaObservacions->removeElement($causaObservacion)) {
            // set the owning side to null (unless already changed)
            if ($causaObservacion->getCausa() === $this) {
                $causaObservacion->setCausa(null);
            }
        }

        return $this;
    }

    public function getFechaUltimoIngreso(): ?\DateTimeInterface
    {
        return $this->fechaUltimoIngreso;
    }

    public function setFechaUltimoIngreso(?\DateTimeInterface $fechaUltimoIngreso): self
    {
        $this->fechaUltimoIngreso = $fechaUltimoIngreso;

        return $this;
    }

    public function getCausaFinalizada(): ?bool
    {
        return $this->causaFinalizada;
    }

    public function setCausaFinalizada(?bool $causaFinalizada): self
    {
        $this->causaFinalizada = $causaFinalizada;

        return $this;
    }
    public function getFechaFinalizado(): ?\DateTimeInterface
    {
        return $this->fechaFinalizado;
    }

    public function setFechaFinalizado(?\DateTimeInterface $fechaFinalizado): self
    {
        $this->fechaFinalizado = $fechaFinalizado;

        return $this;
    }

    /**
     * @return Collection<int, DetalleCuaderno>
     */
    public function getDetalleCuadernos(): Collection
    {
        return $this->detalleCuadernos;
    }

    public function addDetalleCuaderno(DetalleCuaderno $detalleCuaderno): self
    {
        if (!$this->detalleCuadernos->contains($detalleCuaderno)) {
            $this->detalleCuadernos[] = $detalleCuaderno;
            $detalleCuaderno->setCausa($this);
        }

        return $this;
    }

    public function removeDetalleCuaderno(DetalleCuaderno $detalleCuaderno): self
    {
        if ($this->detalleCuadernos->removeElement($detalleCuaderno)) {
            // set the owning side to null (unless already changed)
            if ($detalleCuaderno->getCausa() === $this) {
                $detalleCuaderno->setCausa(null);
            }
        }

        return $this;
    }

    public function getLetra(): ?string
    {
        return $this->letra;
    }

    public function setLetra(?string $letra): self
    {
        $this->letra = $letra;

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

    public function getAnio(): ?int
    {
        return $this->anio;
    }

    public function setAnio(?int $anio): self
    {
        $this->anio = $anio;

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

    public function getJuzgado(): ?Juzgado
    {
        return $this->juzgado;
    }

    public function setJuzgado(?Juzgado $juzgado): self
    {
        $this->juzgado = $juzgado;

        return $this;
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
}
