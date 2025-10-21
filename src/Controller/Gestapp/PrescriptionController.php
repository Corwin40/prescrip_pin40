<?php

namespace App\Controller\Gestapp;

use App\Entity\Gestapp\Prescription;
use App\Form\Gestapp\PrescriptionType;
use App\Repository\Gestapp\PrescriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/gestapp/prescription')]
final class PrescriptionController extends AbstractController
{
    #[Route(name: 'app_gestapp_prescription_index', methods: ['GET'])]
    public function index(PrescriptionRepository $prescriptionRepository): Response
    {
        $member = $this->getUser();
        $prescriptions = $prescriptionRepository->findBy(['membre' => $member]);

        return $this->render('gestapp/prescription/index.html.twig', [
            'prescriptions' => $prescriptions,
        ]);
    }

    #[Route('/new', name: 'app_gestapp_prescription_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $prescription = new Prescription();

        $ref = // mois-année-strucutre-compteur

        $form = $this->createForm(PrescriptionType::class, $prescription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Ajout du membre dans la prescription
            $prescription->setMembre($this->getUser());

            $entityManager->persist($prescription);
            $entityManager->flush();

            return $this->redirectToRoute('app_gestapp_prescription_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gestapp/prescription/new.html.twig', [
            'prescription' => $prescription,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_gestapp_prescription_show', methods: ['GET'])]
    public function show(Prescription $prescription): Response
    {
        return $this->render('gestapp/prescription/show.html.twig', [
            'prescription' => $prescription,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_gestapp_prescription_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Prescription $prescription, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PrescriptionType::class, $prescription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_gestapp_prescription_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gestapp/prescription/edit.html.twig', [
            'prescription' => $prescription,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_gestapp_prescription_delete', methods: ['POST'])]
    public function delete(Request $request, Prescription $prescription, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$prescription->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($prescription);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_gestapp_prescription_index', [], Response::HTTP_SEE_OTHER);
    }
}
