<?php

namespace App\EventListener;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SessionListener
{
    public function __construct(
        private Security $security,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Skip if the request is for the login route or is not a master request
        if ($request->attributes->get('_route') === 'app.login' || !$event->isMainRequest()) {
            return;
        }

        // Check if the user is authenticated
        $user = $this->security->getUser();
        if (!$user) {
            // Redirect to login if no user is authenticated
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app.login')));
        }
    }
}
