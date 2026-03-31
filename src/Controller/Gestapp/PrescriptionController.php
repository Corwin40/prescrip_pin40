<?php

namespace App\Controller\Gestapp;

use App\Config\StatusPrescription;
use App\Config\StepPrescription;
use App\Entity\Gestapp\Competence;
use App\Entity\Gestapp\Prescription;
use App\Form\Gestapp\closedCaseType;
use App\Form\Gestapp\PrescriptionType;
use App\Form\Search\dashboardPrescriptionSearchType;
use App\Repository\Gestapp\PrescriptionRepository;
use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\MultiMatch;
use Elastica\Query\Term;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/gestapp/prescription')]
final class PrescriptionController extends AbstractController
{
    private $finder;

    public function __construct(PaginatedFinderInterface $finder)
    {
        $this->finder = $finder;
    }

    #[Route('/searchdashboard', name: 'app_gestapp_prescription_searchdashboard', methods: ['GET'])]
    public function searchDashboard(Request $request, PrescriptionRepository $prescriptionRepository, MemberRepository $memberRepository): Response
    {
        // récupérer tous les prescripteurs existants (exemple Doctrine)
        $prescriptors = $memberRepository->findByRole('ROLE_PRESCRIPTEUR');

        $prescriptorChoices = [];
        foreach ($prescriptors as $p) {
            $prescriptorChoices[$p['nameStructure']] = $p['id'];
        }

        $form = $this->createForm(dashboardPrescriptionSearchType::class, null, [
            'prescriptors' => $prescriptorChoices
        ]);
        $form->handleRequest($request);

        // construire la query Elastice
        $boolQuery = new BoolQuery();

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (!empty($data['query'])) {
                $multiMatch = new MultiMatch();
                $multiMatch->setFields(['firstName', 'lastName']);
                $multiMatch->setQuery($data['query']);
                $boolQuery->addMust($multiMatch);
            }

            if (!empty($data['prescriptor'])) {

                $termQuery = new Term();
                $termQuery->setTerm('prescriptor.id', $data['prescriptor']);
                $boolQuery->addMust($termQuery);
            }

        }

        $query = new Query($boolQuery);
        $query->setSize(50); // max 50 résultats, ajustable

        $results = $this->finder->find($query);

        $view = $this->renderView('gestapp/prescription/searchDashboard.html.twig', [
            'form' => $form->createView(),
            'results' => $results,
        ]);

