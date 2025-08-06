<?php

namespace App\Service\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

readonly class UserPasswordManager
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $em,
    ) {}

    /**
     * Setzt ein neues Passwort für den Benutzer.
     */
    public function changePassword(User $user, string $plainPassword): void
    {
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        $this->em->flush();
    }

    /**
     * Prüft, ob ein Passwort korrekt ist.
     */
    public function isPasswordValid(PasswordAuthenticatedUserInterface $user, string $plainPassword): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $plainPassword);
    }

    /**
     * @throws RandomException
     */
    public function resetPassword(User $user): string
    {
        $password = bin2hex(random_bytes(4));

        $this->changePassword($user, $password);

        return $password;
    }
}
