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
use Doctrine\ORM\EntityManagerInterface;
use Elastica\Query\Terms;
use FOS\ElasticaBundle\Elastica\Index;
use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\MatchQuery;
use Elastica\Query\Term;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur central du workflow de prescription : recherche, création, édition par
 * étapes (`StepPrescription`), clôture, suppression. Le cycle de vie d'une prescription
 * avance via `edit()` en fonction de l'étape courante et du rôle de l'utilisateur.
 */
#[Route('/gestapp/prescription')]
final class PrescriptionController extends AbstractController
{
    private $finder;

    public function __construct(PaginatedFinderInterface $finder, private string $docuseal_Key)
    {
        $this->finder = $finder;
    }

    /**
     * Génère la référence initiale d'une prescription (`YYYYMMDD-xxxxx-N`) au moment de
     * sa création, avant qu'un prescripteur ne soit associé (le "xxxxx" est un espace
     * réservé remplacé par le slug de structure une fois celle-ci connue, voir `new()`).
     * Le compteur `N` incrémente le dernier compteur connu pour le prescripteur donné.
     */
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

    /**
     * Recherche de prescriptions via Elasticsearch (FOSElasticaBundle), combinée à un
     * filtre automatique selon le rôle (prescripteur/médiateur/super-admin) et aux
     * filtres du formulaire `PrescriptionSearchType` (référence, prescripteur).
     * C'est la route "index" fonctionnelle de la liste des prescriptions.
     */
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

    /**
     * Liste des prescriptions signées (étape `Signed`), toutes structures confondues.
     * Accessible depuis la navbar (lien "Prescriptions" pour les rôles admin).
     */
    #[Route('/admin', name: 'app_gestapp_prescription_foradmin', methods: ['GET', 'POST'])]
    public function listPrescriptionForAdmin(PrescriptionRepository $prescriptionRepository)
    {
        $prescriptions = $prescriptionRepository->findBy(['step' => StepPrescription::Signed->name]);

        dd($prescriptions);

        return $this->render('gestapp/prescription/adminPrescriptions.html.twig',[
            'prescriptions' => $prescriptions
        ]);
    }

    /**
     * Liste des prescriptions via requête Doctrine classique (sans Elasticsearch),
     * filtrée par rôle. **Aucune vue ni script du projet ne référence cette route** —
     * probable reliquat, comme `BeneficiaryController::index()` (route `/await`) et pour
     * la même raison (remplacée par la recherche Elasticsearch de `index()`).
     */
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

