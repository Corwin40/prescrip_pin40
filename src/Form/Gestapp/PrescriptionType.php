<?php

namespace App\Form\Gestapp;

use App\Entity\Admin\Member;
use App\Entity\Admin\Structure;
use App\Entity\Gestapp\Beneficiary;
use App\Entity\Gestapp\Competence;
use App\Entity\Gestapp\Equipment;
use App\Entity\Gestapp\Prescription;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\RequestStack;

class PrescriptionType extends AbstractType
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
        $prescription = $options['prescription'];

        $builder
            ->add('objectName', TextType::class, [
                'label' => 'Objet de la prescription',
                'required' => false
            ])
            ->add('details', TextareaType::class, [
                'label' => 'A completer par le prescripteur',
            ])
            ->add('baseCompetence', ChoiceType::class, [
                'label' => 'Compétences de base',
                'choices' => [
                    'Acquises' => 'Acquises',
                    'À vérifier' => 'A verifier',
                    'Non acquises' => 'Non acquises',
                ],
                'placeholder' => 'Veuillez choisir',
                'required' => true,
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('lieuMediation', EntityType::class, [
                'class' => Structure::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er
                        ->createQueryBuilder('l')
                        ->innerJoin('l.members', 'm')
                        ->where('m.roles LIKE :roles')
                        ->setParameter('roles', '%ROLE_MEDIATEUR%')
                        ->orderBy('m.id', 'ASC');
                },
            ])
        ;

        // En mode Création de la prescription
        if ($route === 'app_gestapp_prescription_edit') {
            // on filtre les bénéficiaires selon le prescripteur
            if($user && in_array('ROLE_PRESCRIPTEUR', $user->getRoles())) {
                $builder
                    ->add('beneficiaire', EntityType::class, [
                        'class' => Beneficiary::class,
                        'choice_label' => function ($beneficiary) {
                            return $beneficiary->getFirstname() . ' ' . $beneficiary->getLastname();
                        },
                        'query_builder' => function (EntityRepository $er) use ($user) {
                            return $er->createQueryBuilder('b')
                                ->leftJoin('b.prescription', 'p')
                                ->leftJoin('b.structure', 's')
                                //->join('s.members', 'm')
                                ->where('s.id = :idStructure')
                                ->setParameter('idStructure', $user->getStructure())
                                ->andWhere('p.id IS NULL')
                                ->orderBy('b.id', 'ASC');
                        },
                    ])
                ;
            }
            elseif ($user && in_array('ROLE_MEDIATEUR', $user->getRoles())) {
                // On, filtre les bénficiaire selon le mediateur
                $builder
                    ->add('beneficiaire', EntityType::class, [
                        'class' => Beneficiary::class,
                        'choice_label' => function ($beneficiary) {
                            return $beneficiary->getFirstname() . ' ' . $beneficiary->getLastname();
                        },
                        'query_builder' => function (EntityRepository $er) use ($user) {
                            return $er->createQueryBuilder('b')
                                ->leftJoin('b.prescription', 'p')
                                ->leftJoin('b.structure', 's')
                                ->join('s.members', 'm')
                                ->join('m.referent', 'r')
                                ->where('r.id = :idreferent')
                                ->setParameter('idreferent', $user)
                                ->andWhere('p.id IS NULL')
                                ->orderBy('b.id', 'ASC')
                                ;
                        },
                    ])
                ;
            }
            // on prends tous les bénéficiaires
            else{
                $builder
                    ->add('beneficiaire', EntityType::class, [
                        'class' => Beneficiary::class,
                        'choice_label' => function ($beneficiary) {
                            return $beneficiary->getFirstname() . ' ' . $beneficiary->getLastname();
                        },
                        'query_builder' => function (EntityRepository $er) use ($user) {
                            return $er->createQueryBuilder('b')
                                ->leftJoin('b.prescription', 'p')
                                ->andWhere('p.id IS NULL')
                                ->orderBy('b.id', 'ASC');
                        },
                    ])
                ;
            }

            if(in_array($prescription->getStep()->name, ['Open', 'OneParts', 'TwoParts'])){
                if ( $user && (
                        in_array('ROLE_SUPER_ADMIN', $user->getRoles()) ||
                        in_array('ROLE_ADMIN', $user->getRoles()) ||
                        in_array('ROLE_MEDIATEUR', $user->getRoles())
                    ))
                {
                    $builder
                        ->add('prescriptor', EntityType::class, [
                            'class' => Structure::class,
                            'query_builder' => function (EntityRepository $er) {
                                return $er->createQueryBuilder('d')
                                    ->innerJoin('d.members', 'm')
                                    ->where('m.roles LIKE :roles')
                                    ->setParameter('roles', '%ROLE_PRESCRIPTEUR%')
                                    ->orderBy('m.id', 'ASC');
                            },
                        ])
                        ->add('competence', CompetenceType::class, [
                            'label' => 'COMPETENCE',
                            'empty_data' => new Competence(),
                        ])
                        ->add('equipement', HiddenType::class)
                    ;
                }
                elseif ($user && in_array('ROLE_PRESCRIPTEUR', $user->getRoles()))
                {
                    $builder
                        ->add('competence', CompetenceType::class, [
                            'label' => 'COMPETENCE',
                            'empty_data' => new Competence(),
                        ])
                        ->add('equipement', HiddenType::class)
                    ;
                }
            }
            elseif(in_array($prescription->getStep()->name, ['ChoiceEquipment', 'ValidCase', 'GeneratePDF'])){
                if ( $user && (
                        in_array('ROLE_SUPER_ADMIN', $user->getRoles()) ||
                        in_array('ROLE_ADMIN', $user->getRoles()) ||
                        in_array('ROLE_MEDIATEUR', $user->getRoles())
                    ))
                {
                    $builder
                        ->add('Prescriptor', EntityType::class, [
                            'class' => Structure::class,
                            'query_builder' => function (EntityRepository $er) {
                                return $er->createQueryBuilder('d')
                                    ->innerJoin('d.members', 'm')
                                    ->where('m.roles LIKE :roles')
                                    ->setParameter('roles', '%ROLE_PRESCRIPTEUR%')
                                    ->orderBy('m.id', 'ASC');
                            },
                        ])
                        ->add('competence', CompetenceType::class, [
                            'label' => 'COMPETENCE',
                            'empty_data' => new Competence(),
                        ])
                        ->add('equipement', EntityType::class, [
                            'label' => 'Choix de l\'équipement',
                            'class' => Equipment::class,
                            'placeholder' => '-- Choisir un équipement --',
                        ])
                    ;
                }
                elseif ($user && in_array('ROLE_PRESCRIPTEUR', $user->getRoles()))
                {
                    $builder
                        ->add('competence', CompetenceType::class, [
                            'label' => 'COMPETENCE',
                            'empty_data' => new Competence(),
                        ])
                        ->add('equipement', HiddenType::class)
                    ;
                }
            }

        }

        // En mode Edition de la prescription
        if($route === 'app_gestapp_prescription_edit') {
            // on filtre les bénéficiaires selon le prescripteur
            if($user && in_array('ROLE_PRESCRIPTEUR', $user->getRoles())) {
                $builder
                    ->add('beneficiaire', EntityType::class, [
                        'class' => Beneficiary::class,
                        'choice_label' => function ($beneficiary) {
                            return $beneficiary->getFirstname() . ' ' . $beneficiary->getLastname();
                        },
                        'query_builder' => function (EntityRepository $er) use ($user) {
                            return $er->createQueryBuilder('b')
                                ->leftJoin('b.prescription', 'p')
                                ->leftJoin('b.structure', 's')
                                //->join('s.members', 'm')
                                ->where('s.id = :idStructure')
                                ->setParameter('idStructure', $user->getStructure())
                                ->andWhere('p.id IS NULL')
                                ->orderBy('b.id', 'ASC');
                        },
                        'disabled' => true,
                    ])
                ;
            }
            elseif ($user && in_array('ROLE_MEDIATEUR', $user->getRoles())) {
                // On, filtre les bénficiaire selon le mediateur
                $builder
                    ->add('beneficiaire', EntityType::class, [
                        'class' => Beneficiary::class,
                        'choice_label' => function ($beneficiary) {
                            return $beneficiary->getFirstname() . ' ' . $beneficiary->getLastname();
                        },
                        'query_builder' => function (EntityRepository $er) use ($user) {
                            return $er->createQueryBuilder('b')
                                ->leftJoin('b.prescription', 'p')
                                ->leftJoin('b.structure', 's')
                                ->join('s.members', 'm')
                                ->join('m.referent', 'r')
                                ->where('r.id = :idreferent')
                                ->setParameter('idreferent', $user)
                                ->andWhere('p.id IS NULL')
                                ->orderBy('b.id', 'ASC')
                                ;
                        },
                        'disabled' => true,
                    ])
                ;
            }
            // on prends tous les bénéficiaires
            else{
                $builder
                    ->add('beneficiaire', EntityType::class, [
                        'class' => Beneficiary::class,
                        'choice_label' => function ($beneficiary) {
                            return $beneficiary->getFirstname() . ' ' . $beneficiary->getLastname();
                        },
                        'query_builder' => function (EntityRepository $er) use ($user) {
                            return $er->createQueryBuilder('b')
                                ->leftJoin('b.prescription', 'p')
                                ->andWhere('p.id IS NULL')
                                ->orderBy('b.id', 'ASC');
                        },
                        'disabled' => true,
                    ])
                ;
            }

            if(in_array($prescription->getStep()->name, ['Open', 'OneParts', 'TwoParts'])){
                if ( $user && (
                        in_array('ROLE_SUPER_ADMIN', $user->getRoles()) ||
                        in_array('ROLE_ADMIN', $user->getRoles()) ||
                        in_array('ROLE_MEDIATEUR', $user->getRoles())
                    ))
                {
                    $builder
                        ->add('prescriptor', EntityType::class, [
                            'class' => Structure::class,
                            'query_builder' => function (EntityRepository $er) {
                                return $er->createQueryBuilder('d')
                                    ->innerJoin('d.members', 'm')
                                    ->where('m.roles LIKE :roles')
                                    ->setParameter('roles', '%ROLE_PRESCRIPTEUR%')
                                    ->orderBy('m.id', 'ASC');
                            },
                        ])
                        ->add('competence', CompetenceType::class, [
                            'label' => 'COMPETENCE',
                        ])
                        ->add('equipement', HiddenType::class)
                    ;
                }
                elseif ($user && in_array('ROLE_PRESCRIPTEUR', $user->getRoles()))
                {
                    $builder
                        ->add('competence', CompetenceType::class, [
                            'label' => 'COMPETENCE',
                            'empty_data' => new Competence(),
                        ])
                        ->add('equipement', HiddenType::class)
                    ;
                }
            }

            elseif(in_array($prescription->getStep()->name, ['ChoiceEquipment', 'ValidCase'])){
                if ( $user && (
                        in_array('ROLE_SUPER_ADMIN', $user->getRoles()) ||
                        in_array('ROLE_ADMIN', $user->getRoles()) ||
                        in_array('ROLE_MEDIATEUR', $user->getRoles())
                    ))
                {
                    $builder
                        ->add('prescriptor', EntityType::class, [
                            'class' => Structure::class,
                            'query_builder' => function (EntityRepository $er) {
                                return $er->createQueryBuilder('d')
                                    ->innerJoin('d.members', 'm')
                                    ->where('m.roles LIKE :roles')
                                    ->setParameter('roles', '%ROLE_PRESCRIPTEUR%')
                                    ->orderBy('m.id', 'ASC');
                            },
                        ])
                        ->add('competence', CompetenceType::class, [
                            'label' => 'COMPETENCE',
                        ])
                        ->add('equipement', EntityType::class, [
                            'label' => 'Choix de l\'équipement',
                            'class' => Equipment::class,
                            'placeholder' => '-- Choisir un équipement --',
                        ])
                    ;
                }
                elseif ($user && in_array('ROLE_PRESCRIPTEUR', $user->getRoles()))
                {
                    $builder
                        ->add('competence', CompetenceType::class, [
                            'label' => 'COMPETENCE',
                            'empty_data' => new Competence(),
                        ])
                        ->add('equipement', HiddenType::class)
                    ;
                }
            }

            // Boucle sur les étapes ou il n'est palus nécéssaire de modifier les champs
            elseif(in_array($prescription->getStep()->name, ['GeneratePDF', 'Signed'])){
                if ( $user && (
                        in_array('ROLE_SUPER_ADMIN', $user->getRoles()) ||
                        in_array('ROLE_MEDIATEUR', $user->getRoles())
                    ))
                {
                    $builder
                        ->add('prescriptor', HiddenType::class)
                        ->add('competence', HiddenType::class)
                        ->add('equipement', HiddenType::class)
                    ;
                }
                elseif ($user && in_array('ROLE_ADMIN', $user->getRoles())){
                    $builder
                        ->add('prescriptor', HiddenType::class)
                        ->add('competence', HiddenType::class)
                        ->add('equipement', HiddenType::class)
                    ;
                }
                elseif ($user && in_array('ROLE_PRESCRIPTEUR', $user->getRoles()))
                {
                    $builder
                        ->add('competence', HiddenType::class)
                        ->add('equipement', HiddenType::class)
                    ;
                }
            }
        }

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Prescription::class,
            'user' => null,
            'prescription' => null,
        ]);
    }
}
