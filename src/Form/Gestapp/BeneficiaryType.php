<?php

namespace App\Form\Gestapp;

use App\Entity\Admin\Member;
use App\Entity\Admin\Structure;
use App\Entity\Gestapp\Beneficiary;
use App\Entity\Gestapp\Prescription;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class BeneficiaryType extends AbstractType
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

        $user = $options['user'];

        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
                'attr' => ['placeholder' => 'Prénom']
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'attr' => ['placeholder' => 'Nom']
            ])
            ->add('civility', ChoiceType::class, [
                'label' => 'Civilité',
                'choices' => [
                    'Mr' => 'Mr',
                    'Mme' => 'Mme',
                    'Mlle' => 'Mlle',
                    'Autre' => 'Autre',
                ],
                'placeholder' => 'veuillez choisir',
                'required' => true,
            ])
            ->add('ageGroup', ChoiceType::class, [
                'label' => 'Tranche d’âge',
                'choices' => [
                    '15 ans et moins' => '0-15',
                    '16 à 25 ans' => '16-25',
                    '26 à 59 ans' => '26-59',
                    '60 ans et plus' => '60+',
                ],
                'expanded' => true,
                'multiple' => false,
                'placeholder' => 'Veuillez choisir',
                'required' => true,
            ])

            ->add('professionnalStatus', ChoiceType::class, [
                'label' => 'Statut professionnel',
                'choices' => [
                    'Scolaire / Étudiant' => 'Scolaire / Étudiant',
                    'En emploi ou en formation' => 'Employé / En Formation',
                    'Sans emploi' => 'Sans emploi',
                    'Retraité' => 'Retraite',
                    'Autre situation' => 'Autre situation',
                ],
                'expanded' => true,
                'multiple' => false,
                'placeholder' => 'veuillez choisir',
                'required' => true,
            ]);
        if ($route === 'app_gestapp_beneficiary_new') {
            if($user && in_array('ROLE_PRESCRIPTEUR', $user->getRoles())) {
                $builder->add('structure', EntityType::class, [
                    'class' => Structure::class,
                    'choices' => [$user->getStructure()],
                    'choice_label' => 'name',
                ]);
            }
            elseif ($user && in_array('ROLE_MEDIATEUR', $user->getRoles())) {
                $builder
                    ->add('structure', EntityType::class, [
                        'class' => Structure::class,
                        'choice_label' => function ($structure) {
                            return $structure->getName();
                        },
                        'query_builder' => function (EntityRepository $er) use ($user) {
                            return $er
                                ->createQueryBuilder('s')
                                ->leftJoin('s.members', 'p')
                                ->where('p.referent = :user')
                                ->setParameter('user', $user)
                                ->orderBy('s.name', 'ASC');
                        },
                    ]);
            }
            elseif ( $user && (
                    in_array('ROLE_SUPER_ADMIN', $user->getRoles()) ||
                    in_array('ROLE_ADMIN', $user->getRoles())
                ))
            {
                $builder
                    ->add('structure', EntityType::class, [
                        'class' => Structure::class,
                        'choice_label' => function ($structure) {
                            return $structure->getName();
                        },
                        'query_builder' => function (EntityRepository $er) use ($user) {
                            return $er
                                ->createQueryBuilder('s')
                                ->innerJoin('s.members', 'p')
                                ->where('p.roles LIKE :roles')
                                ->setParameter('roles', '%ROLE_PRESCRIPTEUR%')
                                ->orderBy('p.id', 'ASC');
                        },
                    ]);
            }
        }

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Beneficiary::class,
            'user' => null,
            'beneficiary' => null,
        ]);
    }
}
