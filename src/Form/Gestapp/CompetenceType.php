<?php

namespace App\Form\Gestapp;

use App\Entity\Gestapp\Beneficiary;
use App\Entity\Gestapp\Competence;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Symfony\Component\Translation\t;

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
                'data' => 'Nonacquis',
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
                'data' => 'Nonacquis',
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
                'data' => 'Nonacquis',
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
                'data' => 'Nonacquis',
            ])
            ->add('isAutoEva',CheckboxType::class, [
                'label' => 'L\'auto évaluation du bénéficiaire a été réalisée avant l\'action.',
                'required' => false,
            ])
            ->add('isAutoEvaEnd' ,CheckboxType::class, [
                'label' => 'L\'auto évaluation du bénéficiaire a été réalisée après l\'action.',
                'required' => false,
            ])
            ->add('isDigComp0')
            ->add('isDigComp1')
            ->add('isDigComp2')
            ->add('isDigComp3')
            ->add('isDigComp4')
            ->add('isDigComp5')
            ->add('detailParcour',TextareaType::class,[
                'label' => 'detail parcour',
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if (null === $data->getCompBase()) {
                $data->setCompBase('Nonacquis');
                $data->setCompDesk('Nonacquis');
                $data->setCompInternet('Nonacquis');
                $data->setCompEmail('Nonacquis');
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Competence::class,
        ]);
    }
}
