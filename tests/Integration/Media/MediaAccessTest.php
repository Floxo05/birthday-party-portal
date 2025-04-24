<?php

declare(strict_types=1);


namespace App\Tests\Feature\Media;

use App\Factory\MediaFactory;
use App\Factory\PartyFactory;
use App\Factory\PartyMemberFactory;
use App\Factory\UserFactory;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MediaAccessTest extends WebTestCase
{
    /**
     * @throws FilesystemException
     * @throws ORMException
     */
    public function testAuthenticatedUserCanViewOwnedMedia(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        /** Factories holen */
        /** @var UserFactory $userFactory */
        $userFactory = $container->get(UserFactory::class);
        /** @var PartyFactory $partyFactory */
        $partyFactory = $container->get(PartyFactory::class);
        /** @var PartyMemberFactory $memberFactory */
        $memberFactory = $container->get(PartyMemberFactory::class);
        /** @var MediaFactory $mediaFactory */
        $mediaFactory = $container->get(MediaFactory::class);
        /** @var FilesystemOperator $storage */
        $storage = $container->get('media.storage'); // alias zu FilesystemOperator
        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);


        // Testdaten anlegen
        $user = $userFactory->create();
        $party = $partyFactory->create();
        $memberFactory->createHost($user, $party);
        $path = $party->getId() . '/' . uniqid() . '.txt';

        $media = $mediaFactory->create($user, $party, [
            'storagePath' => $path,
            'originalFilename' => 'test.txt',
            'mimeType' => 'text/plain',
        ]);

        // Fake-Datei in MinIO schreiben
        $storage->write($path, 'This is a test file.');

        // ❗ Reload erzwingen, um sicherzustellen, dass alle Relationen geladen sind
        $em->refresh($party);

        // Login
        $client->loginUser($user);

        // Abrufen
        $client->request('GET', '/media/view/' . $media->getId());

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('text/plain', $client->getResponse()->headers->get('content-type'));
    }

    public function testUnauthenticatedUserIsDenied(): void
    {
        $client = static::createClient();

        $media = static::getContainer()->get(MediaRepository::class)->findOneBy([]);

        $client->request('GET', '/media/view/' . $media->getId());
        $this->assertResponseStatusCodeSame(302); // Redirect to login
    }

    public function testUnauthorizedUserGets403(): void
    {
        $client = static::createClient();

        /** @var UserFactory $userFactory */
        $userFactory = static::getContainer()->get(UserFactory::class);
        $otherUser = $userFactory->create();

        $client->loginUser($otherUser);

        $media = static::getContainer()->get(MediaRepository::class)->findOneBy([]);

        $client->request('GET', '/media/view/' . $media->getId());
        $this->assertResponseStatusCodeSame(403);
    }
}
