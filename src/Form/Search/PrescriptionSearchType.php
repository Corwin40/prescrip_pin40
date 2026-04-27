<?php

namespace App\Form\Search;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrescriptionSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ref', TextType::class, [
                'label' => 'Référence',
                'required' => false,
                'attr' => ['class' => 'form-control form-control-sm', 'placeholder' => 'Référence',]
            ])
            ->add('prescriptor', ChoiceType::class, [
                'label' => 'Prescripteur',
                'required' => false,
                'choices' => $options['prescriptors'],
                'placeholder' => 'Prescripteur',
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
