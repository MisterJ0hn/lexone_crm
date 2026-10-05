<?php

namespace App\Form;

use App\Entity\Empresa;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\File;

class EmpresaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre')
            ->add('rol')
            ->add('rut')
            ->add('logo', FileType::class, [
                'label' => 'Imagen (PNG)',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // everytime you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Por favor, subir imagen PNG,JPEG ó GIF',
                    ])
                ],
            ])
            ->add('fechaVigencia')
            ->add('lexflowHabilitado', CheckboxType::class, ['required' => false])
            ->add('lexflowSoloCrm', CheckboxType::class, ['required' => false])
            ->add('pjudHabilitado', CheckboxType::class, ['required' => false])
            ->add('pjudClientKey', null, ['required' => false])
            ->add('pjudEmail', null, ['required' => false])
            ->add('pjudPassword', PasswordType::class, ['required' => false, 'mapped' => false])
            ->add('edapiKey', null, ['required' => false])
            ->add('edapiClienteGuid', null, ['required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Empresa::class,
        ]);
    }
}
