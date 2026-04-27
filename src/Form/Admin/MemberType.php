<?php

namespace App\Form\Admin;

use App\Config\Civility;
use App\Entity\Admin\Member;
use App\Entity\Admin\Structure;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;

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
            ->add('civility',EnumType::class, [
                'label' => 'Civilité',
                'class' => Civility::class,
                'choice_label' => static function (\UnitEnum $choice): string {
                    return $choice->value;
                },
            ])
            ->add('firstname')
            ->add('lastname')
            ->add('structure', EntityType::class, [
                'label' => 'Structure',
                'class' => Structure::class,
                'choice_label' => 'name',
                'placeholder' => 'Sélectionnez une structure',
                'required' => true,
            ])
        ;

        if ($route == 'app_admin_member_new') {
            $builder
                ->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'first_options'  => ['label' => 'Mot de passe', 'hash_property_path' => 'password'],
                    'second_options' => ['label' => 'Retapez le mot de passe'],
                    'mapped' => false, //  ne lie pas directement à l'entité
                    'required' => true, // obligatoire à la création
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'placeholder' => 'Saisir un mot de passe',
                        'class' => 'form-control',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Il nous faut un mot de passe, ne laissez pas ce champs Vide.',
                        ]),
                        new Length([
                            'min' => 12,
                            'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                        new Assert\Regex([
                            'pattern' => '/[A-Z]/',
                            'message' => 'Le mot de passe doit contenir au moins une lettre majuscule.',
                        ]),
                        new Assert\Regex([
                            'pattern' => '/[a-z]/',
                            'message' => 'Le mot de passe doit contenir au moins une lettre minuscule.',
                        ]),
                        new Assert\Regex([
                            'pattern' => '/[0-9]/',
                            'message' => 'Le mot de passe doit contenir au moins un chiffre.',
                        ]),
                        new Assert\Regex([
                            'pattern' => '/[\W_]/',
                            'message' => 'Le mot de passe doit contenir au moins un caractère spécial.',
                        ]),
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
