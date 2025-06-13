<?php

namespace App\Controller;

use App\Entity\Account\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/profile', name: 'account')]
class AccountController extends BaseController
{

    #[Route(path: '/{id}', name: 'profile', methods: ['GET'])]
    public function profile(User $user): Response
    {
        $currentUser = $this->getUser();

        if ($currentUser == $user || $currentUser->getId() == $user->getId()) {
            return $this->render('account/profile.html.twig', [
                'user' => $user,
            ]);
        }

        return $this->redirectToRoute('app.dashboard');
    }

}
