<?php

namespace App\Controller\Client;

use App\Controller\UserController;
use App\Entity\Account\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/app/client/users', name: 'app.client.users')]
class ClientUserController extends UserController
{

    #[Route('/', name: '.index')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig');
    }

    #[Route('/new', name: '.new')]
    public function new(): Response
    {
        return $this->render('user/new.html.twig');
    }

    #[Route('/{id}', name: '.show', requirements: ['id' => '\d+'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: '.edit', requirements: ['id' => '\d+'])]
    public function edit(User $user): Response
    {
        return $this->render('user/edit.html.twig', [
            'user' => $user,
        ]);
    }
}
