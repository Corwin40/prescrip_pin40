<?php

namespace App\Form\Gestapp;

use App\Entity\Gestapp\Beneficiary;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class BeneficiaryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname')
            ->add('lastname')
            ->add('civility', ChoiceType::class, [
                'label' => 'Civilité',
                'choices' => [
                    'Mme' => 'Mme',
                    'Mlle' => 'Mlle',
                    'Mr' => 'Mr',
                    'Autre' => 'Autre',
                ],
                'placeholder' => 'veuillez choisir',
                'required' => true,
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'Genre',
                'choices' => [
                    'Féminin' => 'f',
                    'Masculin' => 'm',
                    'Autre' => 'autre',
                ],
                'placeholder' => 'veuillez choisir',
                'required' => true,
            ])
            ->add('ageGroup', ChoiceType::class, [
                'label' => 'Tranche d’âge',
                'choices' => [
                    '15 ans et moins' => '0-15',
                    '16 à 25 ans' => '16-25',
                    '26 à 59 ans' => '26-59',
                    '60 ans et plus' => '60+',
                ],
                'placeholder' => 'Veuillez choisir',
                'required' => true,
            ])

            ->add('professionnalStatus', ChoiceType::class, [
                'label' => 'Situation',
                'choices' => [
                    'scolaire/Étudiant' => 'scolaire/etudiant',
                    'En emploi ou en formation' => 'employe/formation',
                    'Sans emplois' => 'sans emploi',
                    'Retraité' => 'retraite',
                    'Autre situation' => 'autre',
                ],
                'placeholder' => 'veuillez choisir',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Beneficiary::class,
        ]);
    }
}
