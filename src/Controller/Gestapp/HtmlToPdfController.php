<?php

namespace App\Controller\Gestapp;

use App\Config\StepPrescription;
use App\Entity\Gestapp\Prescription;
use Docuseal\Api;
use Docuseal\Docuseal;
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;
use Sensiolabs\GotenbergBundle\Processor\FileProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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
            $filenameNoExt = $prescription->getRef().'_original';
            $slugStructure = $prescription->getPrescriptor()->getSlug();
            $dir = $this->getParameter('prescription_original_directory').$slugStructure;
            $path_url = $this->getParameter('prescription_original_directory_url').$slugStructure.'/'.$filenameNoExt.'.pdf';

            if(!is_dir($dir)){
                mkdir($dir, 0777, true);
            }

            $gotenberg
                ->html()
                ->content('gestapp/htmltopdf/prescriptionpdf.html.twig', [
                    'prescription' => $prescription,
                    'pdf' => $this->viewPdf,
                ])
                ->fileName($filenameNoExt)
                ->processor(new FileProcessor(new Filesystem(), $dir))
                ->generate()
                ->process()
            ;

            $prescription->setPath($path_url);
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

    #[Route('/regenerate_prescription/{id}', name: 'app_regenerate_prescription_pdf')]
    public function regeneratePrescriptionPdf(Prescription $prescription, GotenbergPdfInterface $gotenberg, EntityManagerInterface $em): Response
    {
        if($this->viewPdf == 1){
            $filenameNoExt = $prescription->getRef().'_original';
            $slugStructure = $prescription->getPrescriptor()->getSlug();
            $dir = $this->getParameter('prescription_original_directory').$slugStructure;
            $path_url = $this->getParameter('prescription_original_directory_url').$slugStructure.'/'.$filenameNoExt.'.pdf';

            if(!is_dir($dir)){
                mkdir($dir, 0777, true);
            }

            $gotenberg
                ->html()
                ->content('gestapp/htmltopdf/prescriptionpdf.html.twig', [
                    'prescription' => $prescription,
                    'pdf' => $this->viewPdf,
                ])
                ->fileName($filenameNoExt)
                ->processor(new FileProcessor(new Filesystem(), $dir))
                ->generate()
                ->process()
            ;

            $prescription->setPath($path_url);
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
}
