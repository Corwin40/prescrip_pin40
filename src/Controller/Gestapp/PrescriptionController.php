<?php

namespace App\Controller\Gestapp;

use App\Config\StatusPrescription;
use App\Config\StepPrescription;
use App\Entity\Gestapp\Competence;
use App\Entity\Gestapp\Prescription;
use App\Form\Gestapp\closedCaseType;
use App\Form\Gestapp\PrescriptionType;
use App\Form\Search\PrescriptionSearchType;
use App\Repository\Admin\StructureRepository;
use App\Repository\Gestapp\PrescriptionRepository;
use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Elastica\Query\Terms;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\MatchQuery;
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

    public function createRef($structure, PrescriptionRepository $prescriptionRepository)
    {
        // Construction de la variable Ref
        $date = new \DateTime('now');

        $lastPrescription = $prescriptionRepository->findOneBy(['prescriptor' => $structure],[ 'id' => 'DESC']);
        if(!$lastPrescription){
            $compteur = 1;
        }else{
            $compteur = $lastPrescription->getCompteur() + 1;
        }

        $ref = $date->format('Ymd')."-xxxxx-".$compteur;// mois-année-structure-compteur

        return [$ref, $compteur];
    }

    #[Route('/', name: 'app_gestapp_prescription_index', methods: ['GET', 'POST'])]
    public function index(Request $request, StructureRepository $structureRepository): Response
    {
        $member = $this->getUser();
        $structureId = $member->getStructure()?->getId();

        // Récupération des prescripteurs selon le rôle
        if($member && in_array('ROLE_PRESCRIPTEUR', $member->getRoles())) {
            $prescriptors = $structureRepository->findPrescriptorsByPrescriptor($structureId);
        } elseif($member && in_array('ROLE_MEDIATEUR', $member->getRoles())) {
            $prescriptors = $structureRepository->findPrescriptorsByMediator($structureId);
        } elseif($member && in_array('ROLE_SUPER_ADMIN', $member->getRoles())) {
            $prescriptors = $structureRepository->findPrescriptorsByAdmin();
        } else {
            $prescriptors = [];
        }

        // Construction des choix pour le formulaire
        $prescriptorChoices = [];
        $prescriptorIds = [];

        foreach ($prescriptors as $p) {
            $prescriptorChoices[$p->getName()] = $p->getId();
            $prescriptorIds[] = $p->getId();
        }

        $form = $this->createForm(PrescriptionSearchType::class, null, [
            'prescriptors' => $prescriptorChoices
        ]);
        $form->handleRequest($request);

        // Construction de la requête Elasticsearch
        $boolQuery = new BoolQuery();

        // Filtre automatique selon le rôle
        if ($member && in_array('ROLE_PRESCRIPTEUR', $member->getRoles()) && $structureId) {
            $termQuery = new Term();
            $termQuery->setTerm('prescriptor.id', $structureId);
            $boolQuery->addFilter($termQuery);
        }
        if ($member && in_array('ROLE_MEDIATEUR', $member->getRoles())) {
            $termsQuery = new Terms('prescriptor.id', $prescriptorIds);
            $boolQuery->addFilter($termsQuery);
            //dd($boolQuery);
        }
        if ($member && in_array('ROLE_SUPER_ADMIN', $member->getRoles())) {
            $termsQuery = new Terms('prescriptor.id', $prescriptorIds);
            $boolQuery->addFilter($termsQuery);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (!empty($data['ref'])) {
                $matchQuery = new MatchQuery();
                $matchQuery->setField('ref', $data['ref']);
                $boolQuery->addMust($matchQuery);
            }

            if (!empty($data['prescriptor'])) {
                $termQuery = new Term();
                $termQuery->setTerm('prescriptor.id', $data['prescriptor']);
                $boolQuery->addFilter($termQuery);
            }
        }

        $query = new Query($boolQuery);
        $query->setSize(50); // max 50 résultats, ajustable

        $results = $this->finder->find($query);

        return $this->render('gestapp/prescription/searchdashboard.html.twig', [
            'prescriptions' => $results,
            'form' => $form->createView(),
        ]);


    }

    #[Route('/admin', name: 'app_gestapp_prescription_foradmin', methods: ['GET', 'POST'])]
    public function listPrescriptionForAdmin(PrescriptionRepository $prescriptionRepository)
    {
        $prescriptions = $prescriptionRepository->findBy(['step' => StepPrescription::Signed->name]);

        //dd($prescriptions);

        return $this->render('gestapp/prescription/adminPrescriptions.html.twig',[
            'prescriptions' => $prescriptions
        ]);
    }

    #[Route(name: 'app_gestapp_prescription_await', methods: ['GET'])]
    public function await(PrescriptionRepository $prescriptionRepository): Response
    {
        $member = $this->getUser();
        if($member && in_array('ROLE_PRESCRIPTEUR', $member->getRoles())){
            $prescriptions = $prescriptionRepository->findBy(['prescriptor' => $member->getStructure()]);
        }
        if($member && in_array('ROLE_MEDIATEUR', $member->getRoles())){
            $prescriptions = $prescriptionRepository->findBy(['lieuMediation' => $member->getStructure() ]);
        }
        if($member && in_array('ROLE_ADMIN', $member->getRoles())){
            $prescriptions = $prescriptionRepository->findBy(['step' => StepPrescription::Signed]);
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

        $createRef = $this->createRef($structure, $prescriptionRepository);

        $prescription = new Prescription();
        $prescription->setRef($createRef[0]);
        $prescription->setCompteur($createRef[1]);
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

            $date = new \DateTime('now');

            if($user && (in_array('ROLE_SUPER_ADMIN', $user->getRoles()) || in_array('ROLE_ADMIN', $user->getRoles()) ) ){
                $beneficiary = $form->get('beneficiaire')->getData();
                $structure = $beneficiary->getStructure()->getSlug();
                $idStructure = $beneficiary->getPrescriptor()->getId();
                if($structure)
                {
                    $lastPrescription = $prescriptionRepository->findOneBy(['membre' => $idStructure],[ 'id' => 'DESC']);

                    if(!$lastPrescription){
                        $compteur = 1;
                    }else{
                        $compteur = $lastPrescription->getCompteur() + 1;
                    }
                    $ref = $date->format('Ymd')."-".$structure."-".$compteur;// mois-année-structure-compteur
                    $prescription->setRef($ref);
                }
                $prescription->setStatus(StatusPrescription::OpenByAdministrator);
                $prescription->setPrescriptor($beneficiary->geStructure());
                $prescription->setStep(StepPrescription::Open);
            }
            if($user && in_array('ROLE_MEDIATEUR', $user->getRoles())){
                $beneficiary = $form->get('beneficiaire')->getData();
                $structure = $beneficiary->getStructure()->getSlug();
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
                    $prescripteur = $beneficiaire->getStructure();
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
                    $prescripteur = $beneficiaire->getStructure();
                    $prescription->setPrescriptor($prescripteur);
                    $prescription->setValidcase(1);
                    $prescription->setStep(StepPrescription::ValidCase);
                }
                if($user && in_array('ROLE_MEDIATEUR', $user->getRoles())){
                    $beneficiaire = $form->get('beneficiaire')->getData();
                    $prescripteur = $beneficiaire->getStructure();
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
            $structure = $prescription->getLieuMediation();
            $city = $structure->getCity();
            $cp = $structure->getZipcode();

            $prescription->setValidcase(1);
            $prescription->setClosedAt((new \DateTime('now')));
            $prescription->setCommune($city);
            $prescription->setCp($cp);
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
