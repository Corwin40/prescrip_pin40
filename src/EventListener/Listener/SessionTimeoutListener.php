<?php

namespace App\EventListener\Listener;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

final class SessionTimeoutListener
{
    private Security $security;
    private RouterInterface $router;

    private int $timeout = 1800; // 30 minutes

    public function __construct(Security $security, RouterInterface $router)
    {
        $this->security = $security;
        $this->router = $router;
    }

    #[AsEventListener]
    public function onRequestEvent(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $session = $request->getSession();

        if (!$session || !$session->isStarted()) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user) {
            return;
        }

        $lastActivity = $session->get('lastActivity');

        if ($lastActivity !== null && (time() - $lastActivity) > $this->timeout) {

            $session->invalidate();

            $response = new RedirectResponse(
                $this->router->generate('app_login')
            );

            $event->setResponse($response);
            return;
        }

        $session->set('lastActivity', time());
    }
}
