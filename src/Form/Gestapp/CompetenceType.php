<?php

namespace App\Form\Gestapp;

use App\Entity\Gestapp\Beneficiary;
use App\Entity\Gestapp\Competence;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('compBase', ChoiceType::class, [
                'label' => 'competence',
                'choices' => [
                    'Acquis' => 'acquis',
                    'En cours' => 'encours',
                    'Non acquis' => 'Nonacquis',

                ],
                'required' => true,
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('compDesk', ChoiceType::class, [
                'label' => 'competence',
                'choices' => [
                    'Acquis' => 'acquis',
                    'En cours' => 'encours',
                    'Non acquis' => 'Nonacquis',
                ],
                'required' => true,
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('compInternet', ChoiceType::class, [
                'label' => 'competence',
                'choices' => [
                    'Acquis' => 'acquis',
                    'En cours' => 'encours',
                    'Non acquis' => 'Nonacquis',
                ],
                'required' => true,
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('compEmail', ChoiceType::class, [
                'label' => 'competence',
                'choices' => [
                    'Acquis' => 'acquis',
                    'En cours' => 'encours',
                    'Non acquis' => 'Nonacquis',
                ],
                'required' => true,
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('isAutoEva',CheckboxType::class, [
                'label' => 'IsAutoEva',
                'required' => true,
            ])

            ->add('isAutoEvaEnd' ,CheckboxType::class, [
                'label' => 'IsAutoEvaEnd ',
                'required' => true,
            ])

            ->add('isDigComp0')
            ->add('isDigComp1')
            ->add('isDigComp2')
            ->add('isDigComp3')
            ->add('isDigComp4')
            ->add('isDigComp5')
            ->add('detailParcour')
    ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Competence::class,
        ]);
    }
}
