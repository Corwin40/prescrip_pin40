<?php

namespace App\Form\Admin;

use App\Entity\Admin\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class MemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false, // ⚠️ ne lie pas directement à l'entité
                'required' => true, // ✅ obligatoire à la création
                'label' => 'Mot de passe',
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Saisir un mot de passe',
                    'class' => 'form-control',
                ],
            ])
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
                    'Monsieur' => 'Mr',
                ],
                'label' => 'Civilité',
                'placeholder' => 'Sélectionnez une civilité',
                'required' => true,
            ])
            ->add('isVerified')
            ->add('role', ChoiceType::class, [
                'label' => 'Rôle proposé',
                'choices' => [
                    'Prescripteur' => 'ROLE_PRESCRIPTEUR',
                    'Médiateur' => 'ROLE_MEDIATEUR',
                ],
                'expanded' => true,   // ✅ boutons radio
                'multiple' => false,  // un seul rôle
                'mapped' => false,    // ⚠️ géré dans le contrôleur
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Member::class,
        ]);
    }
}
