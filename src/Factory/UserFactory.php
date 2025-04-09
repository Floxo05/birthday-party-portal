<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\User;
use App\Security\Role;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Autoconfigure(public: true)]
class UserFactory
{
    private Generator $faker;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        $this->faker = FakerFactory::create('de_DE');
    }

    public function create(array $override = []): User
    {
        $username = $override['username'] ?? $this->faker->unique()->userName();
        $name = $override['name'] ?? $this->faker->name();
        $password = $override['password'] ?? 'test123';
        $roles = $override['roles'] ?? [Role::USER->value];

        $user = new User();
        $user->setUsername($username);
        $user->setName($name);
        $user->setPassword($this->hasher->hashPassword($user, $password));
        $user->setRoles($roles);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function createOrganizer(array $override = []): User
    {
        return $this->create(array_merge(['roles' => ['ROLE_ORGANIZER']], $override));
    }

    public function createAdmin(array $override = []): User
    {
        return $this->create(array_merge(['roles' => ['ROLE_ADMIN']], $override));
    }
}