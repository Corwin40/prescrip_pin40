<?php

namespace App\Form\Gestapp;

use App\Entity\Gestapp\Equipment;
use App\Entity\Gestapp\prescription;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeEquipment')
            ->add('computerBrand')
            ->add('matriculEquipment')
            ->add('osInstalled')
            ->add('statusEquipment')
            ->add('isDispo')
            ->add('prescription', EntityType::class, [
                'class' => prescription::class,
                'choice_label' => 'id',
            ])
            ->add('prescriptions', EntityType::class, [
                'class' => prescription::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equipment::class,
        ]);
    }
}
