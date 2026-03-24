<?php

namespace App\Form\Gestapp;

use App\Entity\Admin\Member;
use App\Entity\Gestapp\Beneficiary;
use App\Entity\Gestapp\Competence;
use App\Entity\Gestapp\Equipment;
use App\Entity\Gestapp\Prescription;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
            ->add('details')
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
                'class' => Member::class,
                'choice_label' => 'nameStructure',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->where('d.roles LIKE :roles')
                        ->setParameter('roles', '%ROLE_MEDIATEUR%')
                        ->orderBy('d.id', 'ASC');
                },
            ])
        ;

        if(in_array($prescription->getStep()->name, ['Open', 'OneParts', 'TwoParts'])){
            if ( $user && (
                    in_array('ROLE_SUPER_ADMIN', $user->getRoles()) ||
                    in_array('ROLE_ADMIN', $user->getRoles()) ||
                    in_array('ROLE_MEDIATEUR', $user->getRoles())
                ))
            {
                $builder
                    ->add('membre', EntityType::class, [
                        'class' => Member::class,
                        'choice_label' => 'nameStructure',
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('d')
                                ->where('d.roles LIKE :roles')
                                ->setParameter('roles', '%ROLE_PRESCRIPTEUR%')
                                ->orderBy('d.id', 'ASC');
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
                    ->add('membre', EntityType::class, [
                        'class' => Member::class,
                        'choice_label' => 'nameStructure',
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('d')
                                ->where('d.roles LIKE :roles')
                                ->setParameter('roles', '%ROLE_PRESCRIPTEUR%')
                                ->orderBy('d.id', 'ASC');
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
                                ->where('b.prescriptor = :prescriptor')
                                ->setParameter('prescriptor', $user)
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
                                ->leftJoin('b.prescriptor', 'm')
                                ->join('m.referent', 'r')
                                ->where('r.id = :member')
                                ->setParameter('member', $user)
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

        }
        if($route === 'app_gestapp_prescription_edit') {
            $builder
                ->add('beneficiaire')
            ;
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
