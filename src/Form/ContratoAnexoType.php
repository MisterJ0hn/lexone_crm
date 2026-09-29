<?php

namespace App\Form;

use App\Entity\ContratoAnexo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class ContratoAnexoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('MontoContrato')
            ->add('isAbono')
            ->add('abono')
            ->add('isTotal')
            ->add('nCuotas')
            ->add('valorCuota')
            ->add('vigencia')
            ->add('observacion')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContratoAnexo::class,
        ]);
    }
}
