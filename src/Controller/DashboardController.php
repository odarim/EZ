<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

Abstract class DashboardController extends BaseController
{
    #[Route('/', name: '')]
    public function index(): Response
    {
        return $this->render($this->getDashboardTemplate());
    }

    abstract function getDashboardTemplate(): string;
}
