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

/**
 * CRUD autonome pour l'entité `Competence`, généré (probablement via `make:crud`) mais
 * qui semble ne jamais être utilisé dans le parcours applicatif réel : les compétences
 * sont en pratique créées/éditées via le formulaire embarqué de `PrescriptionType`
 * (`PrescriptionController` fait `new Competence()` directement, voir Observations
 * dans `docs/controllers/gestapp-competence.md`). Aucune route de ce contrôleur n'est
 * référencée en dehors de ses propres vues.
 */
#[Route('/gestapp/competence')]
final class CompetenceController extends AbstractController
{
    /**
     * Liste de toutes les compétences.
     */
    #[Route(name: 'app_gestapp_competence_index', methods: ['GET'])]
    public function index(CompetenceRepository $competenceRepository): Response
    {
        return $this->render('gestapp/competence/index.html.twig', [
            'competences' => $competenceRepository->findAll(),
        ]);
    }

    /**
     * Création d'une compétence autonome (hors flux prescription).
     */
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

    /**
     * Fiche détail d'une compétence.
     */
    #[Route('/{id}', name: 'app_gestapp_competence_show', methods: ['GET'])]
    public function show(Competence $competence): Response
    {
        return $this->render('gestapp/competence/show.html.twig', [
            'competence' => $competence,
        ]);
    }

    /**
     * Édition d'une compétence autonome.
     */
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

    /**
     * Suppression d'une compétence autonome.
     */
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
