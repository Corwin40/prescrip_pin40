<?php

namespace App\Controller\Admin;

use App\Repository\Gestapp\EquipmentRepository;
use App\Repository\Gestapp\PrescriptionRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_admin_dashboard_index', methods: ['GET'])]
    public function index(
        EquipmentRepository $equipmentRepository,
        PrescriptionRepository $prescriptionRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {

        //  Récupération du filtre structure
        $filterStructure = $request->query->get('structure');

        //  Query de base
        $queryBuilder = $prescriptionRepository->createQueryBuilder('p')
            ->leftJoin('p.membre', 'm')
            ->addSelect('m')
            ->orderBy('p.createdAt', 'DESC'); // tri du plus récent au plus ancien

        //  Application du filtre structure
        if (!empty($filterStructure)) {
            $queryBuilder
                ->andWhere('m.nameStructure = :structure')
                ->setParameter('structure', $filterStructure);
        }

        //  Pagination sur 5 éléments
        $prescriptions = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            5
        );

        $equipments = $equipmentRepository->findAll();

        return $this->render('admin/login_dashboard/index.html.twig', [
            'equipments' => $equipments,
            'prescriptions' => $prescriptions,
            'current_structure' => $filterStructure
        ]);
    }
}
