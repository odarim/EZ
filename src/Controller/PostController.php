<?php

namespace App\Controller;

use App\Entity\Post;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/posts', name: 'posts')]
class PostController extends BaseController
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
