<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Member;
use App\Entity\Admin\Structure;
use App\Form\Admin\StructureType;
use App\Repository\Admin\StructureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\UX\Map\Bridge\Leaflet\LeafletOptions;
use Symfony\UX\Map\Bridge\Leaflet\Option\TileLayer;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Point;

/**
 * Gestion des structures (prescripteurs/médiateurs) : liste avec carte, création
 * (structure + membre responsable), édition. Réservé à `ROLE_ADMIN` (couvre aussi
 * `ROLE_SUPER_ADMIN` via la hiérarchie de rôles) pour tout le contrôleur.
 */
#[Route('/admin/structure')]
#[IsGranted('ROLE_ADMIN')]
final class StructureController extends AbstractController
{
    /**
     * Liste des structures avec une carte (Symfony UX Map / Leaflet, centrée sur
     * la région de la structure principale) affichant leur implantation géographique.
     */
    #[Route('/', name: 'app_admin_structure_index')]
    public function index(StructureRepository $structureRepository): Response
    {
        $map = new Map();
        $map
            ->center(new Point(43.88478, -0.50400))
            ->zoom(9)
            ->minZoom(1) // Set the minimum zoom level
            ->maxZoom(10) // Set the maximum zoom level
            ->options((new LeafletOptions())
                ->tileLayer(new TileLayer(
                    url: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                    options: ['maxZoom' => 19]
                ))
            )
        ;

        return $this->render('admin/structure/index.html.twig', [
            'structures' => $structureRepository->findAll(),
            'map' => $map,
        ]);
    }

    /**
     * Création d'une structure ET de son membre responsable en une seule soumission
     * (le formulaire `StructureType` embarque un sous-formulaire membre). Le membre créé
     * reçoit le rôle choisi dans le formulaire et est marqué `isRespStructure = true`.
     */
    #[Route('/new', name: 'app_admin_structure_new', methods: 'GET|POST')]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, SluggerInterface $slugger): Response
    {
        $structure = new Structure();

        $form = $this->createForm(StructureType::class, $structure, [
            'action' => $this->generateUrl('app_admin_structure_new'),
            'method' => 'POST',
            'attr' => [
                'id' => 'formStructure',
            ],
            'structure' => $structure,
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $structure->setSlug($slugger->slug($structure->getName())->lower());

            $entityManager->persist($structure);
            $entityManager->flush();

            /** @var Member $member */
            $member = $form->get('member')->getData();
            $member->setStructure($structure);

            $role = $form->get('member')->get('role')->getData();
            $member->setRoles([$role]);

            $plainPassword = $form->get('member')->get('password')->getData();
            $member->setPassword($passwordHasher->hashPassword($member, $plainPassword));

            $member->setIsRespStructure(true);
            $entityManager->persist($member);
            $entityManager->flush();

            $this->addFlash('success', 'Le membre a bien été créé.');
            return $this->redirectToRoute('app_admin_structure_index');
        }

        // dans la poursuite de la crétaion d'une structure, il faut créer obligatoirement le dossier de stockage des pdfs générés et déposés.

        return $this->render('admin/structure/new.html.twig', [
            'form' => $form,
            'structure' => $structure,
        ]);
    }

    /**
     * Édition d'une structure existante (ne touche pas au membre responsable,
     * contrairement à `new()`).
     */
    #[Route('/{id}/edit', name: 'app_admin_structure_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Structure $structure, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(StructureType::class, $structure, [
            'action' => $this->generateUrl('app_admin_structure_edit', ['id' => $structure->getId()]),
            'method' => 'POST',
            'attr' => [
                'id' => 'formStructure',
            ],
            'structure' => $structure,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $structure->setSlug($slugger->slug($structure->getName())->lower());

            $entityManager->flush();

            $this->addFlash('success', 'La structure a bien été mise à jour.');
            return $this->redirectToRoute('app_admin_structure_index');
        }

        return $this->render('admin/structure/edit.html.twig', [
            'form' => $form,
            'structure' => $structure,
        ]);
    }
}
