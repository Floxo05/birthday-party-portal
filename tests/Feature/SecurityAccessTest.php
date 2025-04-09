<?php

declare(strict_types=1);

use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityAccessTest extends WebTestCase
{
    public function testUserCannotAccessAdmin(): void
    {
        $client = static::createClient();
        $userFactory = self::getContainer()->get(UserFactory::class);
        $user = $userFactory->create();

        $client->loginUser($user);
        $client->request('GET', '/admin');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testOrganizerCanAccessAdmin(): void
    {
        $client = static::createClient();
        $userFactory = self::getContainer()->get(UserFactory::class);
        $organizer = $userFactory->createOrganizer();

        $client->loginUser($organizer);
        $client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
    }

    public function testAdminCanAccessAdmin(): void
    {
        $client = static::createClient();
        $userFactory = self::getContainer()->get(UserFactory::class);
        $admin = $userFactory->createAdmin();

        $client->loginUser($admin);
        $client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
    }
}