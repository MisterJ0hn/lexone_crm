<?php

namespace App\Form;

use App\Entity\Vencimiento;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VencimientoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('valMin')
            ->add('valMax')
            ->add('color', ColorType::class, [
                'html5' => true,
                'attr' => ['class' => 'form-control form-control-color js-vencimiento-color', 'style' => 'height:38px;padding:2px;'],
            ])
            ->add('icono')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Vencimiento::class,
        ]);
    }
}
