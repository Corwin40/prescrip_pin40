<?php

namespace App\Controller\Gestapp;

use App\Entity\Gestapp\Prescription;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HtmlToPdfController extends AbstractController
{
    #[Route('/generateprescriptionpdf/{id}', name: 'app_htmltopdf_generate_prescription_pdf')]
    public function generatePrescriptionPdf(Prescription $prescription, Pdf $knpSnappyPdf): Response
    {

        $html = $this->render('gestapp/htmltopdf/prescriptionpdf.html.twig', [
            'prescription' => $prescription,
        ]);

        return new PdfResponse(
            $knpSnappyPdf->getOutputFromHtml($html),
            'file.pdf'
        );

    }
    #[Route('/prescription/preview/{id}', name: 'app_prescription_preview')]
    public function preview(Prescription $prescription): Response
    {
        return $this->render('gestapp/htmltopdf/prescriptionpdf.html.twig', [
            'prescription' => $prescription,
        ]);
    }
}
