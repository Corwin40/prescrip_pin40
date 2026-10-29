<?php

namespace App\Form\Gestapp;

use App\Entity\Gestapp\Document;
use App\Entity\Gestapp\Prescription;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\UX\Dropzone\Form\DropzoneType;

class DocumentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('documentFile', DropzoneType::class, [
                'attr' => [
                    'placeholder' => 'Glisser et déposer un fichier ou cliquez sur "Parcourir"',
                ],
                'mapped' => false,
                'required' => false,
                'help' => 'Veuillez choisir un fichier au format PDF de moins de 20Mo',
                'constraints' => [
                    new File([
                        'maxSize' => '20000k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Le fichier doit être au format PDF (format reçu : {{ type }}).',
                        'maxSizeMessage' => 'Le fichier est trop volumineux ({{ size }} {{ suffix }}). La taille maximale autorisée est de {{ limit }} {{ suffix }}.',
                        'uploadIniSizeErrorMessage' => 'Le fichier dépasse la taille maximale acceptée par le serveur ({{ limit }} {{ suffix }}).',
                        'uploadErrorMessage' => 'Le téléversement du fichier a échoué. Veuillez réessayer.',
                    ])
                ],

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}