    /**
     * Création d'une prescription. Le statut/l'étape de départ dépend du rôle créateur
     * (admin : `Open` directement ; médiateur/prescripteur : `OneParts`). À la soumission,
     * la référence définitive est recalculée avec le vrai slug de structure et un compteur
     * mensuel par structure (le compteur repart à 1 à chaque changement de mois), en
     * fonction du rôle : admin/médiateur déterminent la structure via le bénéficiaire
     * choisi, un prescripteur utilise directement sa propre structure. Rafraîchit l'index
     * Elasticsearch après persistance pour une visibilité immédiate en recherche.
     */
    #[Route('/new', name: 'app_gestapp_prescription_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        PrescriptionRepository $prescriptionRepository,
        #[Autowire(service: 'fos_elastica.index.prescription')] Index $prescriptionIndex
    ): Response
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
            // Le compteur repart à 1 à chaque changement de mois.
            $monthStart = (clone $date)->modify('first day of this month')->setTime(0, 0, 0);
            $monthEnd = (clone $date)->modify('last day of this month')->setTime(23, 59, 59);

            if($user && (in_array('ROLE_SUPER_ADMIN', $user->getRoles()) || in_array('ROLE_ADMIN', $user->getRoles()) ) ){
                $beneficiary = $form->get('beneficiaire')->getData();
                $structure = $beneficiary->getStructure()->getSlug();
                $idStructure = $beneficiary->getStructure()->getId();
                if($structure)
                {
                    $lastPrescription = $prescriptionRepository->findLastForPrescriptorInMonth($idStructure, $monthStart, $monthEnd);

                    if(!$lastPrescription){
                        $compteur = 1;
                    }else{
                        $compteur = $lastPrescription->getCompteur() + 1;
                    }
                    $ref = $date->format('Ym')."-".$structure."-".$compteur;// mois-année-structure-compteur
                    $prescription->setRef($ref);
                }
                $prescription->setStatus(StatusPrescription::OpenByAdministrator);
                $prescription->setPrescriptor($beneficiary->getStructure());
                $prescription->setStep(StepPrescription::Open);
            }
            if($user && in_array('ROLE_MEDIATEUR', $user->getRoles())){
                $beneficiary = $form->get('beneficiaire')->getData();
                $structure = $beneficiary->getStructure()->getSlug();
                $idStructure = $beneficiary->getStructure()->getId();
                if($structure)
                {
                    $lastPrescription = $prescriptionRepository->findLastForPrescriptorInMonth($idStructure, $monthStart, $monthEnd);
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
                $lastPrescription = $prescriptionRepository->findLastForPrescriptorInMonth($structure->getId(), $monthStart, $monthEnd);
                if(!$lastPrescription){
                    $compteur = 1;
                }else{
                    $compteur = $lastPrescription->getCompteur() + 1;
                }

                $ref = $date->format('Ym')."-".$structure->getSlug()."-".$compteur;// mois-année-structure-compteur
                $prescription->setRef($ref);
                $prescription->setStatus(StatusPrescription::OpenByPrescriptor);
                $prescription->setIsOpenByPrescriptor(1);
                $prescription->setPrescriptor($structure);
                $prescription->setStep(StepPrescription::TwoParts);
            }

            $entityManager->persist($prescription);
            $entityManager->flush();

            // Force le rafraîchissement de l'index Elasticsearch pour que la prescription
            // soit immédiatement visible dans la liste (sinon délai "near real-time" d'ES).
            $prescriptionIndex->refresh();

            return $this->redirectToRoute('app_gestapp_prescription_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gestapp/prescription/new.html.twig', [
            'prescription' => $prescription,
            'form' => $form,
        ]);
    }

    /**
     * Fiche détail d'une prescription.
     * **Aucune vue ni script du projet ne référence cette route** (recherche exhaustive
     * dans `templates/` et `assets/js/`) — probable reliquat, la fiche prescription
     * semble uniquement consultée via l'édition (`edit()`).
     */
    #[Route('/{id}', name: 'app_gestapp_prescription_show', methods: ['GET'])]
    public function show(Prescription $prescription): Response
    {
        return $this->render('gestapp/prescription/show.html.twig', [
            'prescription' => $prescription,
        ]);
    }

    /**
     * Fait avancer une prescription d'une étape (`StepPrescription`) à la suivante,
     * selon l'étape courante ET le rôle de l'utilisateur qui soumet le formulaire —
     * c'est le cœur de la machine à états du workflow de prescription (chaque rôle
     * ne peut faire progresser que certaines étapes). Marque aussi l'équipement choisi
     * comme indisponible (`isDispo = 0`) s'il y en a un. Rafraîchit l'index Elasticsearch
     * après la mise à jour.
     */
    #[Route('/{id}/edit', name: 'app_gestapp_prescription_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Prescription $prescription,
        EntityManagerInterface $entityManager,
        #[Autowire(service: 'fos_elastica.index.prescription')] Index $prescriptionIndex
    ): Response
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
                    $prescripteur = $beneficiaire->getStructure();
                    $prescription->setPrescriptor($prescripteur);
                    $prescription->setStep(StepPrescription::ChoiceEquipment);
                }
                if($user && in_array('ROLE_MEDIATEUR', $user->getRoles())){
                    $prescription->setIsOpenByMediator(1);
                    $prescription->setStep(StepPrescription::TwoParts);
                }
                if($user && in_array('ROLE_PRESCRIPTEUR', $user->getRoles())){
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

            // Force le rafraîchissement de l'index Elasticsearch pour que la mise à jour
            // soit immédiatement visible dans la liste (sinon délai "near real-time" d'ES).
            $prescriptionIndex->refresh();

            return $this->redirectToRoute('app_gestapp_prescription_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('gestapp/prescription/edit.html.twig', [
            'prescription' => $prescription,
            'form' => $form,
        ]);
    }

    /**
     * Clôture une prescription (étape `ValidCase`), en renseignant commune/code postal
     * depuis le lieu de médiation. **Aucune vue ni script du projet ne référence cette
     * route ni n'inclut son template `_formClosedCase.html.twig`** — probable
     * fonctionnalité non finalisée ou abandonnée.
     */
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
    }

    /**
     * Suppression classique d'une prescription (formulaire de suppression, redirection).
     * Ne nettoie ni les fichiers PDF/signés sur disque, ni la soumission Docuseal associée
     * — contrairement à `del()` ci-dessous. Rafraîchit l'index Elasticsearch après coup.
     */
    #[Route('/{id}', name: 'app_gestapp_prescription_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Prescription $prescription,
        EntityManagerInterface $entityManager,
        #[Autowire(service: 'fos_elastica.index.prescription')] Index $prescriptionIndex
    ): Response
    {
        if ($this->isCsrfTokenValid('delete'.$prescription->getId(), $request->getPayload()->getString('_token'))) {
            // Libérer l'équipement réservé plutôt que de le supprimer avec la prescription.
            if ($equipement = $prescription->getEquipement()) {
                $equipement->setIsDispo(1);
            }

            $entityManager->remove($prescription);
            $entityManager->flush();

            // Force le rafraîchissement de l'index Elasticsearch pour que la suppression
            // soit immédiatement reflétée dans la liste (sinon délai "near real-time" d'ES).
            $prescriptionIndex->refresh();
        }

        return $this->redirectToRoute('app_gestapp_prescription_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Suppression complète d'une prescription, appelée en AJAX depuis les listes
     * (modale de confirmation) : supprime aussi les fichiers PDF associés sur disque
     * (original, signé, certificat de signature) et archive/supprime la soumission
     * Docuseal liée si elle existe. Retourne en JSON la liste de prescriptions HTML
     * mise à jour, filtrée par rôle.
     */
    #[Route('/{id}/del', name: 'app_gestapp_prescription_del', methods: ['POST'])]
    public function del(Request $request, Prescription $prescription, EntityManagerInterface $entityManager, PrescriptionRepository $prescriptionRepository): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$prescription->getId(), $request->getPayload()->getString('_token'))) {
            return $this->json([
                'code' => 422,
                'message' => 'Jeton de sécurité invalide, veuillez réessayer.',
            ], 200);
        }

        $member = $this->getUser();

        // Chemins des fichiers à purger UNIQUEMENT après une suppression réussie en base
        // (unlink() n'est pas transactionnel : si le flush échoue, les fichiers seraient
        // perdus alors que les lignes en base subsisteraient).
        $projectDir = $this->getParameter('kernel.project_dir');
        $paths = array_filter(
            [$prescription->getPath(), $prescription->getPathSigned(), $prescription->getPathSignedCertif()],
            fn($path) => !empty($path)
        );

        // Libérer l'équipement réservé : c'est un matériel réutilisable, on le remet
        // à disposition, on ne le supprime pas avec la prescription.
        if($equipement = $prescription->getEquipement()){
            $equipement->setIsDispo(1);
        }

        // Supprimer la trace sur le serveur de signature
        if($prescription->getDocuseal()){
            $docuseal = $prescription->getDocuseal();
            $api = new \Docuseal\Api($this->docuseal_Key, 'https://dseal.openpixl.fr/api');
            $api->archiveSubmission($docuseal->getIdSeal());
            $entityManager->remove($docuseal);
        }

        // La suppression cascade sur les Document liés (OneToMany orphanRemoval).
        try {
            $entityManager->remove($prescription);
            $entityManager->flush();
        } catch (\Throwable $e) {
            return $this->json([
                'code' => 500,
                'message' => "La prescription n'a pas pu être supprimée : ".$e->getMessage(),
            ], 200);
        }

        // Base à jour : on peut purger les fichiers du disque.
        foreach ($paths as $p) {
            $fullPath = $projectDir."/public".$p;
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }

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

        return $this->json([
            'code' => 200,
            'message' => 'Prescription supprimée avec succès',
            'liste' => $this->renderView('gestapp/prescription/include/_liste.html.twig', [
                'prescriptions' => $prescriptions,
            ])
        ], 200);
    }
}
