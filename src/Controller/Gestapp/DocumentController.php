<?php

namespace App\Controller\Gestapp;

use App\Config\StepPrescription;
use App\Entity\Gestapp\Document;
use App\Form\Gestapp\DocumentFormType;
use App\Repository\Gestapp\PrescriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DocumentController extends AbstractController
{
    #[Route('/gestapp/document', name: 'app_gestapp_document')]
    public function index(): Response
    {
        return $this->render('gestapp/document/index.html.twig', [
            'controller_name' => 'DocumentController',
        ]);
    }

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
                    $filename = $prescription->getRef().'_signed.pdf';
                    $pathdir = $this->getParameter('prescription_directory_url');
                    try {
                        if (is_dir($pathdir)){
                            $documentFile->move(
                                $pathdir,
                                $filename
                            );
                        }else{
                            // Création du répertoire s'il n'existe pas.
                            mkdir($pathdir."/", 0775, true);
                            // Déplacement de la photo
                            $documentFile->move(
                                $pathdir,
                                $filename
                            );
                        }

                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }

                    $document->setDocumentFilename($filename);
                    $document->setPrescription($prescription);
                    $document->setPath($pathdir.$filename);
                    $prescription->setStep(stepPrescription::Signed);
                }

                $em->persist($document);
                $em->flush();

                if($user && in_array('ROLE_PRESCRIPTEUR', $user->getRoles())){
                    $prescriptions = $prescriptionRepository->findBy(['prescriptor' => $user->getStructure()]);
                }
                if($user && in_array('ROLE_MEDIATEUR', $user->getRoles())){
                    $prescriptions = $prescriptionRepository->findBy(['lieuMediation' => $user]);
                }
                if($user && in_array('ROLE_ADMIN', $user->getRoles())){
                    $prescriptions = $prescriptionRepository->findAll();
                }
                if($user && in_array('ROLE_SUPER_ADMIN', $user->getRoles())){
                    $prescriptions = $prescriptionRepository->findAll();
                }

                $liste = $this->renderView('admin/dashboard/include/_liste.html.twig', [
                    'prescriptions' => $prescriptions,
                ]);

                return $this->json([
                    'code' => 200,
                    'message' => 'Le document est déposé sur la plateforme',
                    'liste' => $liste,
                ], 200);

            }

            dd($form->getErrors(true, true));

            $view = $this->renderView('gestapp/document/prescript_signed_manually.html.twig', [
                'form' => $form,
                'document' => $document
            ]);

            return $this->json([
                'code' => 400,
                'message' => 'Une erreur s\'est glissé dans le formulaire',
                'formView' => $view
            ], 400);


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
