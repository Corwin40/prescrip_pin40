<?php

namespace App\Form\Admin;

use App\Entity\Admin\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class MemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            #->add('password')
            ->add('nameStructure')
            ->add('address')
            ->add('zipcode')
            ->add('city')
            ->add('contactEmail')
            ->add('contactPhone')
            ->add('ContactResponsableFirstname')
            ->add('contactResponsableLastname')
            ->add('contactResponsableCivility', ChoiceType::class, [
                'choices' => [
                    'Mme' => 'Mme',
                    'Mlle' => 'Mlle',
                    'Monsieur' => 'Monsieur',
                ],
                'label' => 'Civilité',
                'placeholder' => 'Sélectionnez une civilité',
                'required' => true,
            ])
            ->add('isVerified');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Member::class,
        ]);
    }
}
