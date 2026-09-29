<?php

namespace App\Entity;

use App\Repository\ClienteRepository;
use App\Security\Cifrado;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ClienteRepository::class)
 * @ORM\Table(name="cliente")
 * @ORM\HasLifecycleCallbacks()
 */
class Cliente
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * rut, telefono, telefonoRecado, correo, direccion y claveUnica se guardan cifrados
     * (AES-256-GCM, ver App\Doctrine\EncryptedStringType / App\Security\Cifrado).
     * Los getters/setters siguen trabajando en texto plano de forma transparente.
     *
     * @ORM\Column(type="encrypted_string", length=255)
     */
    private $rut;

    /**
     * Hash determinístico (HMAC-SHA256) de rut, usado únicamente para búsquedas
     * exactas (WHERE rutHash = ...), ya que la columna rut queda cifrada con IV
     * aleatorio y no es comparable directamente.
     *
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $rutHash;

    /**
     * @ORM\Column(type="encrypted_string", length=255)
     */
    private $correo;


    /**
     * Hash determinístico (HMAC-SHA256) del correo, normalizado a minúsculas.
     * Como la columna correo queda cifrada, la búsqueda por correo pasa a ser
     * por igualdad exacta (ya no admite coincidencia parcial tipo LIKE).
     *
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $correoHash;

    /**
     * @ORM\Column(type="encrypted_string", length=255)
     */
    private $telefono;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $telefonoHash;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $sexo;
    /**
     * @ORM\Column(type="encrypted_string", length=255, nullable=true)
    */
    private $claveUnica;

    /**
     * @ORM\OneToMany(targetEntity=Contrato::class, mappedBy="cliente")
    */
    private $contratos;

    /**
     * @ORM\OneToMany(targetEntity=ClienteHistorial::class, mappedBy="cliente")
    */
    private $clienteHistorials;
    
    /**
     * @ORM\Column(type="encrypted_string", length=255, nullable=true)
    */
    private $direccion;
    /**
     * @ORM\Column(type="encrypted_string", length=255, nullable=true)
    */
    private $telefonoRecado;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
    */
    private $telefonoRecadoHash;

    /**
     * Solo se usa para clientes de tipo Convenio/Empresa (ver Agenda::$tipoCliente).
     * Para Persona la nacionalidad del cliente se maneja vía Contrato::$pais.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nacionalidad;

    /**
     * @ORM\ManyToOne(targetEntity=Region::class)
     */
    private $region;

    /**
     * @ORM\ManyToOne(targetEntity=Ciudad::class)
     */
    private $ciudad;

    /**
     * @ORM\ManyToOne(targetEntity=Comuna::class)
     */
    private $comuna;

    /**
     * @ORM\ManyToOne(targetEntity=EstadoCivil::class)
     */
    private $estadoCivil;

    /**
     * @ORM\ManyToOne(targetEntity=Reunion::class)
     */
    private $reunion;


    public function __construct()
    {
        $this->contratos = new ArrayCollection();
        $this->clienteHistorials = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(?string $nombre): self
    {
        $this->nombre = $nombre;

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

    public function getCorreo(): ?string
    {
        return $this->correo;
    }

    public function setCorreo(?string $correo): self
    {
        $this->correo = $correo;

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

    public function getSexo(): ?string
    {
        return $this->sexo;
    }

    public function setSexo(?string $sexo): self
    {
        $this->sexo = $sexo;

        return $this;
    }

    public function getClaveUnica(): ?string
    {
        return $this->claveUnica;
    }

    public function setClaveUnica(?string $claveUnica): self
    {
        $this->claveUnica = $claveUnica;

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
            $contrato->setCliente($this);
        }

        return $this;
    }

    public function removeContrato(Contrato $contrato): self
    {
        if ($this->contratos->removeElement($contrato)) {
            if ($contrato->getCliente() === $this) {
                $contrato->setCliente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ClienteHistorial[]
     */
    public function getClienteHistorials(): Collection
    {
        return $this->clienteHistorials;
    }

    public function addClienteHistorial(ClienteHistorial $clienteHistorial): self
    {
        if (!$this->clienteHistorials->contains($clienteHistorial)) {
            $this->clienteHistorials[] = $clienteHistorial;
            $clienteHistorial->setCliente($this);
        }

        return $this;
    }

    public function removeClienteHistorial(ClienteHistorial $clienteHistorial): self
    {
        if ($this->clienteHistorials->removeElement($clienteHistorial)) {
            if ($clienteHistorial->getCliente() === $this) {
                $clienteHistorial->setCliente(null);
            }
        }

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

    public function getTelefonoRecado(): ?string
    {
        return $this->telefonoRecado;
    }

    public function setTelefonoRecado(?string $telefonoRecado): self
    {
        $this->telefonoRecado = $telefonoRecado;

        return $this;
    }

    public function getRutHash(): ?string
    {
        return $this->rutHash;
    }

    public function getTelefonoHash(): ?string
    {
        return $this->telefonoHash;
    }

    public function getCorreoHash(): ?string
    {
        return $this->correoHash;
    }

    public function getTelefonoRecadoHash(): ?string
    {
        return $this->telefonoRecadoHash;
    }

    public function getNacionalidad(): ?string
    {
        return $this->nacionalidad;
    }

    public function setNacionalidad(?string $nacionalidad): self
    {
        $this->nacionalidad = $nacionalidad;

        return $this;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getCiudad(): ?Ciudad
    {
        return $this->ciudad;
    }

    public function setCiudad(?Ciudad $ciudad): self
    {
        $this->ciudad = $ciudad;

        return $this;
    }

    public function getComuna(): ?Comuna
    {
        return $this->comuna;
    }

    public function setComuna(?Comuna $comuna): self
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

    public function getReunion(): ?Reunion
    {
        return $this->reunion;
    }

    public function setReunion(?Reunion $reunion): self
    {
        $this->reunion = $reunion;

        return $this;
    }

    /**
     * Calcula los hashes de búsqueda (rutHash, telefonoHash, telefonoRecadoHash, correoHash) al
     * crear un Cliente nuevo. En este punto del ciclo de vida de Doctrine las
     * propiedades aún son texto plano; el cifrado de las columnas ocurre después,
     * a nivel de EncryptedStringType.
     *
     * @ORM\PrePersist()
     */
    public function calcularHashesAlCrear(): void
    {
        $this->rutHash = Cifrado::hash($this->rut, 'rut');
        $this->telefonoHash = Cifrado::hash($this->telefono, 'telefono');
        $this->telefonoRecadoHash = Cifrado::hash($this->telefonoRecado, 'telefono');
        $this->correoHash = Cifrado::hash($this->correo, 'correo');
    }

    /**
     * Recalcula los hashes de búsqueda al actualizar. PreUpdate es especial en
     * Doctrine: el changeset ya se calculó antes de que este evento se dispare, así
     * que asignar una propiedad acá no alcanza a incluirse en el UPDATE.
     *
     * Tampoco sirve PreUpdateEventArgs::setNewValue(): ese método solo puede cambiar
     * el valor de un campo que YA está en el changeset, y un hash que no cambió (o
     * que aún es NULL en la base) no está ahí — de hecho lanza
     * "Field ... is not a valid field of the entity ... in PreUpdateEventArgs".
     *
     * La forma correcta de agregar campos al UPDATE desde PreUpdate es asignarlos y
     * pedirle a la UnitOfWork que recalcule el changeset de esta entidad, que se
     * fusiona con el que ya existía.
     *
     * @ORM\PreUpdate()
     */
    public function calcularHashesAlActualizar(PreUpdateEventArgs $event): void
    {
        $this->rutHash = Cifrado::hash($this->rut, 'rut');
        $this->telefonoHash = Cifrado::hash($this->telefono, 'telefono');
        $this->telefonoRecadoHash = Cifrado::hash($this->telefonoRecado, 'telefono');
        $this->correoHash = Cifrado::hash($this->correo, 'correo');

        $em = $event->getEntityManager();
        $em->getUnitOfWork()->recomputeSingleEntityChangeSet(
            $em->getClassMetadata(self::class),
            $this
        );
    }
}
