<?php

namespace App\Controller\Gestapp;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HtmlToPdfController extends AbstractController
{
    #[Route('/htmltopdf', name: 'app_html_to_pdf')]
    public function index(): Response
    {
        return $this->render('gestapp/htmltopdf/index.html.twig', [

            // --- envoyer les variables du beneficiaire ---
            'civility' => '',
            'lastname' => '',
            'firstname' => '',
            'gender' => '',
            'AgeGroup' => '',
            'professionnalStatus' => '',

            // --- envoyer les variables du prescripteur ---
            'nameStructure' => '',
            'contactResponsableLastname' => '',
            'contactResponsableFirstname' => '',
            'contactPhone' => '',
            'contactEmail' => '',

            // --- envoyer les variables de la date et du lieu de creation de la prescription ---
            'lieuMediation' => '',
            'createdAT' => '',
            'matriculEquipment' => '',

            // envoyer les variables booleenne des auto eva et autoevaend
            'isAutoEva' => false,
            'isAutoEvaEnd' => false,
        ]);
    }
}
