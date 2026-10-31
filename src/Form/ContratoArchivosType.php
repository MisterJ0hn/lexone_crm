<?php

namespace App\Form;

use App\Entity\ContratoArchivos;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;

class ContratoArchivosType extends AbstractType
{
    public const TAMANO_MAXIMO = '20M';
    public const EXTENSIONES = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'txt', 'csv', 'jpg', 'jpeg', 'png', 'gif', 'zip'];
    public const EXTENSIONES_TEXTO = 'PDF, Word, Excel, PowerPoint, OpenOffice, TXT, CSV, JPG, PNG, GIF y ZIP';
    // El contenido real (finfo) debe coincidir con alguno de estos tipos, no solo el nombre.
    public const MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.oasis.opendocument.text',
        'application/vnd.oasis.opendocument.spreadsheet',
        'application/zip',
        'application/x-zip-compressed',
        'application/octet-stream', // algunos .doc/.xls antiguos; la extensión sigue validándose
        'text/plain',
        'text/csv',
        'image/jpeg',
        'image/png',
        'image/gif',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', FileType::class, [
                'constraints' => [
                    new NotNull(message: 'Debes seleccionar un archivo.'),
                    new File(
                        maxSize: self::TAMANO_MAXIMO,
                        extensions: self::EXTENSIONES,
                        mimeTypes: self::MIME_TYPES,
                        maxSizeMessage: 'El archivo pesa {{ size }} {{ suffix }}; el máximo permitido es {{ limit }} {{ suffix }}.',
                        extensionsMessage: 'Tipo de archivo no permitido. Formatos válidos: ' . self::EXTENSIONES_TEXTO . '.',
                        mimeTypesMessage: 'El contenido del archivo no corresponde a un formato permitido.',
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContratoArchivos::class,
        ]);
    }
}
