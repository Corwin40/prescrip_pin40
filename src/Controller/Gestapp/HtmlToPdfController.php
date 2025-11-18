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
            'controller_name' => 'HtmlToPdfController',
        ]);
    }
}
