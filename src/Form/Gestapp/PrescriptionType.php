<?php

namespace App\Form\Gestapp;

use App\Entity\Admin\Member;
use App\Entity\Gestapp\Beneficiary;
use App\Entity\Gestapp\Equipment;
use App\Entity\Gestapp\Prescription;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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

        $builder
            ->add('equipement', EntityType::class, [
                'class' => Equipment::class,
                'choice_label' => 'id',
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
            ->add('cp')
            ->add('commune')
            ->add('validcase')
            ;

        if ($route == 'gestapp_prescription_create') {
            $builder
                ->add('beneficiaire', EntityType::class, [
                    'class' => Beneficiary::class,
                    'choice_label' => function ($beneficiary) {
                        return $beneficiary->getFirstname() . ' ' . $beneficiary->getLastname();
                    },
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('b')
                            ->leftJoin('b.prescription', 'p')
                            ->where('p.id IS NULL')
                            ->orderBy('b.id', 'ASC');
                    },
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Prescription::class,
        ]);
    }
}
