<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_admin_dashboard_index')]
    public function index(): Response
    {
        return $this->render('admin/login_dashboard/index.html.twig', [
            'controller_name' => 'LoginDashboardController',
        ]);
    }
}
