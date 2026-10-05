<?php

namespace App\Entity;

use App\Repository\ContratoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContratoRepository::class)]
class Contrato
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

   
    #[ORM\Column(type: "string", length: 255,nullable: true)]
    private $ciudad;

   
    #[ORM\Column(type: "string", length: 255)]
    private $comuna;

    #[ORM\ManyToOne(targetEntity: EstadoCivil::class, inversedBy: "contratos")]
    private $estadoCivil;

    #[ORM\ManyToOne(targetEntity: SituacionLaboral::class, inversedBy: "contratos")]
    private $situacionLaboral;

    #[ORM\ManyToOne(targetEntity: Escritura::class, inversedBy: "contratos")]
    private $escritura;

    #[ORM\OneToOne(targetEntity: Agenda::class,inversedBy: "contrato", cascade: ["persist", "remove"])]
    private $agenda;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $tituloContrato;

    #[ORM\Column(type: "decimal", precision: 10, scale: 0, nullable: true)]
    private $montoNivelDeuda;

    #[ORM\Column(type: "decimal", precision: 10, scale: 0, nullable: true)]
    private $MontoContrato;

    #[ORM\Column(type: "integer", nullable: true)]
    private $cuotas;

    #[ORM\Column(type: "decimal", precision: 10, scale: 0, nullable: true)]
    private $valorCuota;

    #[ORM\Column(type: "decimal", precision: 5, scale: 2, nullable: true)]
    private $interes;

    #[ORM\Column(type: "integer", nullable: true)]
    private $diaPago;

    #[ORM\OneToMany(targetEntity: ContratoRol::class, mappedBy: "contrato")]
    private $contratoRols;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $fechaCreacion;

    #[ORM\ManyToOne(targetEntity: Sucursal::class, inversedBy: "contratos")]
    private $sucursal;

    
    private $contratoTramitadores;

    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: "contratos")]
    private $tramitador;

    
    #[ORM\ManyToOne(targetEntity: Cliente::class, inversedBy: "contratos")]
    private $cliente;

  

    #[ORM\ManyToOne(targetEntity: Pais::class, inversedBy: "contratos")]
    private $pais;

   
    #[ORM\Column(type: "datetime", nullable: true)]
    private $fechaPrimerPago;

    #[ORM\ManyToOne(targetEntity: ContratoVehiculo::class, inversedBy: "contratos")]
    private $vehiculo;

    #[ORM\ManyToOne(targetEntity: ContratoVivienda::class, inversedBy: "contratos")]
    private $vivienda;

    #[ORM\ManyToOne(targetEntity: Reunion::class, inversedBy: "contratos")]
    private $reunion;

    #[ORM\Column(type: "float", nullable: true)]
    private $primeraCuota;

    #[ORM\Column(type: "date", nullable: true)]
    private $fechaPrimeraCuota;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $pdf;

    #[ORM\Column(type: "text", nullable: true)]
    private $observacion;

    #[ORM\Column(type: "boolean", nullable: true)]
    private $isAbono;

    #[ORM\OneToMany(targetEntity: Cuota::class, mappedBy: "contrato", orphanRemoval: true)]
    private $detalleCuotas;

    #[ORM\Column(type: "date", nullable: true)]
    private $fechaUltimoPago;

    #[ORM\Column(type: "boolean", nullable: true)]
    private $isFinalizado;

  
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $pdfTermino;

    #[ORM\OneToMany(targetEntity: ContratoAnexo::class, mappedBy: "contrato")]
    private $contratoAnexos;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $fechaTermino;

    #[ORM\Column(type: "integer", nullable: true)]
    private $vigencia;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $fechaDesiste;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $fechaPdfAnexo;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $ultimaFuncion;

    #[ORM\Column(type: "integer", nullable: true)]
    private $qMov;

    #[ORM\Column(type: "date", nullable: true)]
    private $fechaCompromiso;

    #[ORM\OneToMany(targetEntity: Cobranza::class, mappedBy: "contrato", orphanRemoval: true)]
    private $cobranzas;

    #[ORM\Column(type: "integer")]
    private $folio;

    #[ORM\ManyToOne(targetEntity: Comuna::class, inversedBy: "contratos")]
    private $ccomuna;

    /**
     * Plantilla de contrato elegida por el usuario al crear el contrato (ver
     * PanelAbogadoController::contrata()); ContratoController::pdf() la usa
     * para generar el PDF en vez del twig fijo.
     */
    #[ORM\ManyToOne(targetEntity: ContratoTemplate::class)]
    #[ORM\JoinColumn(nullable: true)]
    private $contratoTemplate;

    #[ORM\ManyToOne(targetEntity: Ciudad::class)]
    private $cciudad;

    #[ORM\ManyToOne(targetEntity: Region::class, inversedBy: "contratos")]
    private $cregion;

    #[ORM\Column(type: "boolean", nullable: true)]
    private $IsAnexo;

    #[ORM\Column(type: "date", nullable: true)]
    private $proximoVencimiento;

    #[ORM\Column(type: "date", nullable: true)]
    private $fechaUltimaGestion;

    #[ORM\OneToMany(targetEntity: ContratoAudios::class, mappedBy: "contrato")]
    private $contratoAudios;

    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: "contrato")]
    private $tickets;

    #[ORM\Column(type: "decimal", precision: 10, scale: 0, nullable: true)]
    private $pagoActual;

    #[ORM\Column(type: "boolean", nullable: true)]
    private $isTotal;


    #[ORM\OneToMany(targetEntity: CausaObservacion::class, mappedBy: "contrato")]
    private $causaObservacions;

    #[ORM\OneToMany(targetEntity: Recordatorio::class, mappedBy: "contrato")]
    private $recordatorios;

    #[ORM\OneToMany(targetEntity: ContratoArchivos::class, mappedBy: "contrato")]
    private $contratoArchivos;

    
    #[ORM\Column(type: "boolean", nullable: true)]
    private $isIncorporacion;

    
    public function __construct()
    {
        $this->contratoRols = new ArrayCollection();
        $this->detalleCuotas = new ArrayCollection();
        $this->contratoAnexos = new ArrayCollection();
        $this->cobranzas = new ArrayCollection();
        
        $this->contratoAudios = new ArrayCollection();
        $this->tickets = new ArrayCollection();
        $this->causaObservacions = new ArrayCollection();
        $this->recordatorios = new ArrayCollection();
        $this->contratoArchivos = new ArrayCollection();
  
        
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCiudad(): ?string
    {
        return $this->ciudad;
    }

    public function setCiudad(string $ciudad): self
    {
        $this->ciudad = $ciudad;

        return $this;
    }

    public function getComuna(): ?string
    {
        return $this->comuna;
    }

    public function setComuna(string $comuna): self
    {
        $this->comuna = $comuna;

        return $this;
    }

    public function getEstadoCivil(): ?EstadoCivil
    {
        return $this->estadoCivil;
    }

    public function setEstadoCivil(?EstadoCivil $estadoCivil): self
    {
        $this->estadoCivil = $estadoCivil;

        return $this;
    }

    public function getSituacionLaboral(): ?SituacionLaboral
    {
        return $this->situacionLaboral;
    }

    public function setSituacionLaboral(?SituacionLaboral $situacionLaboral): self
    {
        $this->situacionLaboral = $situacionLaboral;

        return $this;
    }

    public function getEscritura(): ?Escritura
    {
        return $this->escritura;
    }

    public function setEscritura(?Escritura $escritura): self
    {
        $this->escritura = $escritura;

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

    public function getTituloContrato(): ?string
    {
        return $this->tituloContrato;
    }

    public function setTituloContrato(?string $tituloContrato): self
    {
        $this->tituloContrato = $tituloContrato;

        return $this;
    }

    public function getMontoNivelDeuda(): ?string
    {
        return $this->montoNivelDeuda;
    }

    public function setMontoNivelDeuda(?string $montoNivelDeuda): self
    {
        $this->montoNivelDeuda = $montoNivelDeuda;

        return $this;
    }

    public function getMontoContrato(): ?string
    {
        return $this->MontoContrato;
    }

    public function setMontoContrato(?string $MontoContrato): self
    {
        $this->MontoContrato = $MontoContrato;

        return $this;
    }

    public function getCuotas(): ?int
    {
        return $this->cuotas;
    }

    public function setCuotas(?int $cuotas): self
    {
        $this->cuotas = $cuotas;

        return $this;
    }

    public function getValorCuota(): ?string
    {
        return $this->valorCuota;
    }

    public function setValorCuota(?string $valorCuota): self
    {
        $this->valorCuota = $valorCuota;

        return $this;
    }

    public function getInteres(): ?string
    {
        return $this->interes;
    }

    public function setInteres(?string $interes): self
    {
        $this->interes = $interes;

        return $this;
    }

    public function getDiaPago(): ?int
    {
        return $this->diaPago;
    }

    public function setDiaPago(?int $diaPago): self
    {
        $this->diaPago = $diaPago;

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
            $contratoRol->setContrato($this);
        }

        return $this;
    }

    public function removeContratoRol(ContratoRol $contratoRol): self
    {
        if ($this->contratoRols->removeElement($contratoRol)) {
            // set the owning side to null (unless already changed)
            if ($contratoRol->getContrato() === $this) {
                $contratoRol->setContrato(null);
            }
        }

        return $this;
    }
    public function __toString(){
        return $this->getFolio();
    }

    public function getFechaCreacion(): ?\DateTimeInterface
    {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion(?\DateTimeInterface $fechaCreacion): self
    {
        $this->fechaCreacion = $fechaCreacion;

        return $this;
    }

    public function getSucursal(): ?Sucursal
    {
        return $this->sucursal;
    }

    public function setSucursal(?Sucursal $sucursal): self
    {
        $this->sucursal = $sucursal;

        return $this;
    }

    public function getTramitador(): ?Usuario
    {
        return $this->tramitador;
    }

    public function setTramitador(?Usuario $tramitador): self
    {
        $this->tramitador = $tramitador;

        return $this;
    }

    public function getPais(): ?Pais
    {
        return $this->pais;
    }

    public function setPais(?Pais $pais): self
    {
        $this->pais = $pais;

        return $this;
    }

    public function getFechaPrimerPago(): ?\DateTimeInterface
    {
        return $this->fechaPrimerPago;
    }

    public function setFechaPrimerPago(?\DateTimeInterface $fechaPrimerPago): self
    {
        $this->fechaPrimerPago = $fechaPrimerPago;

        return $this;
    }

    public function getVehiculo(): ?ContratoVehiculo
    {
        return $this->vehiculo;
    }

    public function setVehiculo(?ContratoVehiculo $vehiculo): self
    {
        $this->vehiculo = $vehiculo;

        return $this;
    }

    public function getVivienda(): ?ContratoVivienda
    {
        return $this->vivienda;
    }

    public function setVivienda(?ContratoVivienda $vivienda): self
    {
        $this->vivienda = $vivienda;

        return $this;
    }

    public function getReunion(): ?Reunion
    {
        return $this->reunion;
    }

    public function setReunion(?Reunion $reunion): self
    {
        $this->reunion = $reunion;

        return $this;
    }

    public function getPrimeraCuota(): ?float
    {
        return $this->primeraCuota;
    }

    public function setPrimeraCuota(?float $primeraCuota): self
    {
        $this->primeraCuota = $primeraCuota;

        return $this;
    }

    public function getFechaPrimeraCuota(): ?\DateTimeInterface
    {
        return $this->fechaPrimeraCuota;
    }

    public function setFechaPrimeraCuota(?\DateTimeInterface $fechaPrimeraCuota): self
    {
        $this->fechaPrimeraCuota = $fechaPrimeraCuota;

        return $this;
    }

    public function getPdf(): ?string
    {
        return $this->pdf;
    }

    public function setPdf(?string $pdf): self
    {
        $this->pdf = $pdf;

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

    public function getIsAbono(): ?bool
    {
        return $this->isAbono;
    }

    public function setIsAbono(?bool $isAbono): self
    {
        $this->isAbono = $isAbono;

        return $this;
    }

    /**
     * @return Collection|Cuota[]
     */
    public function getDetalleCuotas(): Collection
    {
        return $this->detalleCuotas;
    }

    public function addDetalleCuota(Cuota $detalleCuota): self
    {
        if (!$this->detalleCuotas->contains($detalleCuota)) {
            $this->detalleCuotas[] = $detalleCuota;
            $detalleCuota->setContrato($this);
        }

        return $this;
    }

    public function removeDetalleCuota(Cuota $detalleCuota): self
    {
        if ($this->detalleCuotas->removeElement($detalleCuota)) {
            // set the owning side to null (unless already changed)
            if ($detalleCuota->getContrato() === $this) {
                $detalleCuota->setContrato(null);
            }
        }

        return $this;
    }

    public function getFechaUltimoPago(): ?\DateTimeInterface
    {
        return $this->fechaUltimoPago;
    }

    public function setFechaUltimoPago(?\DateTimeInterface $fechaUltimoPago): self
    {
        $this->fechaUltimoPago = $fechaUltimoPago;

        return $this;
    }

    public function getIsFinalizado(): ?bool
    {
        return $this->isFinalizado;
    }

    public function setIsFinalizado(?bool $isFinalizado): self
    {
        $this->isFinalizado = $isFinalizado;

        return $this;
    }

    public function getPdfTermino(): ?string
    {
        return $this->pdfTermino;
    }

    public function setPdfTermino(?string $pdfTermino): self
    {
        $this->pdfTermino = $pdfTermino;

        return $this;
    }

    /**
     * @return Collection|ContratoAnexo[]
     */
    public function getContratoAnexos(): Collection
    {
        return $this->contratoAnexos;
    }

    public function addContratoAnexo(ContratoAnexo $contratoAnexo): self
    {
        if (!$this->contratoAnexos->contains($contratoAnexo)) {
            $this->contratoAnexos[] = $contratoAnexo;
            $contratoAnexo->setContrato($this);
        }

        return $this;
    }

    public function removeContratoAnexo(ContratoAnexo $contratoAnexo): self
    {
        if ($this->contratoAnexos->removeElement($contratoAnexo)) {
            // set the owning side to null (unless already changed)
            if ($contratoAnexo->getContrato() === $this) {
                $contratoAnexo->setContrato(null);
            }
        }

        return $this;
    }

    public function getFechaTermino(): ?\DateTimeInterface
    {
        return $this->fechaTermino;
    }

    public function setFechaTermino(?\DateTimeInterface $fechaTermino): self
    {
        $this->fechaTermino = $fechaTermino;

        return $this;
    }

    public function getVigencia(): ?int
    {
        return $this->vigencia;
    }

    public function setVigencia(?int $vigencia): self
    {
        $this->vigencia = $vigencia;

        return $this;
    }

    public function getFechaDesiste(): ?\DateTimeInterface
    {
        return $this->fechaDesiste;
    }

    public function setFechaDesiste(?\DateTimeInterface $fechaDesiste): self
    {
        $this->fechaDesiste = $fechaDesiste;

        return $this;
    }

    public function getFechaPdfAnexo(): ?\DateTimeInterface
    {
        return $this->fechaPdfAnexo;
    }

    public function setFechaPdfAnexo(?\DateTimeInterface $fechaPdfAnexo): self
    {
        $this->fechaPdfAnexo = $fechaPdfAnexo;

        return $this;
    }

    public function getUltimaFuncion(): ?string
    {
        return $this->ultimaFuncion;
    }

    public function setUltimaFuncion(?string $ultimaFuncion): self
    {
        $this->ultimaFuncion = $ultimaFuncion;

        return $this;
    }

    public function getQMov(): ?int
    {
        return $this->qMov;
    }

    public function setQMov(?int $qMov): self
    {
        $this->qMov = $qMov;

        return $this;
    }

    public function getFechaCompromiso(): ?\DateTimeInterface
    {
        return $this->fechaCompromiso;
    }

    public function setFechaCompromiso(?\DateTimeInterface $fechaCompromiso): self
    {
        $this->fechaCompromiso = $fechaCompromiso;

        return $this;
    }

    /**
     * @return Collection|Cobranza[]
     */
    public function getCobranzas(): Collection
    {
        return $this->cobranzas;
    }

    public function addCobranza(Cobranza $cobranza): self
    {
        if (!$this->cobranzas->contains($cobranza)) {
            $this->cobranzas[] = $cobranza;
            $cobranza->setContrato($this);
        }

        return $this;
    }

    public function removeCobranza(Cobranza $cobranza): self
    {
        if ($this->cobranzas->removeElement($cobranza)) {
            // set the owning side to null (unless already changed)
            if ($cobranza->getContrato() === $this) {
                $cobranza->setContrato(null);
            }
        }

        return $this;
    }

    public function getFolio(): ?int
    {
        return $this->folio;
    }

    public function setFolio(?int $folio): self
    {
        $this->folio = $folio;

        return $this;
    }


    public function getCcomuna(): ?Comuna
    {
        return $this->ccomuna;
    }

    public function setCcomuna(?Comuna $ccomuna): self
    {
        $this->ccomuna = $ccomuna;

        return $this;
    }

    public function getContratoTemplate(): ?ContratoTemplate
    {
        return $this->contratoTemplate;
    }

    public function setContratoTemplate(?ContratoTemplate $contratoTemplate): self
    {
        $this->contratoTemplate = $contratoTemplate;

        return $this;
    }

    public function getCciudad(): ?Ciudad
    {
        return $this->cciudad;
    }

    public function setCciudad(?Ciudad $cciudad): self
    {
        $this->cciudad = $cciudad;

        return $this;
    }

    public function getCregion(): ?Region
    {
        return $this->cregion;
    }

    public function setCregion(?Region $cregion): self
    {
        $this->cregion = $cregion;

        return $this;
    }

    public function getIsAnexo(): ?bool
    {
        return $this->IsAnexo;
    }

    public function setIsAnexo(?bool $IsAnexo): self
    {
        $this->IsAnexo = $IsAnexo;

        return $this;
    }

    public function getProximoVencimiento(): ?\DateTimeInterface
    {
        return $this->proximoVencimiento;
    }

    public function setProximoVencimiento(?\DateTimeInterface $proximoVencimiento): self
    {
        $this->proximoVencimiento = $proximoVencimiento;

        return $this;
    }

    public function getFechaUltimaGestion(): ?\DateTimeInterface
    {
        return $this->fechaUltimaGestion;
    }

    public function setFechaUltimaGestion(?\DateTimeInterface $fechaUltimaGestion): self
    {
        $this->fechaUltimaGestion = $fechaUltimaGestion;

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
            $contratoAudio->setContrato($this);
        }

        return $this;
    }

    public function removeContratoAudio(ContratoAudios $contratoAudio): self
    {
        if ($this->contratoAudios->removeElement($contratoAudio)) {
            // set the owning side to null (unless already changed)
            if ($contratoAudio->getContrato() === $this) {
                $contratoAudio->setContrato(null);
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
            $ticket->setContrato($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getContrato() === $this) {
                $ticket->setContrato(null);
            }
        }

        return $this;
    }

    public function getPagoActual(): ?string
    {
        return $this->pagoActual;
    }

    public function setPagoActual(?string $pagoActual): self
    {
        $this->pagoActual = $pagoActual;

        return $this;
    }

    public function getIsTotal(): ?bool
    {
        return $this->isTotal;
    }

    public function setIsTotal(?bool $isTotal): self
    {
        $this->isTotal = $isTotal;

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
            $causaObservacion->setContrato($this);
        }

        return $this;
    }

    public function removeCausaObservacion(CausaObservacion $causaObservacion): self
    {
        if ($this->causaObservacions->removeElement($causaObservacion)) {
            // set the owning side to null (unless already changed)
            if ($causaObservacion->getContrato() === $this) {
                $causaObservacion->setContrato(null);
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
            $recordatorio->setContrato($this);
        }

        return $this;
    }

    public function removeRecordatorio(Recordatorio $recordatorio): self
    {
        if ($this->recordatorios->removeElement($recordatorio)) {
            // set the owning side to null (unless already changed)
            if ($recordatorio->getContrato() === $this) {
                $recordatorio->setContrato(null);
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
            $contratoArchivo->setContrato($this);
        }

        return $this;
    }

    public function removeContratoArchivo(ContratoArchivos $contratoArchivo): self
    {
        if ($this->contratoArchivos->removeElement($contratoArchivo)) {
            // set the owning side to null (unless already changed)
            if ($contratoArchivo->getContrato() === $this) {
                $contratoArchivo->setContrato(null);
            }
        }

        return $this;
    }


    public function getIsIncorporacion(): ?bool
    {
        return $this->isIncorporacion;
    }

    public function setIsIncorporacion(?bool $isIncorporacion): self
    {
        $this->isIncorporacion = $isIncorporacion;

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

    /**
     * Materias que abarca el contrato, derivadas de sus causas (vía la agenda).
     * Un contrato puede abarcar varias materias.
     *
     * @return Materia[]
     */
    public function getMaterias(): array
    {
        if ($this->agenda === null) {
            return [];
        }

        $materias = [];
        foreach ($this->agenda->getCausas() as $causa) {
            $materia = $causa->getMateria();
            if ($materia !== null) {
                $materias[$materia->getId()] = $materia;
            }
        }

        return array_values($materias);
    }
}
