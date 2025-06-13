<?php

namespace App\Controller\Client;

use App\Controller\DashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: 'app/client', name: 'app.client')]
class ClientDashboardController extends DashboardController
{

    function getDashboardTemplate(): string
    {
        return 'client/dashboard/index.html.twig';
    }
}
