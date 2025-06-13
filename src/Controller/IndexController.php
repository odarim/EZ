<?php

namespace App\Controller;

use App\Enum\Roles;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/', name: 'app.index')]
class IndexController extends BaseController
{
    public function __invoke(): RedirectResponse
    {
        if ($this->isGranted(Roles::ROLE_ACL_ALL->value)) {
            return $this->redirectToRoute('app.admin');
        } else {
            return $this->redirectToRoute('app.client');
        }
    }
}
