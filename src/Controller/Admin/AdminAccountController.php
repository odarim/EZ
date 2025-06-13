<?php

namespace App\Controller\Admin;

use App\Controller\AccountController;
use App\Entity\Account\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: 'app/admin/profile', name: 'app.admin.account')]
class AdminAccountController extends AccountController
{

    #[Route(path: '/{id}', name: '.profile')]
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
