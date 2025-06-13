<?php

namespace App\Controller\Admin;

use App\Controller\DashboardController;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: 'app/admin', name: 'app.admin')]
class AdminDashboardController extends DashboardController
{

    function getDashboardTemplate(): string
    {
        return 'admin/dashboard/index.html.twig';
    }
}
