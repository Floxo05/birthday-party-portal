<?php

declare(strict_types=1);

namespace App\Tests\Service\UserMessage;

use App\Entity\Party;
use App\Entity\PartyNews;
use App\Entity\User;
use App\Entity\UserMessageStatus;
use App\Repository\PartyNewsRepository;
use App\Repository\UserMessageStatusRepository;
use App\Service\UserMessage\UserMessageManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UserMessageManagerTest extends TestCase
{
    public function testEnsureAllMessagesHaveStatusCreatesMissingStatuses(): void
    {
        $user = $this->createMock(User::class);
        $party = $this->createMock(Party::class);

        $news1 = $this->createMock(PartyNews::class);
        $news2 = $this->createMock(PartyNews::class);

        $partyNewsRepo = $this->createMock(PartyNewsRepository::class);
        $partyNewsRepo->method('findBy')->willReturn([$news1, $news2]);

        $statusRepo = $this->createMock(UserMessageStatusRepository::class);
        $statusRepo->method('findOneByUserAndPartyNews')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->exactly(2))->method('persist')->with($this->isInstanceOf(UserMessageStatus::class));
        $em->expects($this->once())->method('flush');

        $manager = new UserMessageManager($partyNewsRepo, $statusRepo, $em);
        $manager->ensureAllMessagesHaveStatus($user, $party);
    }
}
