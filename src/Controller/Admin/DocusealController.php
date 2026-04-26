<?php

namespace App\Controller\Admin;

use App\Config\StepPrescription;
use App\Entity\Gestapp\Prescription;
use App\Entity\Serv\Docuseal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/admin/docuseal/')]
final class DocusealController extends AbstractController
{
    public function __construct(
        private string $docuseal_Url,
        private string $docuseal_Key,
        public EntityManagerInterface $em,
    ) {
    }

    public function getElement(Prescription $prescription)
    {
        // Création des éléments de préremplissage
        $element = [];
        $beneficiaire = $prescription->getBeneficiaire()->getCivility() ." ".$prescription->getBeneficiaire()->getFirstname()." ".$prescription->getBeneficiaire()->getLastname() ?? '';
        $age = $prescription->getBeneficiaire()->getAgeGroup() ?? '';
        $genre = $prescription->getBeneficiaire()->getGender() ?? '';
        $situation = $prescription->getBeneficiaire()->getProfessionnalStatus() ?? '';
        $structure = $prescription->getPrescriptor()->getName() ?? '';
        $lieuMediation = $prescription->getLieuMediation()->getName() ?? '';
        $respStructure = $prescription->getPrescriptor()->getContactResponsableCivility()." ".$prescription->getPrescriptor()->getContactResponsableFirstname()." ".$prescription->getPrescriptor()->getContactResponsableLastname() ?? '';
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
        //dd($this->docuseal_Url, $this->docuseal_Key);

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
            $prescription->setStep(StepPrescription::SignedSubmission);
            $this->em->flush();
        }

        return $this->json([
            'code' => 200,
            'embed_src' => $docuseal->getEmbedSrcSeal()
        ], 200);
    }
}
