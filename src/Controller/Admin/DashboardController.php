<?php

namespace App\Controller\Admin;

use App\Repository\Gestapp\BeneficiaryRepository;
use App\Repository\Gestapp\EquipmentRepository;
use App\Repository\Gestapp\PrescriptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_admin_dashboard_index', methods: ['GET'])]
    public function index(EquipmentRepository $equipmentRepository, PrescriptionRepository $prescriptionRepository, BeneficiaryRepository $beneficiaryRepository): Response
    {
        $user = $this->getUser();
        $roles = $user->getRoles();

        $equipments = $equipmentRepository->findAll();
        $prescriptions = $prescriptionRepository->findAll();
        $beneficiaries = $beneficiaryRepository->findAll();

        return $this->render('admin/login_dashboard/index.html.twig', [
            'equipments' => $equipments,
            'prescriptions' => $prescriptions,
            'beneficiaries' => $beneficiaries
        ]);
    }
}
