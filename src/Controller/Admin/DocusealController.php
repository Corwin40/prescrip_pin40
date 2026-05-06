<?php

namespace App\Controller\Admin;

use App\Config\StepPrescription;
use App\Entity\Gestapp\Prescription;
use App\Entity\Serv\Docuseal;
use App\Repository\Serv\DocusealRepository;
use App\Service\QrcodeGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/gestapp/docuseal/')]
final class DocusealController extends AbstractController
{
    public function __construct(
        //private string $docuseal_Url,
        private string $docuseal_Key,
        public EntityManagerInterface $em,
        private readonly QrcodeGenerator $qrcodeGenerator,
    ) {
    }

    public function getElement(Prescription $prescription)
    {
        // Création des éléments de préremplissage
        $element = [];
        $beneficiaire = $prescription->getBeneficiaire()->getCivility()->value ." ".$prescription->getBeneficiaire()->getFirstname()." ".$prescription->getBeneficiaire()->getLastname() ?? '';
        $age = $prescription->getBeneficiaire()->getAgeGroup() ?? '';
        $genre = $prescription->getBeneficiaire()->getGender() ?? '';
        $situation = $prescription->getBeneficiaire()->getProfessionnalStatus() ?? '';
        $structure = $prescription->getPrescriptor()->getName() ?? '';
        $lieuMediation = $prescription->getLieuMediation()->getName() ?? '';
        $respStructure = $prescription->getPrescriptor()->getContactResponsableCivility()->value." ".$prescription->getPrescriptor()->getContactResponsableFirstname()." ".$prescription->getPrescriptor()->getContactResponsableLastname() ?? '';
        $telPrescripteur = $prescription->getPrescriptor()->getContactPhone() ?? '';
        $emailPrescripteur = $prescription->getPrescriptor()->getContactEmail() ?? '';
        $details = $prescription->getDetails() ?? '';
        $baseCompetences = $prescription->getBaseCompetence() ?? '';
        $CompBase = $prescription->getCompetence()->getCompBase();
        $CompDesk = $prescription->getCompetence()->getCompDesk();
        $CompInternet = $prescription->getCompetence()->getCompInternet();
        $CompEmail = $prescription->getCompetence()->getCompEmail();
        $isAutoEval = $prescription->getCompetence()->isAutoEva() ? 'true' : 'false';
        $isDigComp0 = $prescription->getCompetence()->isDigComp0() ? 'true' : 'false';
        $isDigComp1 = $prescription->getCompetence()->isDigComp1() ? 'true' : 'false';
        $isDigComp2 = $prescription->getCompetence()->isDigComp2() ? 'true' : 'false';
        $isDigComp3 = $prescription->getCompetence()->isDigComp3() ? 'true' : 'false';
        $isDigComp4 = $prescription->getCompetence()->isDigComp4() ? 'true' : 'false';
        $isDigComp5 = $prescription->getCompetence()->isDigComp5() ? 'true' : 'false';
        $parcours = $prescription->getCompetence()->getDetailParcour() ?? '';
        $isAutoEvalEnd = $prescription->getCompetence()->isAutoEvaEnd() ? 'true' : 'false';
        $idMachine = $prescription->getEquipement()->getEquipmentId() ?? '';
        $ville = $prescription->getCp()." ".$prescription->getCommune() ??'';
        $le = (new \DateTime('now'))->format('d-m-Y');

        $element['Beneficiaire']            = $beneficiaire;
        $element['Genre']                   = $genre;
        $element['Age']                     = $age;
        $element['Situation']               = $situation;
        $element['Nom_Prescripteur']        = $structure;
        $element['Resp_Prescripteur']       = $respStructure;
        $element['Tel_Prescripteur']        = $telPrescripteur;
        $element['Email_Prescripteur']      = $emailPrescripteur;
        $element['Details']                 = $details;
        $element['Base_Competences']        = $baseCompetences;
        $element['Lieu_Mediation']          = $lieuMediation;
        $element['Compentences_Bases']      = $CompBase;
        $element['Compentences_Bureautique']= $CompDesk;
        $element['Compentences_Internet']   = $CompInternet;
        $element['Compentences_Messagerie'] = $CompEmail;
        $element['isAutoEval']              = $isAutoEval;
        $element['isDigComp0']              = $isDigComp0;
        $element['isDigComp1']              = $isDigComp1;
        $element['isDigComp2']              = $isDigComp2;
        $element['isDigComp3']              = $isDigComp3;
        $element['isDigComp4']              = $isDigComp4;
        $element['isDigComp5']              = $isDigComp5;
        $element['Parcours']                = $parcours;
        $element['isAutoEvalEnd']           = $isAutoEvalEnd;
        $element['Id_Machine']              = $idMachine;
        $element['Ville']                   = $ville;
        $element['Le']                      = $le;


        return $element;
    }

