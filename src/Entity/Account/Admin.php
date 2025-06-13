<?php

namespace App\Entity\Account;

use App\Enum\Roles;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table(name: '`admins`')]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username.')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email.')]
class Admin extends User
{
    public function __construct()
    {
        parent::__construct();

        $this->roles = [Roles::ROLE_ACL_ALL->value];
    }
}