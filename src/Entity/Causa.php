<?php

namespace App\Entity;

use App\Repository\CausaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CausaRepository::class)
 */
class Causa
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    
    /**
     * Materia (área de práctica: Civil, Laboral, Familia…) de esta causa.
     * La materia pertenece a la Empresa; un contrato puede abarcar varias materias,
     * una por causa.
     *
     * @ORM\ManyToOne(targetEntity=Materia::class, inversedBy="causas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $materia;

    /**
     * Servicio/estrategia jurídica concreta dentro de la materia. Opcional: se
     * puede elegir después de crear la causa. Si está seteada, su materia debe
     * coincidir con $materia.
     *
     * @ORM\ManyToOne(targetEntity=MateriaEstrategia::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $materiaEstrategia;

    /**
     * @ORM\ManyToOne(targetEntity=JuzgadoCuenta::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $juzgadoCuenta;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $id_causa;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $causaNombre;

    /**
     * @ORM\ManyToOne(targetEntity=Agenda::class, inversedBy="causas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $agenda;

    /**
     * @ORM\OneToMany(targetEntity=LineaTiempoTerminada::class, mappedBy="causa")
     */
    private $lineaTiempoTerminadas;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $estado;

    /**
     * @ORM\ManyToOne(targetEntity=ContratoAnexo::class, inversedBy="causas")
     */
    private $anexo;

    /**
     * @ORM\OneToMany(targetEntity=CausaObservacion::class, mappedBy="causa")
     */
    private $causaObservacions;

     /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaUltimoIngreso;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $causaFinalizada;
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaFinalizado;

    /**
     * @ORM\OneToMany(targetEntity=DetalleCuaderno::class, mappedBy="causa", orphanRemoval=true)
     */
    private $detalleCuadernos;

    /**
     * @ORM\OneToMany(targetEntity=EstrategiaJuridicaReporteArchivos::class, mappedBy="causa")
     */
    private $estrategiaJuridicaReporteArchivos;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $letra;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rol;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $anio;

    /**
     * @ORM\ManyToOne(targetEntity=Corte::class, inversedBy="causas")
     */
    private $corte;

    /**
     * @ORM\ManyToOne(targetEntity=Juzgado::class)
     */
    private $juzgado;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $etapaPendiente;

    /**
     * Cliente propio de esta causa, para Agendas de tipo Convenio/Empresa (donde el
     * Contrato agrupa varios clientes, cada uno con sus propias causas). Para
     * Agendas de tipo Persona queda en null: el único cliente del caso sigue
     * viviendo en Contrato::$cliente.
     *
     * @ORM\ManyToOne(targetEntity=Cliente::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $cliente;

    public function __construct()
    {
        $this->lineaTiempoTerminadas = new ArrayCollection();
        $this->causaObservacions = new ArrayCollection();
        $this->detalleCuadernos = new ArrayCollection();
        $this->estrategiaJuridicaReporteArchivos = new ArrayCollection();
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

    public function getMateriaEstrategia(): ?MateriaEstrategia
    {
        return $this->materiaEstrategia;
    }

    public function setMateriaEstrategia(?MateriaEstrategia $materiaEstrategia): self
    {
        $this->materiaEstrategia = $materiaEstrategia;

        return $this;
    }

    public function getJuzgadoCuenta(): ?JuzgadoCuenta
    {
        return $this->juzgadoCuenta;
    }

    public function setJuzgadoCuenta(?JuzgadoCuenta $juzgadoCuenta): self
    {
        $this->juzgadoCuenta = $juzgadoCuenta;

        return $this;
    }

    public function getIdCausa(): ?string
    {
        return $this->id_causa;
    }

    public function setIdCausa(?string $id_causa): self
    {
        $this->id_causa = $id_causa;

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
     * @return Collection<int, LineaTiempoTerminada>
     */
    public function getLineaTiempoTerminadas(): Collection
    {
        return $this->lineaTiempoTerminadas;
    }

    public function addLineaTiempoTerminada(LineaTiempoTerminada $lineaTiempoTerminada): self
    {
        if (!$this->lineaTiempoTerminadas->contains($lineaTiempoTerminada)) {
            $this->lineaTiempoTerminadas[] = $lineaTiempoTerminada;
            $lineaTiempoTerminada->setCausa($this);
        }

        return $this;
    }

    public function removeLineaTiempoTerminada(LineaTiempoTerminada $lineaTiempoTerminada): self
    {
        if ($this->lineaTiempoTerminadas->removeElement($lineaTiempoTerminada)) {
            // set the owning side to null (unless already changed)
            if ($lineaTiempoTerminada->getCausa() === $this) {
                $lineaTiempoTerminada->setCausa(null);
            }
        }

        return $this;
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

    /**
     * @return Collection<int, EstrategiaJuridicaReporteArchivos>
     */
    public function getEstrategiaJuridicaReporteArchivos(): Collection
    {
        return $this->estrategiaJuridicaReporteArchivos;
    }

    public function addEstrategiaJuridicaReporteArchivo(EstrategiaJuridicaReporteArchivos $estrategiaJuridicaReporteArchivo): self
    {
        if (!$this->estrategiaJuridicaReporteArchivos->contains($estrategiaJuridicaReporteArchivo)) {
            $this->estrategiaJuridicaReporteArchivos[] = $estrategiaJuridicaReporteArchivo;
            $estrategiaJuridicaReporteArchivo->setCausa($this);
        }

        return $this;
    }

    public function removeEstrategiaJuridicaReporteArchivo(EstrategiaJuridicaReporteArchivos $estrategiaJuridicaReporteArchivo): self
    {
        if ($this->estrategiaJuridicaReporteArchivos->removeElement($estrategiaJuridicaReporteArchivo)) {
            // set the owning side to null (unless already changed)
            if ($estrategiaJuridicaReporteArchivo->getCausa() === $this) {
                $estrategiaJuridicaReporteArchivo->setCausa(null);
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

   

    public function getEtapaPendiente(): ?string
    {
        return $this->etapaPendiente;
    }

    public function setEtapaPendiente(?string $etapaPendiente): self
    {
        $this->etapaPendiente = $etapaPendiente;

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
