<?php

namespace App\Controller\Gestapp;

use App\Config\StepPrescription;
use App\Entity\Gestapp\Document;
use App\Form\Gestapp\DocumentFormType;
use App\Repository\Gestapp\PrescriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DocumentController extends AbstractController
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    /**
     * Page d'accueil scaffold par défaut (générée par Maker Bundle, jamais personnalisée).
     * Aucune vue ni lien de l'application ne référence cette route — voir Observations
     * dans `docs/controllers/gestapp-document.md`.
     */
    #[Route('/gestapp/document', name: 'app_gestapp_document')]
    public function index(): Response
    {
        return $this->render('gestapp/document/index.html.twig', [
            'controller_name' => 'DocumentController',
        ]);
    }

    /**
     * Dépôt manuel d'un document de prescription signée (upload direct, alternative
     * au flux de signature électronique Docuseal). Enregistre le fichier sur disque
     * (`prescription_signed_directory`), crée l'entité `Document` liée, fait passer la
     * prescription à l'étape `Signed`. Répond toujours en JSON (appelée en AJAX depuis
     * la modale d'upload) : GET/POST invalide renvoie le formulaire en HTML encapsulé,
     * succès renvoie aussi la liste de prescriptions HTML mise à jour (filtrée par rôle).
     */
    #[Route('/gestapp/document/prescription_signed_manually/{idprescription}', name: 'app_gestapp_document_prescriptionsigned_manually')]
    public function prescription_signed_manually(
        Request $request,
        $idprescription,
        PrescriptionRepository $prescriptionRepository,
        EntityManagerInterface $em
    )
    {
        $user = $this->getUser();
        $document = new Document();

        $prescription = $prescriptionRepository->find($idprescription);

        $form = $this->createForm(DocumentFormType::class, $document, [
            'action' => $this->generateUrl('app_gestapp_document_prescriptionsigned_manually', [
                'idprescription' => $idprescription
            ]),
            'method' => 'POST',
            'attr' => [
                'id' => 'formDocument_addPrescriptionSignedManually',
            ]
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted()){

            if($form->isValid()) {
                $documentFile = $form->get('documentFile')->getData();
                if($documentFile){
                    $slugStructure = $prescription->getPrescriptor()->getSlug();
                    $filename = $prescription->getRef().'_signedByManually.pdf';
                    $pathdir = $this->getParameter('prescription_signed_directory').$slugStructure;
                    $pathUrl = $this->getParameter('prescription_signed_directory_url').$slugStructure;
                    try {
                        if (!is_dir($pathdir)){
                            // Création du répertoire s'il n'existe pas.
                            mkdir($pathdir."/", 0775, true);
                        }
                        $documentFile->move($pathdir, $filename);
                    } catch (FileException $e) {
                        $this->logger->error('Dépôt manuel de document signé : échec du déplacement du fichier.', [
                            'prescription' => $prescription->getRef(),
                            'path' => $pathdir.'/'.$filename,
                            'exception' => $e->getMessage(),
                        ]);
                    }

                    $document->setDocumentFilename($filename);
                    $document->setPrescription($prescription);
                    $document->setPath($pathUrl.'/'.$filename);
                    $prescription->setStep(StepPrescription::Signed);
                    $prescription->setPathSigned($pathUrl.'/'.$filename);
                }

                $em->persist($document);
                $em->flush();

                // Liste filtrée selon le rôle. Initialisée à [] : jamais de variable indéfinie
                // si l'utilisateur ne correspond à aucun rôle attendu.
                $prescriptions = [];
                if($user && in_array('ROLE_PRESCRIPTEUR', $user->getRoles())){
                    $prescriptions = $prescriptionRepository->findBy(['prescriptor' => $user->getStructure()]);
                } elseif($user && in_array('ROLE_MEDIATEUR', $user->getRoles())){
                    $prescriptions = $prescriptionRepository->findBy(['lieuMediation' => $user->getStructure()]);
                } elseif($user && in_array('ROLE_ADMIN', $user->getRoles())){
                    $prescriptions = $prescriptionRepository->findBy(['step' => StepPrescription::Signed]);
                } elseif($user && in_array('ROLE_SUPER_ADMIN', $user->getRoles())){
                    $prescriptions = $prescriptionRepository->findAll();
                }

                // La modale est ouverte aussi bien depuis le tableau de bord admin que
                // depuis la liste des prescriptions : chaque page indique son gabarit.
                $listTemplate = $request->request->get('_listContext') === 'prescription_index'
                    ? 'gestapp/prescription/include/_liste.html.twig'
                    : 'admin/dashboard/include/_liste.html.twig';

                $liste = $this->renderView($listTemplate, [
                    'prescriptions' => $prescriptions,
                ]);

                return $this->json([
                    'code' => 200,
                    'message' => 'Le document est déposé sur la plateforme',
                    'liste' => $liste,
                ], 200);

            }

            $view = $this->renderView('gestapp/document/prescript_signed_manually.html.twig', [
                'form' => $form,
                'document' => $document
            ]);

            return $this->json([
                'code' => 400,
                'message' => 'Une erreur s\'est glissé dans le formulaire',
                'formView' => $view
            ], 200);


        }

        $view = $this->renderView('gestapp/document/prescript_signed_manually.html.twig', [
            'form' => $form,
            'document' => $document
        ]);

        return $this->json([
            'code' => 200,
            'formView' => $view
        ], 200);
    }
}
