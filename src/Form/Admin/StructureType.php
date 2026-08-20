<?php

namespace App\Form\Admin;

use App\Entity\Admin\Member;
use App\Entity\Admin\Structure;
use App\Entity\Gestapp\Competence;
use App\Form\Gestapp\CompetenceType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StructureType extends AbstractType
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $route = $request?->attributes->get('_route');
        $structure = $options['structure'];

        $builder
            ->add('name', TextType::class,[
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Entrez le nom',
                ],
            ])
            ->add('address', TextType::class,[
                'label' => 'Adresse',
                'attr' => [
                    'placeholder' => 'Entrez l\'adresse',
                ],
            ])
            ->add('zipcode', TextType::class,[
                'label' => 'CP',
                'attr' => [
                    'placeholder' => 'CP',
                ],
            ])
            ->add('city', TextType::class,[
                'label' => 'Commune',
                'attr' => [
                    'placeholder' => 'Commune',
                ],
            ])
            ->add('contactEmail', TextType::class,[
                'label' => 'Email',
            ])
            ->add('contactPhone', TextType::class,[
                'label' => 'Téléphone',
            ])
            ->add('lieuMediationPartner', EntityType::class, [
                'label' => 'Lieu de médiation partenaire',
                'class' => Structure::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Aucun',
                'query_builder' => function (EntityRepository $er) use ($structure) {
                    $qb = $er->createQueryBuilder('l')
                        ->innerJoin('l.members', 'm')
                        ->where('m.roles LIKE :roles')
                        ->setParameter('roles', '%ROLE_MEDIATEUR%')
                        ->orderBy('l.name', 'ASC');

                    if ($structure && $structure->getId()) {
                        $qb->andWhere('l != :self')
                            ->setParameter('self', $structure);
                    }

                    return $qb;
                },
            ])
        ;

        if ($route === 'app_admin_structure_new') {
            $builder
                ->add('member', MemberType::class, [
                    'label' => 'Le membre référent',
                    'mapped' => false,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Structure::class,
            'structure' => null,
        ]);
    }
}
