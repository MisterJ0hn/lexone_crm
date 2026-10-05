<?php

namespace App\Entity;

use App\Repository\CorreoBienvenidaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Correo de bienvenida que se envía al cliente al crear un contrato. Uno por
 * (empresa, tipo de cliente). El contenido es un HTML con variables tipo
 * {{cliente_nombre}} (ver ContratoTemplateRenderer::catalogo()).
 */
#[ORM\Entity(repositoryClass: CorreoBienvenidaRepository::class)]
#[ORM\UniqueConstraint(name: "uniq_correo_bienvenida_empresa_tipo", columns: ["empresa_id", "tipo_cliente_id"])]
class CorreoBienvenida
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\ManyToOne(targetEntity: Empresa::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $empresa;

    #[ORM\ManyToOne(targetEntity: TipoCliente::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $tipoCliente;

    #[ORM\Column(type: "string", length: 150, nullable: true)]
    private $remitenteNombre;

    #[ORM\Column(type: "string", length: 255)]
    private $remitenteCorreo;

    #[ORM\Column(type: "string", length: 255)]
    private $asunto;

    /** HTML del correo, con variables {{...}}. */
    #[ORM\Column(type: "text")]
    private $contenido;

    /**
     * Nombre del archivo de imagen (en var/correo_bienvenida/) que se incrusta en
     * el correo y se referencia en el HTML con {{imagen}}.
     */
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $imagen;

    #[ORM\Column(type: "boolean")]
    private $activo = true;

    #[ORM\Column(type: "datetime", nullable: true)]
    private $fechaModificacion;

    public function getId(): ?int { return $this->id; }

    public function getEmpresa(): ?Empresa { return $this->empresa; }
    public function setEmpresa(?Empresa $empresa): self { $this->empresa = $empresa; return $this; }

    public function getTipoCliente(): ?TipoCliente { return $this->tipoCliente; }
    public function setTipoCliente(?TipoCliente $tipoCliente): self { $this->tipoCliente = $tipoCliente; return $this; }

    public function getRemitenteNombre(): ?string { return $this->remitenteNombre; }
    public function setRemitenteNombre(?string $remitenteNombre): self { $this->remitenteNombre = $remitenteNombre; return $this; }

    public function getRemitenteCorreo(): ?string { return $this->remitenteCorreo; }
    public function setRemitenteCorreo(?string $remitenteCorreo): self { $this->remitenteCorreo = $remitenteCorreo; return $this; }

    public function getAsunto(): ?string { return $this->asunto; }
    public function setAsunto(?string $asunto): self { $this->asunto = $asunto; return $this; }

    public function getContenido(): ?string { return $this->contenido; }
    public function setContenido(?string $contenido): self { $this->contenido = $contenido; return $this; }

    public function getImagen(): ?string { return $this->imagen; }
    public function setImagen(?string $imagen): self { $this->imagen = $imagen; return $this; }

    public function isActivo(): ?bool { return $this->activo; }
    public function setActivo(bool $activo): self { $this->activo = $activo; return $this; }

    public function getFechaModificacion(): ?\DateTimeInterface { return $this->fechaModificacion; }
    public function setFechaModificacion(?\DateTimeInterface $fechaModificacion): self { $this->fechaModificacion = $fechaModificacion; return $this; }
}
