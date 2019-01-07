<?php

namespace App\Services;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

class UserService
{
    private $tokenStorage;
    private $security;

    public function __construct(TokenStorageInterface $storage, Security $security)
    {
        $this->tokenStorage = $storage;
        $this->security = $security;
    }

    public function getCurrentUser(): ?User
    {
        $token = $this->tokenStorage->getToken();
        if ($token instanceof TokenInterface) {
            /** @var User $user */
            $user = $token->getUser();
            return $user;
        } else {
            return null;
        }
    }

    public function accessControlToTask(Task $task): bool
    {
        return $this->security->isGranted('ROLE_ADMIN')
            || $task->getUser() === $this->getCurrentUser();
    }
}
