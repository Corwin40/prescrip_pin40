<?php

namespace App\Controller\Gestapp;

use App\Config\StepPrescription;
use App\Entity\Gestapp\Prescription;
use Docuseal\Api;
use Docuseal\Docuseal;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;
use Doctrine\ORM\EntityManagerInterface;

#[Route("/admin/generatepdf")]
final class HtmlToPdfController extends AbstractController
{
    private bool $viewPdf;
    public function __construct(){
        $this->viewPdf = true;
    }

    #[Route('/prescription/{id}', name: 'app_generate_prescription_pdf')]
    public function generatePrescriptionPdf(Prescription $prescription, GotenbergPdfInterface $gotenberg, EntityManagerInterface $em): Response
    {
        if($this->viewPdf == 1){
            ob_start();
            $pdf = $gotenberg
                ->html()
                ->content('gestapp/htmltopdf/prescriptionpdf.html.twig', [
                    'prescription' => $prescription,
                    'pdf' => $this->viewPdf,
                ])
                ->generate()
                //->stream() // will return directly a stream response
            ;
            $pdf->sendContent(); // envoie le PDF dans le buffer

            $pdfContent = ob_get_clean(); // récupère le binaire

            // stockage sur disque
            $filename = $prescription->getRef().'.pdf';
            $path = $this->getParameter('prescription_directory_url').$filename;

            if(!is_dir($this->getParameter('prescription_directory_url'))){
                mkdir($this->getParameter('prescription_directory_url'), 0777, true);
            }

            if(file_exists($path)){
                unlink($path);
            }
            file_put_contents($path, $pdfContent);

            // enregistrement en bdd du nom du fichier
            $prescription->setPath($this->getParameter('prescription_directory').$filename);
            $prescription->setStep(StepPrescription::GeneratePDF);
            $em->flush();

            return $this->redirectToRoute('app_gestapp_prescription_index', [], Response::HTTP_SEE_OTHER);

        }else{
            return $this->render('gestapp/htmltopdf/prescriptionpdf.html.twig', [
                'prescription' => $prescription,
                'pdf' => $this->viewPdf,
            ]);
        }
    }

    #[Route('/prescription/preview/{id}', name: 'app_prescription_preview')]
    public function preview(Prescription $prescription): Response
    {
        return $this->render('gestapp/htmltopdf/prescriptionpdf.html.twig', [
            'prescription' => $prescription,
        ]);
    }

    #[Route('/prescription/signed/{id}', name: 'app_prescription_pdf_at_signed')]
    public function signed(Prescription $prescription): Response
    {
        $filename = $prescription->getRef().'.pdf';
        $filePath = $this->getParameter('prescription_directory').$filename;

        $fileData = base64_encode(file_get_contents($filePath));

        $docuseal = new \Docuseal\Api('5eA2y44DMe7EYs1Q872cjd79NHwE2raWmbSoYxrXY5h', 'https://dseal.openpixl.fr');

        $submission = $docuseal->createSubmissionFromPdf([
            'name' => 'Rental Agreement',
            'documents' => [
                [
                    'name' => 'rental-agreement',
                    'file' => $fileData
                ]
            ],
            'submitters' => [
                [
                    'role' => 'First Party',
                    'email' => 'xavier.burke@gmail.com'
                ]
            ]
        ]);

        return $this->redirectToRoute('app_gestapp_prescription_index', [], Response::HTTP_SEE_OTHER);
    }
}
