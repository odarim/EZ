<?php

namespace App\Security\Provider;

use App\Repository\Account\UserRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

abstract class GenericUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(protected UserRepository $userRepository)
    {
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        $this->userRepository->upgradePassword($user, $newHashedPassword);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        $userClass = $this->getUserClass();
        if (!$user instanceof $userClass) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }
        return $user;
    }

    abstract protected function getUserClass(): string;

    public function supportsClass(string $class): bool
    {
        return $this->getUserClass() === $class;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->userRepository->loadUserByIdentifier($identifier);
        $userClass = $this->getUserClass();
        if (!$user instanceof $userClass) {
            throw new UserNotFoundException();
        }
        return $user;
    }
}
