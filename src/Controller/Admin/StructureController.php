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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Map\Bridge\Leaflet\LeafletOptions;
use Symfony\UX\Map\Bridge\Leaflet\Option\TileLayer;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Point;

#[Route('/admin/structure')]
#[IsGranted('ROLE_ADMIN')]
final class StructureController extends AbstractController
{
    #[Route('/', name: 'app_admin_structure_index')]
    public function index(StructureRepository $structureRepository): Response
    {
        $user = $this->getUser();

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

    #[Route('/new', name: 'app_admin_structure_new', methods: 'GET|POST')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $structure = new Structure();

        $form = $this->createForm(StructureType::class, $structure, [
            'action' => $this->generateUrl('app_admin_structure_new'),
            'method' => 'POST',
            'attr' => [
                'id' => 'formStructure',
            ],
            //'user' => $user,
            //'structure' => $structure
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $entityManager->persist($structure);
            $entityManager->flush();

            $member = new Member();
            $member->setStructure($structure);
            $member->setRoles(['ROLE_PRESCRIPTEUR']);
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
}
