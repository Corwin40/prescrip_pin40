<?php

namespace App\Form\Gestapp;

use App\Entity\Admin\Member;
use App\Entity\Gestapp\Beneficiary;
use App\Entity\Gestapp\Prescription;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
                    'Mr' => 'Mr',
                    'Mme' => 'Mme',
                    'Mlle' => 'Mlle',
                    'Autre' => 'Autre',
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
                'expanded' => true,
                'multiple' => false,
                'placeholder' => 'Veuillez choisir',
                'required' => true,
            ])
            ->add('prescriptor', EntityType::class, [
                'class' => Member::class,
                'choice_label' => function ($prescriptor) {
                    return $prescriptor->getNameStructure();
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er
                        ->createQueryBuilder('p')
                        ->where('p.roles LIKE :roles')
                        ->setParameter('roles', '%ROLE_PRESCRIPTEUR%')
                        ->orderBy('p.id', 'ASC');
                },
            ])
            ->add('professionnalStatus', ChoiceType::class, [
                'label' => 'Statut professionnel',
                'choices' => [
                    'Scolaire / Étudiant' => 'Scolaire / Étudiant',
                    'En emploi ou en formation' => 'Employé / En Formation',
                    'Sans emploi' => 'Sans emploi',
                    'Retraité' => 'Retraite',
                    'Autre situation' => 'Autre situation',
                ],
                'expanded' => true,
                'multiple' => false,
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
