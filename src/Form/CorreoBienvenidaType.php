<?php

namespace App\Form;

use App\Entity\CorreoBienvenida;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CorreoBienvenidaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('remitenteNombre', TextType::class, ['label' => 'Nombre del remitente', 'required' => false])
            ->add('remitenteCorreo', EmailType::class, ['label' => 'Correo del remitente'])
            ->add('asunto', TextType::class, ['label' => 'Asunto (admite variables)'])
            // Si se sube un archivo, su contenido reemplaza al del textarea (ver el controller).
            ->add('archivo', FileType::class, [
                'label' => 'Subir template HTML (opcional)',
                'mapped' => false,
                'required' => false,
                'constraints' => [new File(maxSize: '1M', extensions: ['html', 'htm'], extensionsMessage: 'El archivo debe ser .html o .htm')],
            ])
            ->add('contenido', TextareaType::class, ['label' => 'Contenido HTML', 'required' => false])
            // Imagen incrustada en el correo; se usa en el HTML como <img src="{{imagen}}">.
            ->add('imagenArchivo', FileType::class, [
                'label' => 'Imagen del correo (opcional)',
                'mapped' => false,
                'required' => false,
                'constraints' => [new File(maxSize: '2M', mimeTypes: ['image/png', 'image/jpeg', 'image/gif'], mimeTypesMessage: 'La imagen debe ser PNG, JPG o GIF')],
            ])
            ->add('quitarImagen', CheckboxType::class, ['label' => 'Quitar la imagen actual', 'mapped' => false, 'required' => false])
            ->add('activo', CheckboxType::class, ['label' => 'Enviar este correo al crear un contrato', 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => CorreoBienvenida::class]);
    }
}
