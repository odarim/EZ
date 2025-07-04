<?php

namespace App\Security\Provider;

use App\Entity\Account\Customer;

class CustomerProvider extends GenericUserProvider
{

    protected function getUserClass(): string
    {
        return Customer::class;
    }
}
