<?php

namespace App\Form\Gestapp;

use App\Entity\Admin\Member;
use App\Entity\Gestapp\Beneficiary;
use App\Entity\Gestapp\Equipment;
use App\Entity\Gestapp\Prescription;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Sodium\add;

class PrescriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('beneficiaire', EntityType::class, [
                'class' => Beneficiary::class,
                'choice_label' => 'id',
            ])
            ->add('equipement', EntityType::class, [
                'class' => Equipment::class,
                'choice_label' => 'id',
            ])
            ->add('details')
            ->add('baseCompetence', ChoiceType::class, [
                'label' => 'Compétences de base',
                'choices' => [
                    'Acquises' => 'Acquises',
                    'À vérifier' => 'A verifier',
                    'Non acquises' => 'Non acquises',
                ],
                'placeholder' => 'Veuillez choisir',
                'required' => true,
            ])


            ->add('lieuMediation')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Prescription::class,
        ]);
    }
}
