<?php

declare(strict_types=1);

namespace App\tests\Integration\Service\UserMessage;

use App\Event\BeforeLoadDataForPartyEvent;
use App\Factory\PartyFactory;
use App\Factory\PartyNewsFactory;
use App\Factory\UserFactory;
use App\Repository\PartyNewsRepository;
use App\Repository\UserMessageStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UserMessageEventIntegrationTest extends KernelTestCase
{
    public function testEventTriggersMessageStatusCreation(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $dispatcher = $container->get(EventDispatcherInterface::class);
        $partyNewsRepo = $container->get(PartyNewsRepository::class);
        /** @var UserMessageStatusRepository $messageStatusRepo */
        $messageStatusRepo = $container->get(UserMessageStatusRepository::class);
        $em = $container->get(EntityManagerInterface::class);

        /** @var PartyFactory $partyFactory */
        $partyFactory = $container->get(PartyFactory::class);
        /** @var PartyNewsFactory $partyNewsFactory */
        $partyNewsFactory = $container->get(PartyNewsFactory::class);
        /** @var UserFactory $userFactory */
        $userFactory = $container->get(UserFactory::class);


        $party = $partyFactory->create();
        $news = $partyNewsFactory->create($party);
        $user = $userFactory->create();

        // Act: Event dispatchen
        $dispatcher->dispatch(new BeforeLoadDataForPartyEvent($user, $party));

        // Assert: MessageStatus wurde erstellt
        $status = $messageStatusRepo->findOneByUserAndPartyNews($user, $news);
        self::assertNotNull($status);
        self::assertEquals($user->getId(), $status->getUser()->getId());
        static::assertNull($status->getReadAt());
    }
}