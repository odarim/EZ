<?php

namespace App\Controller\Client;

use App\Controller\TaskController;
use App\Entity\Task;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/app/client/task', name: 'app.client.task')]
class ClientTaskController extends TaskController
{


    #[Route('/', name: '.index')]
    public function index(): Response
    {
        return $this->render('task/index.html.twig');
    }

    #[Route('/new', name: '.new')]
    public function new(): Response
    {
        return $this->render('task/new.html.twig');
    }

    #[Route('/{id}', name: '.show', requirements: ['id' => '\d+'])]
    public function show(Task $task): Response
    {
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/{id}/edit', name: '.edit', requirements: ['id' => '\d+'])]
    public function edit(Task $task): Response
    {
        return $this->render('task/edit.html.twig', [
            'task' => $task,
        ]);
    }
}
