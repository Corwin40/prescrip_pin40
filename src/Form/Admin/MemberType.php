<?php

namespace App\Form\Admin;

use App\Entity\Admin\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class MemberType extends AbstractType
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $route = $request?->attributes->get('_route');

        $builder
            ->add('email')

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
        ;

        if ($route == 'app_admin_member_new') {
            $builder
                ->add('password', PasswordType::class, [
                    'mapped' => false, //  ne lie pas directement à l'entité
                    'required' => true, // obligatoire à la création
                    'label' => 'Mot de passe',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'placeholder' => 'Saisir un mot de passe',
                        'class' => 'form-control',
                    ],
                ])
                ;

        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Member::class,
        ]);
    }
}
