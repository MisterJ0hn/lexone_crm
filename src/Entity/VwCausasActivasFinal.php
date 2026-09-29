<?php

namespace App\Entity;
use App\Repository\VwCausasActivasFinalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VwCausasActivasFinalRepository::class)
 * @ORM\Table(name="vw_causas_activas_final")
 */
class VwCausasActivasFinal{
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cuentaId;
    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */ 
    private $compañia;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $contratoId;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $agendaId;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $folio;	
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaCto;
    /**
     *  @ORM\Column(type="string", length=255,nullable=true)
     */
    private $cliente;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tramitadorId;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $tramitador;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $abogadoId;
    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $cerrador;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $IdCausa;
    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $caratulado;
    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $folioActivo;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $vigenciaActivo;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $mesesActivo;
    /**
     * @ORM\Column(type="datetime", nullable=true)
*/
    private $fechaCreacionAnexo;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $vigenciaAnexo;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $morosos;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $VipMayor2MM;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $VipReferidos;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $VipUnaCuota;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $rol;
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $fechaRegistroObservacion;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $activo;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $moroso;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $vip;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tieneRol;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $causaFinalizada;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cuentaNombre;

    public function getCuentaId()
    {
        return $this->cuentaId;
    }

    public function setCuentaId($cuentaId): self
    {
        $this->cuentaId = $cuentaId;
        return $this;
    }

    public function getCompañia()
    {
        return $this->compañia;
    }

    public function setCompañia($compañia): self
    {
        $this->compañia = $compañia;
        return $this;
    }

    public function getContratoId()
    {
        return $this->contratoId;
    }

    public function setContratoId($contratoId): self
    {
        $this->contratoId = $contratoId;
        return $this;
    }

    public function getAgendaId()
    {
        return $this->agendaId;
    }

    public function setAgendaId($agendaId): self
    {
        $this->agendaId = $agendaId;
        return $this;
    }

    public function getFolio()
    {
        return $this->folio;
    }

    public function setFolio($folio): self
    {
        $this->folio = $folio;
        return $this;
    }

    public function getFechaCto()
    {
        return $this->fechaCto;
    }

    public function setFechaCto($fechaCto): self
    {
        $this->fechaCto = $fechaCto;
        return $this;
    }

    public function getCliente()
    {
        return $this->cliente;
    }

    public function setCliente($cliente): self
    {
        $this->cliente = $cliente;
        return $this;
    }

    public function getTramitadorId()
    {
        return $this->tramitadorId;
    }

    public function setTramitadorId($tramitadorId): self
    {
        $this->tramitadorId = $tramitadorId;
        return $this;
    }

    public function getTramitador()
    {
        return $this->tramitador;
    }

    public function setTramitador($tramitador): self
    {
        $this->tramitador = $tramitador;
        return $this;
    }

    public function getAbogadoId()
    {
        return $this->abogadoId;
    }

    public function setAbogadoId($abogadoId): self
    {
        $this->abogadoId = $abogadoId;
        return $this;
    }

    public function getCerrador()
    {
        return $this->cerrador;
    }

    public function setCerrador($cerrador): self
    {
        $this->cerrador = $cerrador;
        return $this;
    }

    public function getIdCausa()
    {
        return $this->IdCausa;
    }

    public function setIdCausa($IdCausa): self
    {
        $this->IdCausa = $IdCausa;
        return $this;
    }

    public function getCaratulado()
    {
        return $this->caratulado;
    }

    public function setCaratulado($caratulado): self
    {
        $this->caratulado = $caratulado;
        return $this;
    }

    public function getFolioActivo()
    {
        return $this->folioActivo;
    }

    public function setFolioActivo($folioActivo): self
    {
        $this->folioActivo = $folioActivo;
        return $this;
    }

    public function getVigenciaActivo()
    {
        return $this->vigenciaActivo;
    }

    public function setVigenciaActivo($vigenciaActivo): self
    {
        $this->vigenciaActivo = $vigenciaActivo;
        return $this;
    }

    public function getMesesActivo()
    {
        return $this->mesesActivo;
    }

    public function setMesesActivo($mesesActivo): self
    {
        $this->mesesActivo = $mesesActivo;
        return $this;
    }

    public function getFechaCreacionAnexo()
    {
        return $this->fechaCreacionAnexo;
    }

    public function setFechaCreacionAnexo($fechaCreacionAnexo): self
    {
        $this->fechaCreacionAnexo = $fechaCreacionAnexo;
        return $this;
    }

    public function getVigenciaAnexo()
    {
        return $this->vigenciaAnexo;
    }

    public function setVigenciaAnexo($vigenciaAnexo): self
    {
        $this->vigenciaAnexo = $vigenciaAnexo;
        return $this;
    }

    public function getMorosos()
    {
        return $this->morosos;
    }

    public function setMorosos($morosos): self
    {
        $this->morosos = $morosos;
        return $this;
    }

    public function getVipMayor2MM()
    {
        return $this->VipMayor2MM;
    }

    public function setVipMayor2MM($VipMayor2MM): self
    {
        $this->VipMayor2MM = $VipMayor2MM;
        return $this;
    }

    public function getVipReferidos()
    {
        return $this->VipReferidos;
    }

    public function setVipReferidos($VipReferidos): self
    {
        $this->VipReferidos = $VipReferidos;
        return $this;
    }

    public function getVipUnaCuota()
    {
        return $this->VipUnaCuota;
    }

    public function setVipUnaCuota($VipUnaCuota): self
    {
        $this->VipUnaCuota = $VipUnaCuota;
        return $this;
    }

    public function getRol()
    {
        return $this->rol;
    }

    public function setRol($rol): self
    {
        $this->rol = $rol;
        return $this;
    }

    public function getFechaRegistroObservacion()
    {
        return $this->fechaRegistroObservacion;
    }

    public function setFechaRegistroObservacion($fechaRegistroObservacion): self
    {
        $this->fechaRegistroObservacion = $fechaRegistroObservacion;
        return $this;
    }

    public function getActivo()
    {
        return $this->activo;
    }

    public function setActivo($activo): self
    {
        $this->activo = $activo;
        return $this;
    }

    public function getMoroso()
    {
        return $this->moroso;
    }

    public function setMoroso($moroso): self
    {
        $this->moroso = $moroso;
        return $this;
    }

    public function getVip()
    {
        return $this->vip;
    }

    public function setVip($vip): self
    {
        $this->vip = $vip;
        return $this;
    }

    public function getTieneRol()
    {
        return $this->tieneRol;
    }

    public function setTieneRol($tieneRol): self
    {
        $this->tieneRol = $tieneRol;
        return $this;
    }

    public function getCausaFinalizada()
    {
        return $this->causaFinalizada;
    }

    public function setCausaFinalizada($causaFinalizada): self
    {
        $this->causaFinalizada = $causaFinalizada;
    }
     public function getCuentaNombre()
    {
        return $this->cuentaNombre;
    }

    public function setCuentaNombre($cuentaNombre): self
    {
        $this->cuentaNombre = $cuentaNombre;
    }


}