<?php

namespace App\Controller\Admin;

use App\Repository\Admin\StructureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
