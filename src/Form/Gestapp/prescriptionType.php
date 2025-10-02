<?php

namespace App\Form\Gestapp;

use App\Entity\Admin\Member;
use App\Entity\Gestapp\beneficiary;
use App\Entity\Gestapp\equipment;
use App\Entity\Gestapp\prescription;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class prescriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('createdAt')
            ->add('updatedAt')
            ->add('ref')
            ->add('idMember', EntityType::class, [
                'class' => Member::class,
                'choice_label' => 'id',
            ])
            ->add('beneficiary', EntityType::class, [
                'class' => beneficiary::class,
                'choice_label' => 'id',
            ])
            ->add('idBenefiaciary', EntityType::class, [
                'class' => beneficiary::class,
                'choice_label' => 'id',
            ])
            ->add('equipment', EntityType::class, [
                'class' => equipment::class,
                'choice_label' => 'id',
            ])
            ->add('idEquipment', EntityType::class, [
                'class' => equipment::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => prescription::class,
        ]);
    }
}
