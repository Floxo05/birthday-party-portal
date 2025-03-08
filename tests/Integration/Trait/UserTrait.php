<?php
declare(strict_types=1);

namespace App\Tests\Integration\Trait;

use App\Entity\UserPackage\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

trait UserTrait
{
    public function getNewUser(UserPasswordHasherInterface $hasher): User
    {
        $user = new User();
        $user
            ->setPassword($hasher->hashPassword($user, 'password'))
            ->setUsername('testuser' . random_int(1, 1000));

        return $user;
    }
}