<?php

namespace App\Controller\Gestapp;

use App\Entity\Gestapp\Prescription;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HtmlToPdfController extends AbstractController
{
    #[Route('/generateprescriptionpdf/{id}', name: 'app_htmltopdf_generate_prescription_pdf')]
    public function generatePrescriptionPdf(Prescription $prescription): Response
    {
        //dd($prescription);
        return $this->render('gestapp/htmltopdf/prescriptionpdf.html.twig', [
            'prescription' => $prescription,
        ]);
    }
}
