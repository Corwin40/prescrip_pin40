<?php

namespace App\Controller\Gestapp;

use App\Config\StepPrescription;
use App\Entity\Gestapp\Prescription;
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;
use Sensiolabs\GotenbergBundle\Processor\FileProcessor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Génération du PDF de prescription via Gotenberg (conversion HTML → PDF du template
 * `gestapp/htmltopdf/prescriptionpdf.html.twig`).
 */
#[Route("/admin/generatepdf")]
final class HtmlToPdfController extends AbstractController
{
    /**
     * Première génération du PDF original d'une prescription : rend le template en PDF
     * via Gotenberg, l'écrit sur disque (`prescription_original_directory`), enregistre
     * le chemin sur la prescription et la fait passer à l'étape `GeneratePDF`.
     */
    #[Route('/prescription/{id}', name: 'app_generate_prescription_pdf')]
    public function generatePrescriptionPdf(Prescription $prescription, GotenbergPdfInterface $gotenberg, EntityManagerInterface $em): Response
    {
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
                'pdf' => true,
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
    }

    /**
     * Identique à `generatePrescriptionPdf()` mais sans changer l'étape de la prescription
     * (regénère le PDF au même chemin, pour une prescription déjà passée `GeneratePDF`).
     * **Aucune vue ni script du projet n'appelle cette route** — voir Observations
     * dans `docs/controllers/gestapp-htmltopdf.md`.
     */
    #[Route('/regenerate_prescription/{id}', name: 'app_regenerate_prescription_pdf')]
    public function regeneratePrescriptionPdf(Prescription $prescription, GotenbergPdfInterface $gotenberg, EntityManagerInterface $em): Response
    {
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
                'pdf' => true,
            ])
            ->fileName($filenameNoExt)
            ->processor(new FileProcessor(new Filesystem(), $dir))
            ->generate()
            ->process()
        ;

        $prescription->setPath($path_url);
        $em->flush();

        return $this->redirectToRoute('app_gestapp_prescription_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Aperçu HTML (non converti en PDF) du template de prescription.
     * **Aucune vue ni script du projet n'appelle cette route** — voir Observations
     * dans `docs/controllers/gestapp-htmltopdf.md`.
     */
    #[Route('/prescription/preview/{id}', name: 'app_prescription_preview')]
    public function preview(Prescription $prescription): Response
    {
        return $this->render('gestapp/htmltopdf/prescriptionpdf.html.twig', [
            'prescription' => $prescription,
        ]);
    }
}
