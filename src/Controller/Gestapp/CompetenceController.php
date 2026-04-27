<?php

namespace App\Controller\Gestapp;

use App\Entity\Gestapp\Competence;
use App\Form\Gestapp\CompetenceType;
use App\Repository\Gestapp\CompetenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/gestapp/competence')]
final class CompetenceController extends AbstractController
{
    #[Route(name: 'app_gestapp_competence_index', methods: ['GET'])]
    public function index(CompetenceRepository $competenceRepository): Response
    {
        return $this->render('gestapp/competence/index.html.twig', [
            'competences' => $competenceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_gestapp_competence_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $competence = new Competence();
        $form = $this->createForm(CompetenceType::class, $competence, [
            'action' => $this->generateUrl('app_gestapp_competence_new'),
            'method' => 'POST',
            'attr' => [
                'id' => 'formCompetence',
            ]
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($competence);
            $entityManager->flush();

            return $this->redirectToRoute('app_gestapp_competence_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gestapp/competence/_form.html.twig', [
            'competence' => $competence,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_gestapp_competence_show', methods: ['GET'])]
    public function show(Competence $competence): Response
    {
        return $this->render('gestapp/competence/show.html.twig', [
            'competence' => $competence,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_gestapp_competence_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Competence $competence, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CompetenceType::class, $competence, [
            'action' => $this->generateUrl('app_gestapp_competence_edit', ['id' => $competence->getId()]),
            'method' => 'POST',
            'attr' => [
                'id' => 'formCompetence',
            ]
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_gestapp_competence_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gestapp/competence/_form.html.twig', [
            'competence' => $competence,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_gestapp_competence_delete', methods: ['POST'])]
    public function delete(Request $request, Competence $competence, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$competence->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($competence);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_gestapp_competence_index', [], Response::HTTP_SEE_OTHER);
    }
}
