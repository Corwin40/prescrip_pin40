<?php

namespace App\Form\Search;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class dashboardPrescriptionSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('query', TextType::class, [
                'label' => 'Nom ou prénom',
                'required' => false,
                'attr' => ['class' => 'form-control form-control-sm']
            ])
            ->add('prescriptor', ChoiceType::class, [
                'label' => 'Prescripteur',
                'required' => false,
                'choices' => $options['prescriptors'],
                'placeholder' => 'Tous',
                'attr' => ['class' => 'form-select form-select-sm']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'prescriptors' => [],
        ]);
    }
}
