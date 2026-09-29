<?php

namespace App\Form;

use App\Entity\ContratoTemplate;
use App\Entity\TipoCliente;
use App\Repository\TipoClienteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContratoTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre de la plantilla',
            ])
            ->add('tipoCliente', EntityType::class, [
                'class' => TipoCliente::class,
                'choice_label' => 'nombre',
                'label' => 'Tipo de cliente',
                'query_builder' => fn (TipoClienteRepository $r) => $r->createQueryBuilder('t')->orderBy('t.id', 'ASC'),
            ])
            ->add('contenido', TextareaType::class, [
                'label' => 'Contenido',
            ])
            ->add('activo', CheckboxType::class, [
                'label' => 'Disponible (aparece para elegir al crear un contrato de este tipo de cliente)',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContratoTemplate::class,
        ]);
    }
}
