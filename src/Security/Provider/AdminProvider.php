<?php

namespace App\Security\Provider;

use App\Entity\Account\Admin;

class AdminProvider extends GenericUserProvider
{

    protected function getUserClass(): string
    {
        return Admin::class;
    }
}
