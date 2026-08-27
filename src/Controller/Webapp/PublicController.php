<?php

namespace App\Controller\Webapp;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PublicController extends AbstractController
{
    /**
     * Page des mentions légales, accessible publiquement (liée depuis le footer).
     */
    #[Route('/mentions', name: 'app_webapp_public_mentions')]
    public function mentions(): Response
    {
        return $this->render('webapp/public/mentions.html.twig');
    }

    /**
     * Page de présentation du dispositif de don ("prezDon"), accessible publiquement
     * (liée depuis la navbar).
     */
    #[Route('/prezDon', name: 'app_webapp_public_prezDon')]
    public function prezDon(): Response
    {
        return $this->render('webapp/public/prezDon.html.twig');
    }

    /**
     * Page de contact, accessible publiquement (liée depuis le footer).
     */
    #[Route('/contact', name: 'app_webapp_public_contact')]
    public function contact(): Response
    {
        return $this->render('webapp/public/contact.html.twig');
    }
}
