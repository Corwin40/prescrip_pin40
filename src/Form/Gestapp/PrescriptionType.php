<?php

namespace App\Form\Gestapp;

use App\Entity\Admin\Member;
use App\Entity\Gestapp\Beneficiary;
use App\Entity\Gestapp\Equipment;
use App\Entity\Gestapp\Prescription;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrescriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ref')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('membre', EntityType::class, [
                'class' => Member::class,
                'choice_label' => 'id',
            ])
            ->add('beneficiaire', EntityType::class, [
                'class' => Beneficiary::class,
                'choice_label' => 'id',
            ])
            ->add('equipement', EntityType::class, [
                'class' => Equipment::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Prescription::class,
        ]);
    }
}
