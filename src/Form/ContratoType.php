<?php

namespace App\Form;

use App\Entity\Cliente;
use App\Entity\Contrato;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class ContratoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    { 
        $cliente = $options['cliente'];

        $builder
            ->add('nombre', TextType::class, [
                'mapped' => false,
                'data' => $cliente ? $cliente->getNombre() : null,
            ])
            ->add('email', TextType::class, [
                'mapped' => false,
                'data' => $cliente ? $cliente->getCorreo() : null,
                'constraints' => CorreoClienteConstraints::restricciones(),
            ])
            ->add('telefono', TextType::class, [
                'mapped' => false,
                'data' => $cliente ? $cliente->getTelefono() : null,
            ])
            ->add('rut', TextType::class, [
                'mapped' => false,
                'data' => $cliente ? $cliente->getRut() : null,
            ])
            ->add('direccion', TextType::class, [
                'mapped' => false,
                'data' => $cliente ? $cliente->getDireccion() : null,
            ])
            ->add('montoNivelDeuda')
            ->add('MontoContrato')
            ->add('primeraCuota')
            ->add('isAbono')
            ->add('isTotal')
            ->add('cuotas')
            ->add('valorCuota')
            ->add('situacionLaboral')
             ->add('claveUnica', TextType::class, [
                'mapped' => false,
                'required' => false,
                'data' => $cliente ? $cliente->getClaveUnica() : null,
            ])
            ->add('pais')
            ->add('telefonoRecado', TextType::class, [
                'mapped' => false,
                'required' => false,
                'data' => $cliente ? $cliente->getTelefonoRecado() : null,
            ])
            ->add('vehiculo')
            ->add('vivienda')
            ->add('reunion')
            ->add('observacion')

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contrato::class,
            'cliente' => null,
        ]);
        $resolver->setAllowedTypes('cliente', [Cliente::class, 'null']);
    }
}
