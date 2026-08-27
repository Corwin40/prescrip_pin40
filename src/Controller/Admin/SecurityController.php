<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * Formulaire de connexion. Route utilisée à la fois comme `login_path` et `check_path`
     * par le firewall `form_login` de security.yaml : en GET elle affiche le formulaire,
     * l'échec d'authentification en POST y redirige avec l'erreur portée par
     * `AuthenticationUtils::getLastAuthenticationError()`.
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * Route de déconnexion. Le corps ne s'exécute jamais : Symfony intercepte `/logout`
     * au niveau du firewall (clé `logout` de security.yaml) avant d'atteindre le contrôleur.
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
