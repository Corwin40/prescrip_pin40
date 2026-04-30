<?php

namespace App\Controller\Admin;

use App\Config\StepPrescription;
use App\Entity\Gestapp\Prescription;
use App\Repository\Gestapp\BeneficiaryRepository;
use App\Repository\Gestapp\EquipmentRepository;
use App\Repository\Gestapp\PrescriptionRepository;
use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{

    #[Route('/admin/dashboard', name: 'app_admin_dashboard_index', methods: ['GET'])]
    public function index(MemberRepository $memberRepository, EquipmentRepository $equipmentRepository, PrescriptionRepository $prescriptionRepository, BeneficiaryRepository $beneficiaryRepository): Response
    {
        $user = $this->getUser();
        $structure = $user->getStructure();
        $roles = $user->getRoles();
        $member = $memberRepository->find($user->getId());

        if($user && in_array('ROLE_PRESCRIPTEUR', $user->getRoles())){
            $beneficiaries = $beneficiaryRepository->findBy(['structure' => $user->getStructure()]);

            $prescriptions = $prescriptionRepository->filteredByWithoutStepForPrescriptor(StepPrescription::Signed, $user->getStructure());
            $prescriptionsSigned = $prescriptionRepository->filteredByStepForPrescriptor(StepPrescription::Signed, $user->getStructure());

            $equipments = $equipmentRepository->findByDispos($member);
        }
        if($user && in_array('ROLE_MEDIATEUR', $user->getRoles())){
            $beneficiaries = $beneficiaryRepository->findByMediation($user->getStructure());
            $prescriptions = $prescriptionRepository->filteredByWithoutStepForMediator(StepPrescription::Signed, $user->getStructure());
            $prescriptionsSigned = $prescriptionRepository->filteredByStepForMediator(StepPrescription::Signed, $user->getStructure());
            $equipments = $equipmentRepository->findByDispos($member);
        }
        if($user && in_array('ROLE_ADMIN', $user->getRoles())){
            $beneficiaries = $beneficiaryRepository->findAll();
            $prescriptions = $prescriptionRepository->findBy(['step' => StepPrescription::Signed]);
            $equipments = $equipmentRepository->findByDispos();
        }
        if($user && in_array('ROLE_SUPER_ADMIN', $user->getRoles())){
            $beneficiaries = $beneficiaryRepository->findAll();
            $prescriptions = $prescriptionRepository->filteredByWithoutStep(StepPrescription::Signed->name);
            $prescriptionsSigned = $prescriptionRepository->filteredByStep(StepPrescription::Signed->name);
            $equipments = $equipmentRepository->findByDispos();
        }

        //dd($member,$equipments, $prescriptions, $beneficiaries);

        return $this->render('admin/dashboard/index.html.twig', [
            'equipments' => $equipments,
            'prescriptions' => $prescriptions,
            'prescriptionsSigned' => $prescriptionsSigned,
            'beneficiaries' => $beneficiaries
        ]);
    }
}
