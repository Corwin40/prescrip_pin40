<?php

namespace App\EventListener\Listener;

use App\Repository\MemberRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

final class FirstRunSetupListener
{
    public function __construct(
        private MemberRepository $memberRepository,
        private RouterInterface $router
    ) {
    }

    // Doit s'exécuter après le RouterListener (32, pour connaître la route) mais avant
    // le pare-feu de sécurité (8, qui sinon redirige vers /login avant nous sur les routes protégées).
    #[AsEventListener(priority: 20)]
    public function onRequestEvent(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $route = $event->getRequest()->attributes->get('_route');

        if ($route === 'app_setup' || $route === null) {
            return;
        }

        if ($this->memberRepository->count([]) > 0) {
            return;
        }

        $event->setResponse(new RedirectResponse(
            $this->router->generate('app_setup')
        ));
    }
}
