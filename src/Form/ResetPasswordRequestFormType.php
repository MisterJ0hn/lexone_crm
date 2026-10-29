<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rutEmpresa', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ingrese el RUT de la empresa',
                    ]),
                ],
            ])
            ->add('username', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ingrese su nombre de usuario',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
