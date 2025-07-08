<?php

namespace App\Validator;

use App\Entity\Account\User;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use function Symfony\Component\String\u;

class CreateUserCommandValidator
{
    public function validateUsername(?string $username): string
    {
        if (empty($username)) {
            throw new \InvalidArgumentException('The username can not be empty.');
        }

        if (u($username)->trim()->length() < 4) {
            throw new \InvalidArgumentException('The username must be at least 4 characters long.');
        }

        return $username;
    }

    public function validateEmail(?string $email): string
    {
        if (empty($email)) {
            throw new \InvalidArgumentException('The email can not be empty.');
        }

        if (null === u($email)->indexOf('@')) {
            throw new \InvalidArgumentException('The email should look like a real email.');
        }

        return $email;
    }

    public function validatePassword(?string $plainPassword): string
    {
        if (empty($plainPassword)) {
            throw new \InvalidArgumentException('The password can not be empty.');
        }

        if (u($plainPassword)->trim()->length() < 6) {
            throw new \InvalidArgumentException('The password must be at least 6 characters long.');
        }

        if (1 !== preg_match(User::PASSWORD_PATTERN, $plainPassword)) {
            throw new \InvalidArgumentException(User::PASSWORD_MESSAGE);
        }

        return $plainPassword;
    }

    public function validateLastName(?string $fullName): string
    {
        if (empty($fullName)) {
            throw new InvalidArgumentException('The last name can not be empty.');
        }

        return $fullName;
    }

    public function validateFirstName(?string $fullName): string
    {
        if (empty($fullName)) {
            throw new InvalidArgumentException('The first name can not be empty.');
        }

        return $fullName;
    }
}