    public function getDocuseal($data, $prescription)
    {
        $docuseal = new Docuseal();
        $docuseal->setIdSeal($data[0]['id']);
        $docuseal->setSlugSeal($data[0]['slug']);
        $docuseal->setUuidSeal($data[0]['uuid']);
        $docuseal->setNameSubmissionSeal($data[0]['name']);
        $docuseal->setEmailSubmissionSeal($data[0]['email']);
        $docuseal->setPhoneSeal($data[0]['phone']);
        $docuseal->setCompletedAtSeal(new \DateTime($data[0]['completed_at']));
        $docuseal->setDeclinedAtSeal(new \DateTime($data[0]['declined_at']));
        $docuseal->setOpenedAtSeal(new \DateTime($data[0]['opened_at']));
        $docuseal->setSentAtSeal(new \DateTime($data[0]['sent_at']));
        $docuseal->setCreatedAtSeal(new \DateTime($data[0]['created_at']));
        $docuseal->setUpdatedAtSeal(new \DateTime($data[0]['updated_at']));
        $docuseal->setStatusSeal($data[0]['status']);
        $docuseal->setValuesSeal($data[0]['values']);
        $docuseal->setEmbedSrcSeal($data[0]['embed_src']);
        $docuseal->setPrescription($prescription);
        $this->em->persist($docuseal);
        $this->em->flush();

        return $docuseal;
    }

    #[Route('prescription/{id}', name: 'app_admin_docuseal_prescription')]
    public function prescription(Prescription $prescription, HttpClientInterface $client): Response
    {
        $element = $this->getElement($prescription);
        //dd($element);

        $response = $client->request('POST', 'https://dseal.openpixl.fr/api/submissions', [
            'headers' => [
                'X-Auth-Token' => $this->docuseal_Key,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'template_id' => 3,
                'submitters' => [
                    [
                        'name' => $element['Beneficiaire'],
                        'role' => 'Première partie',
                        'email' => 'xavier.burke@openpixl.fr',
                        'values' => [
                            'Beneficiaire' => $element['Beneficiaire'],
                            'Genre' => $element['Genre'],
                            'Age' => $element['Age'],
                            'Situation' => $element['Situation'],
                            'Nom_Prescripteur' => $element['Nom_Prescripteur'],
                            'Resp_Prescripteur' => $element['Resp_Prescripteur'],
                            'Tel_Prescripteur' => $element['Tel_Prescripteur'],
                            'Email_Prescripteur' => $element['Email_Prescripteur'],
                            'Details' => $element['Details'],
                            'Base_Competences' => $element['Base_Competences'],
                            'Lieu_Mediation' => $element['Lieu_Mediation'],
                            'Compentences_Bases' => $element['Compentences_Bases'],
                            'Compentences_Bureautique' => $element['Compentences_Bureautique'],
                            'Compentences_Internet' => $element['Compentences_Internet'],
                            'Compentences_Messagerie' => $element['Compentences_Messagerie'],
                            'isAutoEval' => $element['isAutoEval'],
                            'isDigComp0' => $element['isDigComp0'],
                            'isDigComp1' => $element['isDigComp1'],
                            'isDigComp2' => $element['isDigComp2'],
                            'isDigComp3' => $element['isDigComp3'],
                            'isDigComp4' => $element['isDigComp4'],
                            'isDigComp5' => $element['isDigComp5'],
                            'Parcours' => $element['Parcours'],
                            'isAutoEvalEnd' => $element['isAutoEvalEnd'],
                            'Id_Machine' => $element['Id_Machine'],
                            'Ville' => $element['Ville'],
                            'Le' => $element['Le'],
                        ],
                    ],
                ],
            ],
        ]);

        $data = $response->toArray();

        if($data){
            $docuseal = $this->getDocuseal($data, $prescription);
            $prescription->setStep(StepPrescription::SubmissionForSigned);
            $this->em->flush();
        }

        $qrcodeImage = $this->qrcodeGenerator->generate($docuseal?->getEmbedSrcSeal());

        //dd($qrcodeImage);

        $view = $this->renderView('gestapp/prescription/include/_linkDocuseal.html.twig', [
            'embedSrc' => $docuseal->getEmbedSrcSeal(),
            'qrcodeImage' => $qrcodeImage
        ]);

        return $this->json([
            'code' => 200,
            'view' => $view,
            'embed_src' => $docuseal->getEmbedSrcSeal()
        ], 200);
    }

