<?php

namespace App\Form\Search;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BeneficiarySearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('query', TextType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control form-control-sm', 'placeholder' => 'Rechercher un bénéficiaire',]
            ])
            ->add('structure', ChoiceType::class, [
                'required' => false,
                'choices' => $options['prescripteurs'],
                'placeholder' => 'Tous',
                'attr' => ['class' => 'form-select form-select-sm']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'prescripteurs' => [],
        ]);
    }
}
