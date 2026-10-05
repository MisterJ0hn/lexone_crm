<?php

namespace App\Form;

use App\Entity\Ciudad;
use App\Entity\Cliente;
use App\Entity\Comuna;
use App\Entity\Region;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Formulario para crear/reutilizar un Cliente propio de una Agenda de tipo
 * Convenio/Empresa (ver Agenda::$tipoCliente y ContratoController::nuevoClienteConvenio).
 *
 * A diferencia del flujo Persona (ContratoType, con los campos de cliente
 * "mapped=>false" leídos/escritos a mano en el controller), este formulario
 * mapea directo contra la entidad Cliente.
 *
 * Los campos 'sexo' y 'estadoCivil' NO se agregan aquí: solo aplican a Convenio
 * (no a Empresa), así que el controller los agrega dinámicamente con
 * $form->add('sexo', ChoiceType::class, ...) / $form->add('estadoCivil') cuando
 * corresponde, igual que ya hacen AdministradorCuentasController/AdministradoresController
 * para el sexo de Usuario.
 *
 * 'nacionalidad' tampoco se agrega aquí: Cliente::$nacionalidad es un string (no
 * una relación a Pais), así que el controller arma su combo dinámicamente con
 * $form->add('nacionalidad', ChoiceType::class, ...) a partir de PaisRepository,
 * igual que el resto de campos dependientes del tenant (empresa actual).
 */
class ClienteConvenioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Al CREAR un cliente hijo casi todos los datos son obligatorios; al modificar uno existente solo los
        // básicos (nombre, RUT y correo), para no obligar a completar de golpe fichas antiguas incompletas.
        $todos = $options['obligatorios'];
        $obligatorio = static fn (string $mensaje): array => $todos
            ? ['required' => true, 'constraints' => [new NotBlank(message: $mensaje)]]
            : [];
        $obligatorioEntidad = static fn (string $mensaje): array => $todos
            ? ['required' => true, 'placeholder' => '', 'constraints' => [new NotNull(message: $mensaje)]]
            : ['required' => false];

        $builder
            ->add('nombre', null, [
                'required' => true,
                'constraints' => [new NotBlank(message: 'Debe ingresar el nombre (o la razón social).')],
            ])
            // RUT y correo son siempre obligatorios (el RUT, además, identifica al cliente: se reutiliza si ya existe).
            ->add('rut', null, [
                'required' => true,
                'constraints' => [new NotBlank(message: 'Debe ingresar el RUT del cliente.')],
            ])
            ->add('correo', null, [
                'required' => true,
                'constraints' => CorreoClienteConstraints::restricciones(),
            ])
            ->add('telefono', null, $obligatorio('Debe ingresar el teléfono del cliente.'))
            ->add('direccion', null, $obligatorio('Debe ingresar la dirección del cliente.'))
            // La Clave Única es opcional: si está, PjudController::credenciales() la usa para consultar api-pjud.
            ->add('claveUnica', null, ['required' => false])
            // Region/Ciudad/Comuna no tienen __toString(), a diferencia de EstadoCivil
            // y Reunion (que sí, y por eso se dejan con guessing automático de tipo);
            // hay que indicar explícitamente el campo a mostrar en el combo.
            ->add('region', EntityType::class, [
                'class' => Region::class,
                'choice_label' => 'nombre',
            ] + $obligatorioEntidad('Debe seleccionar la región.'))
            ->add('ciudad', EntityType::class, [
                'class' => Ciudad::class,
                'choice_label' => 'nombre',
            ] + $obligatorioEntidad('Debe seleccionar la ciudad.'))
            ->add('comuna', EntityType::class, [
                'class' => Comuna::class,
                'choice_label' => 'nombre',
            ] + $obligatorioEntidad('Debe seleccionar la comuna.'))
            ->add('reunion', null, $obligatorioEntidad('Debe seleccionar la reunión.'))
            // Representante legal (Convenio/Empresa).
            /*->add('repLegalRut', null, ['required' => false])
            ->add('repLegalNombre', null, ['required' => false])
            ->add('repLegalEstadoCivil', null, ['required' => false])
            ->add('repLegalProfesion', null, ['required' => false])*/
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cliente::class,
            'obligatorios' => true,
        ]);
        $resolver->setAllowedTypes('obligatorios', 'bool');
    }
}
