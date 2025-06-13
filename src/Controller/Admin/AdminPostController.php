<?php

namespace App\Controller\Admin;

use App\Controller\PostController;
use App\Entity\Post;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/app/admin/posts', name: 'app.admin.posts')]
class AdminPostController extends PostController
{


    #[Route('/', name: '.index')]
    public function index(): Response
    {
        return $this->render('post/index.html.twig');
    }

    #[Route('/new', name: '.new')]
    public function new(): Response
    {
        return $this->render('post/new.html.twig');
    }

    #[Route('/{id}', name: '.show', requirements: ['id' => '\d+'])]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: '.edit', requirements: ['id' => '\d+'])]
    public function edit(Post $post): Response
    {
        return $this->render('post/edit.html.twig', [
            'post' => $post,
        ]);
    }
}
