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

#[Route('/gestapp/beneficiary')]
final class BeneficiaryController extends AbstractController
{
    #[Route(name: 'app_gestapp_beneficiary_index', methods: ['GET'])]
    public function index(BeneficiaryRepository $beneficiaryRepository): Response
    {
        return $this->render('gestapp/beneficiary/index.html.twig', [
            'beneficiaries' => $beneficiaryRepository->findAll(),
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
        $form = $this->createForm(BeneficiaryType::class, $beneficiary);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $civility = $form->get('civility')->getData();

            $beneficiary->setGender($civility);
            $entityManager->persist($beneficiary);
            $entityManager->flush();

            return $this->redirectToRoute('app_gestapp_beneficiary_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->json([
            'message' => 'le formulaire est livré.',
            'formView' => $this->renderView('gestapp/beneficiary/_form.html.twig', [
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
        $form = $this->createForm(BeneficiaryType::class, $beneficiary);
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
        $form = $this->createForm(BeneficiaryType::class, $beneficiary);
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
