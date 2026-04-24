<?php

namespace App\Form\Admin;

use App\Config\Civility;
use App\Entity\Admin\Member;
use App\Entity\Admin\Structure;
use App\Entity\Gestapp\Competence;
use App\Form\Gestapp\CompetenceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StructureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class,[
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Entrez le nom',
                ],
            ])
            ->add('address', TextType::class,[
                'label' => 'Adresse',
                'attr' => [
                    'placeholder' => 'Entrez l\'adresse',
                ],
            ])
            ->add('zipcode', TextType::class,[
                'label' => 'CP',
                'attr' => [
                    'placeholder' => 'CP',
                ],
            ])
            ->add('city', TextType::class,[
                'label' => 'Commune',
                'attr' => [
                    'placeholder' => 'Commune',
                ],
            ])
            ->add('contactEmail', TextType::class,[
                'label' => 'Email',
            ])
            ->add('contactPhone', TextType::class,[
                'label' => 'Téléphone',
            ])
            ->add('contactResponsableFirstname', TextType::class,[
                'label' => 'Nom du responsable',
            ])
            ->add('contactResponsableLastname', TextType::class,[
                'label' => 'Prénom du responsable',
            ])
            ->add('contactResponsableCivility', EnumType::class, [
                'class' => Civility::class,
            ])
            //->add('members', CollectionType::class, [
            //    'label' => 'Membres de la structure',
            //    'help' => 'Ajouter un membre avant de sauvegarder',
            //    'entry_type' => AddMemberStructureType::class,
            //    'entry_options' => ['label' => false,],
            //    'allow_add' => true,
            //    'allow_delete' => true,
            //    'by_reference' => false,
            //    'attr' => [
            //        'data-controller' => 'form-collection'
            //    ]
            //])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Structure::class,
        ]);
    }
}
