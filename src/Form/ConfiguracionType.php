<?php

namespace App\Form;

use App\Entity\Configuracion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfiguracionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('maxDiasComision')
            ->add('morosidadPatColor', ChoiceType::class, [
                'choices' => [
                    'text-warning' => 'text-warning',
                    'text-danger'  => 'text-danger',
                    'text-info'    => 'text-info',
                    'text-success'    => 'text-success',
                    'text-ligth'    => 'text-ligth',
                ],
            ])
        ;
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Configuracion::class,
        ]);
    }
}
