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
        $builder
            ->add('nombre')
            ->add('rut')
            ->add('correo')
            ->add('telefono')
            ->add('direccion')
            ->add('claveUnica')
            // Region/Ciudad/Comuna no tienen __toString(), a diferencia de EstadoCivil
            // y Reunion (que sí, y por eso se dejan con guessing automático de tipo);
            // hay que indicar explícitamente el campo a mostrar en el combo.
            ->add('region', EntityType::class, [
                'class' => Region::class,
                'choice_label' => 'nombre',
                'required' => false,
            ])
            ->add('ciudad', EntityType::class, [
                'class' => Ciudad::class,
                'choice_label' => 'nombre',
                'required' => false,
            ])
            ->add('comuna', EntityType::class, [
                'class' => Comuna::class,
                'choice_label' => 'nombre',
                'required' => false,
            ])
            ->add('reunion')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cliente::class,
        ]);
    }
}
