<?php

namespace App\Form\Gestapp;

use App\Entity\Gestapp\Equipment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeEquipment',ChoiceType::class, [
                'label'=>'Type Equipment',
                'choices'=>[
                    'Ordinateur de bureau'=>'Ordinateur de bureau',
                    'ordinateur portable'=>'Ordinateur portable',
                    'tablette'=>'Tablette',

                ],
                'placeholder'=>'Veuillez choisir un type',
                'required'=>true,
            ])


            ->add('brandEquipment')
            ->add('matriculEquipment')
            ->add('osInstalled',ChoiceType::class, [
                'label'=>'OS Installé',
                'choices'=>[
                    'windows 11'=>'Windows 11',
                    'windows 10'=>'Windows 10',
                    'linux'=>'Linux',
                    'Android'=>'Android',
                ],
                'placeholder'=>'Veuillez choisir',
                'required'=>true,
            ])

            ->add('statusEquipment',ChoiceType::class, [
                'label'=>'Status',
                'choices'=>[
                    'neuf'=>'neuf',
                    'bon état'=>'bon état',
                    'satisfaisant'=>'satisfaisant',
                   ],
                'placeholder'=>'Veuillez choisir',
                'required'=>true,
            ])

            ->add('isDispo')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Equipment::class,
        ]);
    }
}
