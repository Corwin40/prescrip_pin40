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
        if($user && in_array('ROLE_PRESCRIPTEUR', $user->getRoles())){
            $beneficiaries = $beneficiaryRepository->findBy(['prescriptor' => $user]);
        }
        if($user && in_array('ROLE_MEDIATEUR', $user->getRoles())){
            $beneficiaries = $beneficiaryRepository->findByMediation($user);
        }
        if($user && in_array('ROLE_SUPER_ADMIN', $user->getRoles())){
            $beneficiaries = $beneficiaryRepository->findAll();
        }

        return $this->render('admin/dashboard/index.html.twig', [
            'equipments' => $equipments,
            'prescriptions' => $prescriptions,
            'beneficiaries' => $beneficiaries
        ]);
    }
}