        return $this->json([], 200);
    }

    #[Route(name: 'app_gestapp_prescription_index', methods: ['GET'])]
    public function index(PrescriptionRepository $prescriptionRepository): Response
    {
        $member = $this->getUser();
        if($member && in_array('ROLE_PRESCRIPTEUR', $member->getRoles())){
            $prescriptions = $prescriptionRepository->findBy(['membre' => $member]);
        }
        if($member && in_array('ROLE_MEDIATEUR', $member->getRoles())){
            $prescriptions = $prescriptionRepository->findBy(['lieuMediation' => $member]);
        }
        if($member && in_array('ROLE_SUPER_ADMIN', $member->getRoles())){
            $prescriptions = $prescriptionRepository->findAll();
        }

        return $this->render('gestapp/prescription/index.html.twig', [
            'prescriptions' => $prescriptions,
        ]);
    }

    #[Route('/new', name: 'app_gestapp_prescription_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, PrescriptionRepository $prescriptionRepository): Response
    {
        $user = $this->getUser();
        $structure = $user->getStructure();

        // Construction de la variable Ref
        $date = new \DateTime('now');

        $lastPrescription = $prescriptionRepository->findOneBy(['prescriptor' => $structure],[ 'id' => 'DESC']);
        if(!$lastPrescription){
            $compteur = 1;
        }else{
            $compteur = $lastPrescription->getCompteur() + 1;
        }

        $ref = $date->format('Ym')."-xxxxx-".$compteur;// mois-année-structure-compteur

        $prescription = new Prescription();
        $prescription->setRef($ref);
        $prescription->setCompteur($compteur);
        $prescription->setBaseCompetence('Non acquises');
        $prescription->setCompetence(new Competence());
        if ($user && (in_array('ROLE_SUPER_ADMIN', $user->getRoles()) || in_array('ROLE_ADMIN', $user->getRoles()))) {
            $prescription->setStatus(StatusPrescription::OpenByAdministrator);
            $prescription->setStep(StepPrescription::Open);
        } else if ($user && in_array('ROLE_MEDIATEUR', $user->getRoles())) {
            $prescription->setStatus(StatusPrescription::OpenByMediator);
            $prescription->setStep(StepPrescription::OneParts);
        } else if ($user && in_array('ROLE_PRESCRIPTEUR', $user->getRoles())) {
            $prescription->setStatus(StatusPrescription::OpenByPrescriptor);
            $prescription->setStep(StepPrescription::OneParts);
        }

        $form = $this->createForm(PrescriptionType::class, $prescription, [
            'action' => $this->generateUrl('app_gestapp_prescription_new'),
            'method' => 'POST',
            'attr' => [
                'id' => 'formPrescription',
            ],
            'user' => $user,
            'prescription' => $prescription,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if($user && (in_array('ROLE_SUPER_ADMIN', $user->getRoles()) || in_array('ROLE_ADMIN', $user->getRoles()) ) ){
                $beneficiary = $form->get('beneficiaire')->getData();
                $structure = $beneficiary->getPrescriptor()->getSlug();
                $idStructure = $beneficiary->getPrescriptor()->getId();
                if($structure)
                {
                    $lastPrescription = $prescriptionRepository->findOneBy(['membre' => $idStructure],[ 'id' => 'DESC']);

                    if(!$lastPrescription){
                        $compteur = 1;
                    }else{
                        $compteur = $lastPrescription->getCompteur() + 1;
                    }
                    $ref = $date->format('Ym')."-".$structure."-".$compteur;// mois-année-structure-compteur
                    $prescription->setRef($ref);
                }
                $prescription->setStatus(StatusPrescription::OpenByAdministrator);
                $prescription->setPrescriptor($beneficiary->geStructure());
                $prescription->setStep(StepPrescription::Open);
            }
            if($user && in_array('ROLE_MEDIATEUR', $user->getRoles())){
                $beneficiary = $form->get('beneficiaire')->getData();
                $structure = $beneficiary->getPrescriptor()->getSlug();
                $idStructure = $beneficiary->getPrescriptor()->getId();
                if($structure)
                {
                    $lastPrescription = $prescriptionRepository->findOneBy(['prescriptor' => $idStructure],[ 'id' => 'DESC']);
                    if(!$lastPrescription){
                        $compteur = 1;
                    }else{
                        $compteur = $lastPrescription->getCompteur() + 1;
                    }
                    $ref = $date->format('Ym')."-".$structure."-".$compteur;// mois-année-structure-compteur
                    $prescription->setRef($ref);
                }
                $prescription->setIsOpenByMediator(1);
                $prescription->setPrescriptor($beneficiary->getStructure());
                if($prescription->getStatus()->name == StatusPrescription::OpenByPrescriptor){
                    $prescription->setStep(StepPrescription::TwoParts);
                }else{
                    $prescription->setStep(StepPrescription::OneParts);
                }
            }
            if($user && in_array('ROLE_PRESCRIPTEUR', $user->getRoles())){
                $structure = $this->getUser()->getStructure();
                $ref = $date->format('Ym')."-".$structure."-".$compteur;// mois-année-structure-compteur
                $prescription->setRef($ref);
                $prescription->setStatus(StatusPrescription::OpenByPrescriptor);
                $prescription->setIsOpenByPrescriptor(1);
                $prescription->setPrescriptor($structure);
                $prescription->setStep(StepPrescription::TwoParts);
            }

            $entityManager->persist($prescription);
            $entityManager->flush();

            return $this->redirectToRoute('app_gestapp_prescription_index', [], Response::HTTP_SEE_OTHER);
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
    public function edit(Request $request, Prescription $prescription, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(PrescriptionType::class, $prescription, [
            'action' => $this->generateUrl('app_gestapp_prescription_edit',[
                'id' => $prescription->getId()
            ]),
            'method' => 'POST',
            'attr' => [
                'id' => 'formPrescription',
            ],
            'user' => $user,
            'prescription' => $prescription,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $step = $prescription->getStep();

            if($step == StepPrescription::Open){
                if($user && (in_array('ROLE_SUPER_ADMIN', $user->getRoles()) || in_array('ROLE_ADMIN', $user->getRoles()) ) ){
                    $beneficiaire = $form->get('beneficiaire')->getData();
                    $prescripteur = $beneficiaire->getStructure();
                    $prescription->setPrescriptor($prescripteur);
                    $prescription->setStep(StepPrescription::ChoiceEquipment);
                }
            }
            if($step == StepPrescription::OneParts){
                if($user && (in_array('ROLE_SUPER_ADMIN', $user->getRoles()) || in_array('ROLE_ADMIN', $user->getRoles()) ) ){
                    $beneficiaire = $form->get('beneficiaire')->getData();
                    $prescripteur = $beneficiaire->getPrescriptor();
                    $prescription->setPrescriptor($prescripteur);
                    $prescription->setStep(StepPrescription::ChoiceEquipment);
                }
                if($user && in_array('ROLE_MEDIATEUR', $user->getRoles())){
                    $prescription->setIsOpenByMediator(1);
                    $prescription->setStep(StepPrescription::TwoParts);
                }
                if($user && in_array('ROLE_PRESCRIPTEUR', $user->getRoles())){
                    $competence = $prescription->getCompetence();
                    $prescription->setIsOpenByPrescriptor(1);
                    $prescription->setStep(StepPrescription::TwoParts);
                }
            }
            elseif($step == StepPrescription::TwoParts){
                if($user && (in_array('ROLE_SUPER_ADMIN', $user->getRoles()) || in_array('ROLE_ADMIN', $user->getRoles()) || in_array('ROLE_MEDIATEUR', $user->getRoles()) ) ){
                    $beneficiaire = $form->get('beneficiaire')->getData();
                    $prescripteur = $beneficiaire->getPrescriptor();
                    $prescription->setPrescriptor($prescripteur);
                    $prescription->setStep(StepPrescription::ChoiceEquipment);
                }
                if($user && in_array('ROLE_MEDIATEUR', $user->getRoles())){
                    $prescription->setIsOpenByMediator(1);
                    $prescription->setStep(StepPrescription::ChoiceEquipment);
                }
            }
            elseif($step == StepPrescription::ChoiceEquipment){
                if($user && (in_array('ROLE_SUPER_ADMIN', $user->getRoles()) || in_array('ROLE_ADMIN', $user->getRoles()) || in_array('ROLE_MEDIATEUR', $user->getRoles()) ) ){
                    $beneficiaire = $form->get('beneficiaire')->getData();
                    $prescripteur = $beneficiaire->getPrescriptor();
                    $prescription->setPrescriptor($prescripteur);
                    $prescription->setValidcase(1);
                    $prescription->setStep(StepPrescription::ValidCase);
                }
                if($user && in_array('ROLE_MEDIATEUR', $user->getRoles())){
                    $beneficiaire = $form->get('beneficiaire')->getData();
                    $prescripteur = $beneficiaire->getPrescriptor();
                    $prescription->setPrescriptor($prescripteur);
                    $prescription->setValidcase(1);
                    $prescription->setStep(StepPrescription::ValidCase);
                }
            }

            $equipment = $form->get('equipement')->getData();
            if($equipment){
                $prescription->setEquipement($equipment);
                $equipment->setIsDispo(0);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_gestapp_prescription_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gestapp/prescription/edit.html.twig', [
            'prescription' => $prescription,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/closedcase', name: 'app_gestapp_prescription_closedcase', methods: ['POST'])]
    public function closedcase(Prescription $prescription, EntityManagerInterface $entityManager, Request $request)
    {

        $form = $this->createForm(ClosedCaseType::class, $prescription, [
            'action' => $this->generateUrl('app_gestapp_prescription_closedcase',[
                'id' => $prescription->getId()
            ]),
            'method' => 'POST',
            'attr' => [
                'id' => 'formClosedCase',
            ],
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $prescription->setValidcase(1);
            $prescription->setStep(StepPrescription::ValidCase);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_dashboard_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gestapp/prescription/_formClosedCase.html.twig', [
            'prescription' => $prescription,
            'form' => $form,
        ]);

        //return $this->json([
        //    'code' => 200,
        //    'message' => 'Le fichier PDF correspondant à la prescription est en cours de génération',
        //    'prescription' => $prescription,
        //], 200);
    }

    #[Route('/{id}', name: 'app_gestapp_prescription_delete', methods: ['POST'])]
    public function delete(Request $request, Prescription $prescription, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$prescription->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($prescription);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_gestapp_prescription_index', [], Response::HTTP_SEE_OTHER);
    }
}
