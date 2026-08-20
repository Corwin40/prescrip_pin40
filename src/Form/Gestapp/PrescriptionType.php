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
                'query_builder' => function (EntityRepository $er) use ($user) {
                    $qb = $er
                        ->createQueryBuilder('l')
                        ->innerJoin('l.members', 'm')
                        ->where('m.roles LIKE :roles')
                        ->setParameter('roles', '%ROLE_MEDIATEUR%')
                        ->orderBy('m.id', 'ASC');

                    // Un prescripteur n'a qu'un seul lieu de médiation partenaire : on restreint
                    // le choix à celui-ci s'il est défini, pour éviter de proposer tout le réseau.
                    if ($user && in_array('ROLE_PRESCRIPTEUR', $user->getRoles())) {
                        $partner = $user->getStructure()?->getLieuMediationPartner();
                        if ($partner) {
                            $qb->andWhere('l = :partner')
                                ->setParameter('partner', $partner);
                        }
                    }

                    return $qb;
                },
                'disabled' => $route === 'app_gestapp_prescription_edit',
            ])
        ;

        // En mode Création de la prescription
        if ($route === 'app_gestapp_prescription_new') {
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
                                ->where('s.lieuMediationPartner = :userStructure')
                                ->setParameter('userStructure', $user->getStructure())
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
                            'query_builder' => function (EntityRepository $er) {
                                return $er->createQueryBuilder('e')
                                    ->where('e.isDispo = 1')
                                    ->orderBy('e.id', 'ASC');
                            },
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
                        'query_builder' => function (EntityRepository $er) use ($user, $prescription) {
                            $qb = $er->createQueryBuilder('b')
                                ->leftJoin('b.prescription', 'p')
                                ->leftJoin('b.structure', 's')
                                ->where('s.id = :idStructure')
                                ->setParameter('idStructure', $user->getStructure())
                                ->orderBy('b.id', 'ASC');

                            // Le bénéficiaire déjà rattaché à cette prescription doit rester
                            // proposé, même s'il a donc déjà une prescription (celle-ci).
                            if ($prescription && $prescription->getBeneficiaire()) {
                                $qb->andWhere('p.id IS NULL OR b = :currentBeneficiaire')
                                    ->setParameter('currentBeneficiaire', $prescription->getBeneficiaire());
                            } else {
                                $qb->andWhere('p.id IS NULL');
                            }

                            return $qb;
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
                        'query_builder' => function (EntityRepository $er) use ($user, $prescription) {
                            $qb = $er->createQueryBuilder('b')
                                ->leftJoin('b.prescription', 'p')
                                ->leftJoin('b.structure', 's')
                                ->where('s.lieuMediationPartner = :userStructure')
                                ->setParameter('userStructure', $user->getStructure())
                                ->orderBy('b.id', 'ASC')
                                ;

                            // Le bénéficiaire déjà rattaché à cette prescription doit rester
                            // proposé, même s'il a donc déjà une prescription (celle-ci).
                            if ($prescription && $prescription->getBeneficiaire()) {
                                $qb->andWhere('p.id IS NULL OR b = :currentBeneficiaire')
                                    ->setParameter('currentBeneficiaire', $prescription->getBeneficiaire());
                            } else {
                                $qb->andWhere('p.id IS NULL');
                            }

                            return $qb;
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
                        'query_builder' => function (EntityRepository $er) use ($prescription) {
                            $qb = $er->createQueryBuilder('b')
                                ->leftJoin('b.prescription', 'p')
                                ->orderBy('b.id', 'ASC');

                            // Le bénéficiaire déjà rattaché à cette prescription doit rester
                            // proposé, même s'il a donc déjà une prescription (celle-ci).
                            if ($prescription && $prescription->getBeneficiaire()) {
                                $qb->andWhere('p.id IS NULL OR b = :currentBeneficiaire')
                                    ->setParameter('currentBeneficiaire', $prescription->getBeneficiaire());
                            } else {
                                $qb->andWhere('p.id IS NULL');
                            }

                            return $qb;
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
                            'disabled' => true,
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
                            'disabled' => true,
                        ])
                        ->add('competence', CompetenceType::class, [
                            'label' => 'COMPETENCE',
                        ])
                        ->add('equipement', EntityType::class, [
                            'label' => 'Choix de l\'équipement',
                            'class' => Equipment::class,
                            'placeholder' => '-- Choisir un équipement --',
                            'query_builder' => function (EntityRepository $er) use ($prescription) {
                                $qb = $er->createQueryBuilder('e')
                                    ->orderBy('e.id', 'ASC');

                                // L'équipement déjà assigné à cette prescription doit rester
                                // sélectionnable, même s'il n'est donc plus disponible ailleurs.
                                if ($prescription && $prescription->getEquipement()) {
                                    $qb->where('e.isDispo = 1 OR e = :currentEquipement')
                                        ->setParameter('currentEquipement', $prescription->getEquipement());
                                } else {
                                    $qb->where('e.isDispo = 1');
                                }

                                return $qb;
                            },
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
