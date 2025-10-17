<?php

namespace App\Form\Gestapp;

use App\Entity\Gestapp\Beneficiary;
use App\Entity\Gestapp\Competence;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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
                'placeholder' => 'Veuillez choisir',
                'required' => true,
            ])
            ->add('compDesk', ChoiceType::class, [
                'label' => 'competence',
                'choices' => [
                    'Acquis' => 'acquis',
                    'En cours' => 'encours',
                    'Non acquis' => 'Nonacquis',
                ],
                 'placeholder' => 'Veuillez choisir',
                'required' => true,
            ])
            ->add('compInternet', ChoiceType::class, [
        'label' => 'competence',
        'choices' => [
            'Acquis' => 'acquis',
            'En cours' => 'encours',
            'Non acquis' => 'Nonacquis',
        ],
             'placeholder' => 'Veuillez choisir',
             'required' => true,
    ])



            ->add('compEmail', ChoiceType::class, [
        'label' => 'competence',
        'choices' => [
            'Acquis' => 'acquis',
            'En cours' => 'encours',
            'Non acquis' => 'Nonacquis',
        ],
             'placeholder' => 'Veuillez choisir',
             'required' => true,
    ])




            ->add('isAutoEva')
            ->add('isDigComp1')
            ->add('isDigComp2')
            ->add('isDigComp3')
            ->add('isDigComp4')
            ->add('isDigComp5')
            ->add('detailParcour')
            ->add('isAutoEvalEnd')
            ->add('beneficiary', EntityType::class, [
                'class' => Beneficiary::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Competence::class,
        ]);
    }
}
