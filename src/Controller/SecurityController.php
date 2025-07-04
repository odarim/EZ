<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app.login', defaults: ['title' => 'Login'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('app.admin');
            }
            return $this->redirectToRoute('app.client');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // Handle CSRF token errors
        if ($error instanceof InvalidCsrfTokenException) {
            $error = 'Invalid CSRF token. Please try again.';
        }elseif ($error instanceof BadCredentialsException){
            $error = 'Invalid username or password. Please try again.';
        } else {
            $error = $error?->getMessage();
        }

        return $this->render('common/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route(path: '/signup', name: 'app.signup', defaults: ['title' => 'Sign Up'])]
    public function registration(): Response
    {
        if ($this->getUser()) {
            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('app.admin');
            }
            return $this->redirectToRoute('app.client');
        }

        return $this->render('security/registration.html.twig');
    }

    #[Route(path: '/logout', name: 'app.logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
