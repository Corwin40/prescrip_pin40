<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestErrorController extends AbstractController
{
    #[Route('/test500', name: 'app_test_500')]
    public function test500(): Response
    {
        throw new \Exception("Exception volontaire pour tester l’erreur 500 !");
    }
}
