<?php
declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\UserMessageStatus;
use App\Entity\User;
use App\Entity\PartyNews;
use App\Factory\PartyNewsFactory;
use App\Factory\UserFactory;
use App\Repository\UserMessageStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserMessageStatusRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private UserMessageStatusRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = static::getContainer()->get(UserMessageStatusRepository::class);
    }

    public function testFindOneByUserAndPartyNewsReturnsCorrectResult(): void
    {
        // Arrange: Erstelle User, News und Status
        /** @var UserFactory $userFactory */
        $userFactory = $this->getContainer()->get(UserFactory::class);
        $user = $userFactory->create();

        $partyNewsFactory = $this->getContainer()->get(PartyNewsFactory::class);
        $partyNews = $partyNewsFactory->create();

        $userMessageStatus = new UserMessageStatus();
        $userMessageStatus->setUser($user);
        $userMessageStatus->setPartyNews($partyNews);

        $this->em->persist($userMessageStatus);
        $this->em->flush();

        // Act: Suche mit Repository
        /** @var UserMessageStatus $result */
        $result = $this->repository->findOneByUserAndPartyNews($user, $partyNews);

        // Assert: Treffer prüfen
        $this->assertInstanceOf(UserMessageStatus::class, $result);
        $this->assertEquals($user->getId(), $result->getUser()->getId());
        $this->assertEquals($partyNews->getId(), $result->getPartyNews()->getId());
    }

    public function testFindOneByUserAndPartyNewsReturnsNullIfNotExists(): void
    {
        $user = new User();
        $user->setEmail('neu@example.com');
        $user->setPassword('pw');
        $this->em->persist($user);

        $news = new PartyNews();
        $news->setTitle('leer');
        $news->setContent('...');
        $this->em->persist($news);

        $this->em->flush();

        $result = $this->repository->finOneByUserAndPartyNews($user, $news);
        $this->assertNull($result);
    }
}
