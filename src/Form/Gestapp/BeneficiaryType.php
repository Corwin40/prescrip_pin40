<?php

namespace App\Form\Gestapp;

use App\Entity\Gestapp\Beneficiary;
use App\Entity\Gestapp\prescription;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BeneficiaryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname')
            ->add('lastname')
            ->add('civility')
            ->add('gender')
            ->add('ageGroup')
            ->add('professionnalStatus')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('prescription', EntityType::class, [
                'class' => prescription::class,
                'choice_label' => 'id',
            ])
            ->add('beneficiary', EntityType::class, [
                'class' => prescription::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Beneficiary::class,
        ]);
    }
}
