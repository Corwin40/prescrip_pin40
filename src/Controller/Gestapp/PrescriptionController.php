<?php

namespace App\Controller\Gestapp;

use App\Entity\Gestapp\Competence;
use App\Entity\Gestapp\Prescription;
use App\Form\Gestapp\PrescriptionType;
use App\Repository\Gestapp\PrescriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/gestapp/prescription')]
final class PrescriptionController extends AbstractController
{
    #[Route('/', name: 'app_gestapp_prescription_index', methods: ['GET'])]
    public function index(
        Request $request,
        PrescriptionRepository $prescriptionRepository,
        PaginatorInterface $paginator
    ): Response {

        $member = $this->getUser();
        $search = $request->query->get('search');

        // QueryBuilder de base, limité au membre connecté
        // tri du plus récent au plus ancien
        $qb = $prescriptionRepository->createQueryBuilder('p')
            ->where('p.membre = :membre')
            ->setParameter('membre', $member)
            ->orderBy('p.createdAt', 'DESC');  // 🔥 TRI DESC ICI

        // rechercher par reference
        if (!empty($search)) {
            $qb->andWhere('p.ref LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // pour afficher 5 prescription par page
        $pagination = $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('gestapp/prescription/index.html.twig', [
            'pagination' => $pagination,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'app_gestapp_prescription_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        PrescriptionRepository $prescriptionRepository
    ): Response {
        // Construction de la variable Ref
        $date = new \DateTime('now');
        $structure = $this->getUser()->getNameStructure();

        // Dernière prescription du membre courant
        $lastPrescription = $prescriptionRepository->findOneBy(
            ['membre' => $this->getUser()],
            ['id' => 'DESC']
        );

        $compteur = $lastPrescription ? $lastPrescription->getCompteur() + 1 : 1;

        $ref = $date->format('mY') . "-" . $structure . "-" . $compteur; // mois-année-structure-compteur

        $prescription = new Prescription();
        $prescription->setRef($ref);
        $prescription->setCompteur($compteur);
        $prescription->setCompetence(new Competence());

        $form = $this->createForm(PrescriptionType::class, $prescription, [
            'user' => $this->getUser(),
            'attr' => [
                'id' => 'formPrescription',
            ],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Ajout du membre dans la prescription
            $prescription->setMembre($this->getUser());

            $entityManager->persist($prescription);
            $entityManager->flush();

            return $this->redirectToRoute('app_gestapp_prescription_index');
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
    public function edit(
        Request $request,
        Prescription $prescription,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(PrescriptionType::class, $prescription, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_gestapp_prescription_index');
        }

        return $this->render('gestapp/prescription/edit.html.twig', [
            'prescription' => $prescription,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/closecase', name: 'app_gestapp_prescription_closecase', methods: ['POST'])]
    public function closecase(Prescription $prescription, EntityManagerInterface $entityManager): Response
    {
        $prescription->setValidcase(1);
        $entityManager->flush();

        return $this->json([
            'code' => 200,
            'message' => 'Le fichier PDF correspondant à la prescription est en cours de génération',
            'prescription' => $prescription,
        ], 200);
    }

    #[Route('/{id}', name: 'app_gestapp_prescription_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Prescription $prescription,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $prescription->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($prescription);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_gestapp_prescription_index');
    }
    #[Route('/test500')]
    public function test500()
    {
        throw new \Exception("Erreur de test 500 OK !");
    }
}
