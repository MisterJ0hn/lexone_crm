<?php

namespace App\Form;

use App\Entity\Agenda;
use App\Entity\TipoCliente;
use App\Repository\TipoClienteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgendaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombreCliente')
            ->add('emailCliente')
            ->add('telefonoCliente')
            ->add('rutCliente')
            ->add('telefonoRecadoCliente')
            ->add('tipoCliente', EntityType::class, [
                'class' => TipoCliente::class,
                'choice_label' => 'nombre',
                'query_builder' => fn (TipoClienteRepository $r) => $r->createQueryBuilder('t')->orderBy('t.id', 'ASC'),
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Agenda::class,
        ]);
    }
}