    #[Route('prescription/showqrcode/{id}', name: 'app_admin_docuseal_prescription_showqrcode')]
    public function prescriptionShowQrcode(Prescription $prescription, DocusealRepository $docusealRepository): Response
    {
        $docuseal = $docusealRepository->findOneBy(['prescription' => $prescription]);

        // -------------------------------------
        // PARTIE API - Information sur la soumission
        // -------------------------------------
        $api = new \Docuseal\Api($this->docuseal_Key, 'https://dseal.openpixl.fr/api');
        $submission = $api->getSubmission($docuseal->getIdSeal());

        $status = $submission['status'];

        $qrcodeImage = $this->qrcodeGenerator->generate($docuseal?->getEmbedSrcSeal());

        if($status == 'completed')
        {
            $view = $this->renderView('gestapp/prescription/include/_linkwithoutdocuseal.html.twig');
        }else{
            $view = $this->renderView('gestapp/prescription/include/_linkDocuseal.html.twig', [
                'embedSrc' => $docuseal->getEmbedSrcSeal(),
                'qrcodeImage' => $qrcodeImage
            ]);
        }

        return $this->json([
            'code' => 200,
            'view' => $view,
            'url' => $this->generateUrl('app_admin_docuseal_prescription_getdocuments', ['id' => $prescription->getId()])
        ], 200);
    }

    #[Route('prescription/getdocuments/{id}', name: 'app_admin_docuseal_prescription_getdocuments')]
    public function prescriptionGetDocs(Prescription $prescription, DocusealRepository $docusealRepository,  HttpClientInterface $client)
    {
        $docuseal =  $docusealRepository->findOneBy(['prescription' => $prescription]);

        // -------------------------------------
        // PARTIE API - Information sur la soumission
        // -------------------------------------
        $api = new \Docuseal\Api($this->docuseal_Key, 'https://dseal.openpixl.fr/api');
        $submission = $api->getSubmission($docuseal->getIdSeal());

        $docuseal->setSlugSeal($submission['slug']);
        $docuseal->setUpdatedAtSeal(new \DateTime($submission['updated_at']));
        $docuseal->setStatusSeal($submission['status']);
        $docuseal->setCompletedAtSeal(new \DateTime($submission['completed_at']));
        $this->em->flush();

        // -------------------------------------
        // PARTIE DOCUMENTS - cHARGEMENT DES FICHIERS DANS SERVEUR
        // -------------------------------------
        $documents = $submission['documents'] ?? [];
        foreach ($documents as $index => $document) {
            $pdfUrl = $document['url'];                                                 // Récupération du chemin PDF
            $responsePdf = $client->request('GET', $pdfUrl);                            // Téléchargement via HttpClient
            $slugStructure = $prescription->getPrescriptor()->getSlug();

            // DOCUMENT
            if ($responsePdf->getStatusCode() === 200) {                                // dans le cas d'une réussite
                $filename = sprintf($prescription->getRef().'_signedByDocuseal.pdf');
                $path = $this->getParameter('prescription_signed_directory').$slugStructure.'/'.$filename;
                $pathurl = $this->getParameter('prescription_signed_directory_url').$slugStructure.'/'.$filename;

                //dd($path, dirname($path));

                try{
                    if (!is_dir(dirname($path))) {
                        mkdir(dirname($path), 0775, true);
                        file_put_contents($path, $responsePdf->getContent());
                    }else{
                        file_put_contents($path, $responsePdf->getContent());
                    }
                }catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $docuseal->setPathDocSeal($pathurl);
            }
        }

        // AUDIT
        $certifUrl = $submission['audit_log_url'];                                    // Récupération du chemin Certif
        $responseCertif = $client->request('GET', $certifUrl);                      // Téléchargement via HttpClient

        $certifname = sprintf($prescription->getRef().'_certifByDocuseal.pdf');
        $certifpath = $this->getParameter('prescription_signed_directory').$slugStructure.'/'.$certifname;
        $certifpathurl = $this->getParameter('prescription_signed_directory_url').$slugStructure.'/'.$certifname;
        try{
            if (!is_dir(dirname($certifpath))) {
                mkdir(dirname($certifpath), 0775, true);
                file_put_contents($certifpath, $responseCertif->getContent());
            }else{
                file_put_contents($certifpath, $responseCertif->getContent());
            }
        }catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }
        $docuseal->setPathCertifSeal($certifpathurl);

        $prescription->setStep(StepPrescription::Signed);
        $this->em->flush();

        return $this->json([
            'code' => 200,
            'message' => 'Fichiers chargés sur le serveur'
        ], 200);
    }
}












