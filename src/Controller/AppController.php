<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AppController extends AbstractController
{
    #[Route('/', name: 'app_ppin40_entry')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_admin_dashboard_index');
    }
}
