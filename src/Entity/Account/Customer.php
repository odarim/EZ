<?php

namespace App\Entity\Account;

use App\Entity\Estate\Estate;
use App\Enum\Roles;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table(name: '`customers`')]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username.')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email.')]
class Customer extends User
{

    public function __construct()
    {
        parent::__construct();

        $this->username = null;
        $this->roles = [Roles::ROLE_ACL_CUSTOMER->value];
        $this->estates = new ArrayCollection();
    }

    public function setUsername(?string $username = null): static
    {
        parent::setUsername($username ?? '');
        $this->username = $username;

        return $this;
    }

}
