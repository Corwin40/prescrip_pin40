<?php

namespace App\Form\Gestapp;

use App\Entity\Admin\Member;
use App\Entity\Gestapp\Equipment;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('equipmentId')
            ->add('matriculEquipment')
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
            ->add('note')
            ->add('recoveryAt')
            ->add('isDispo')
            ->add('reconditioner', EntityType::class, [
                'class' => Member::class,
                'choice_label' => function ($prescriptor) {
                    return $prescriptor->getNameStructure();
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er
                        ->createQueryBuilder('p')
                        ->where('p.roles LIKE :roles')
                        ->setParameter('roles', '%ROLE_RECONDITIONNEUR%')
                        ->orderBy('p.id', 'ASC');
                },
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
