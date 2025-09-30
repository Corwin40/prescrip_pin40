<?php

namespace App\Controller\Webapp;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PublicController extends AbstractController
{
    #[Route('/', name: 'app_webapp_public_accueil')]
    public function index(): Response
    {
        return $this->render('webapp/public/acceuil.html.twig', [
            'controller_name' => 'PublicController',
        ]);
    }
}
