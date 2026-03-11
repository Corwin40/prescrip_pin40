<?php

namespace App\Controller\Gestapp;

use App\Entity\Gestapp\Beneficiary;
use App\Form\Search\BeneficiarySearchType;
use App\Form\Gestapp\BeneficiaryType;
use App\Repository\Gestapp\BeneficiaryRepository;
use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\MultiMatch;
use Elastica\Query\Term;
use Elastica\Query\Nested;

#[Route('/gestapp/beneficiary')]
final class BeneficiaryController extends AbstractController
{
    private $finder;

    public function __construct(PaginatedFinderInterface $finder)
    {
        $this->finder = $finder;
    }

    #[Route('/search/', name: 'app_gestapp_beneficiary_search', methods: ['GET','POST'])]
    public function search(Request $request, MemberRepository $repository): Response {
        // récupérer tous les prescripteurs existants (exemple Doctrine)
        $prescriptors = $repository->findByRole('ROLE_PRESCRIPTEUR');

        $prescriptorChoices = [];
        foreach ($prescriptors as $p) {
            $prescriptorChoices[$p['nameStructure']] = $p['nameStructure'];
        }

        $form = $this->createForm(BeneficiarySearchType::class, null, [
            'prescriptors' => $prescriptorChoices
        ]);
        $form->handleRequest($request);

        // construire la query Elastice
        $boolQuery = new BoolQuery();

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (!empty($data['query'])) {
                $multiMatch = new MultiMatch();
                $multiMatch->setFields(['firstName', 'lastName']);
                $multiMatch->setQuery($data['query']);
                $boolQuery->addMust($multiMatch);
            }

            if (!empty($data['prescriptor'])) {
                $nestedQuery = new Nested();
                $nestedQuery->setPath('prescriptor');

                $termQuery = new Term();
                $termQuery->setTerm('prescriptor.nameStructure', $data['prescriptor']);
                $nestedQuery->setQuery($termQuery);
                $boolQuery->addFilter($nestedQuery);
            }

        }

        $query = new Query($boolQuery);
        $query->setSize(50); // max 50 résultats, ajustable

        $results = $this->finder->find($query);

        return $this->render('gestapp/beneficiary/search.html.twig', [
            'form' => $form->createView(),
            'results' => $results,
        ]);
    }

    #[Route(name: 'app_gestapp_beneficiary_index', methods: ['GET'])]
    public function index(BeneficiaryRepository $beneficiaryRepository): Response
    {
        $member = $this->getUser();
        if($member && in_array('ROLE_PRESCRIPTEUR', $member->getRoles())){
            $beneficiaries = $beneficiaryRepository->findBy(['prescriptor' => $member]);
        }
        if($member && in_array('ROLE_MEDIATEUR', $member->getRoles())){
            $beneficiaries = $beneficiaryRepository->findByMediation(['lieuMediation' => $member]);
        }
        if($member && in_array('ROLE_SUPER_ADMIN', $member->getRoles())){
            $beneficiaries = $beneficiaryRepository->findAll();
        }

        return $this->render('gestapp/beneficiary/index.html.twig', [
            'beneficiaries' => $beneficiaries,
        ]);
    }

    #[Route('/new', name: 'app_gestapp_beneficiary_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $beneficiary = new Beneficiary();
        $form = $this->createForm(BeneficiaryType::class, $beneficiary);
        $form->handleRequest($request);
        $beneficiary->setPrescriptor($user);

        if ($form->isSubmitted() && $form->isValid()) {

            $civility = $form->get('civility')->getData();

            $beneficiary->setGender($civility);

            $entityManager->persist($beneficiary);
            $entityManager->flush();

            return $this->redirectToRoute('app_gestapp_beneficiary_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gestapp/beneficiary/new.html.twig', [
            'beneficiary' => $beneficiary,
            'form' => $form,
        ]);
    }

    #[Route('/new2', name: 'app_gestapp_beneficiary_new2', methods: ['GET', 'POST'])]
    public function new2(Request $request, EntityManagerInterface $entityManager): Response
    {
        $beneficiary = new Beneficiary();
        $form = $this->createForm(BeneficiaryType::class, $beneficiary, [
            'action' => $this->generateUrl('app_gestapp_beneficiary_new2'),
            'method' => 'POST',
            'attr' => [
                'id' => 'formBeneficiary',
            ]
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $civility = $form->get('civility')->getData();

            $beneficiary->setGender($civility);
            $entityManager->persist($beneficiary);
            $entityManager->flush();

            return $this->json([
                'message' => 'le béneficiaire est ajouté au formulaire de prescription.',
                'beneficiaire' => $beneficiary->getFirstname() . ' ' . $beneficiary->getLastname(),
                'value' => $beneficiary->getId(),
            ],200);
        }

        return $this->json([
            'message' => 'le formulaire est livré.',
            'formView' => $this->renderView('gestapp/beneficiary/_form2.html.twig', [
                'beneficiary' => $beneficiary,
                'form' => $form,
            ])
        ],200);
    }

    #[Route('/{id}', name: 'app_gestapp_beneficiary_show', methods: ['GET'])]
    public function show(Beneficiary $beneficiary): Response
    {
        return $this->render('gestapp/beneficiary/show.html.twig', [
            'beneficiary' => $beneficiary,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_gestapp_beneficiary_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Beneficiary $beneficiary, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BeneficiaryType::class, $beneficiary, [
            'action' => $this->generateUrl('app_gestapp_beneficiary_edit', ['id' => $beneficiary->getId()]),
            'method' => 'POST',
            'attr' => [
                'id' => 'formBenficiary',
            ]
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_gestapp_beneficiary_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gestapp/beneficiary/edit.html.twig', [
            'beneficiary' => $beneficiary,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit2', name: 'app_gestapp_beneficiary_edit2', methods: ['GET', 'POST'])]
    public function edit2(Request $request, Beneficiary $beneficiary, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BeneficiaryType::class, $beneficiary, [
            'action' => $this->generateUrl('app_gestapp_beneficiary_edit', ['id' => $beneficiary->getId()]),
            'method' => 'POST',
            'attr' => [
                'id' => 'formBenficiary',
            ]
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_gestapp_beneficiary_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gestapp/beneficiary/_form.html.twig', [
            'beneficiary' => $beneficiary,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_gestapp_beneficiary_delete', methods: ['POST'])]
    public function delete(Request $request, Beneficiary $beneficiary, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$beneficiary->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($beneficiary);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_gestapp_beneficiary_index', [], Response::HTTP_SEE_OTHER);
    }
}
