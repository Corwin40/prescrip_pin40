<?php

namespace App\Controller\Gestapp;

use App\Entity\Gestapp\Beneficiary;
use App\Form\Gestapp\BeneficiaryType;
use App\Repository\Gestapp\BeneficiaryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/gestapp/beneficiary')]
final class BeneficiaryController extends AbstractController
{
    #[Route('/', name: 'app_gestapp_beneficiary_index', methods: ['GET'])]
    public function index(
        Request $request,
        BeneficiaryRepository $beneficiaryRepository,
        PaginatorInterface $paginator
    ): Response {

        $search = $request->query->get('search');

        // TRI DESC → les plus récents en premier
        $qb = $beneficiaryRepository->createQueryBuilder('b')
            ->orderBy('b.createdAt', 'DESC');

        if (!empty($search)) {
            $qb->andWhere('b.firstname LIKE :search
                           OR b.lastname LIKE :search
                           OR b.civility LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $pagination = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('gestapp/beneficiary/index.html.twig', [
            'pagination' => $pagination,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'app_gestapp_beneficiary_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $beneficiary = new Beneficiary();
        $form = $this->createForm(BeneficiaryType::class, $beneficiary);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $civility = $form->get('civility')->getData();
            $beneficiary->setGender($civility);

            $entityManager->persist($beneficiary);
            $entityManager->flush();

            return $this->redirectToRoute('app_gestapp_beneficiary_index');
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
                'id' => 'formBenficiary',
            ]
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $civility = $form->get('civility')->getData();
            $beneficiary->setGender($civility);

            $entityManager->persist($beneficiary);
            $entityManager->flush();

            return $this->json([
                'message' => 'le beneficiaire est ajouté au formulaire de prescription.',
                'beneficiaire' => $beneficiary->getFirstname() . ' ' . $beneficiary->getLastname(),
                'value' => $beneficiary->getId(),
            ], 200);
        }

        return $this->json([
            'message' => 'le formulaire est livré.',
            'formView' => $this->renderView('gestapp/beneficiary/_form.html.twig', [
                'beneficiary' => $beneficiary,
                'form' => $form,
            ])
        ], 200);
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

            return $this->redirectToRoute('app_gestapp_beneficiary_index');
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

            return $this->redirectToRoute('app_gestapp_beneficiary_index');
        }

        return $this->render('gestapp/beneficiary/_form.html.twig', [
            'beneficiary' => $beneficiary,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_gestapp_beneficiary_delete', methods: ['POST'])]
    public function delete(Request $request, Beneficiary $beneficiary, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $beneficiary->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($beneficiary);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_gestapp_beneficiary_index');
    }
}
