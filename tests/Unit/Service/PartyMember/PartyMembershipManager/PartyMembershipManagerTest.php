<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\PartyMember\PartyMembershipManager;

use App\Entity\Invitation;
use App\Entity\Party;
use App\Entity\PartyMember;
use App\Entity\User;
use App\Exception\Party\UserAlreadyInPartyException;
use App\Repository\PartyMemberRepository;
use App\Service\PartyMember\PartyMemberFactory\PartyMemberFactory;
use App\Service\PartyMember\PartyMembershipManager\PartyMembershipManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class PartyMembershipManagerTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private PartyMemberRepository $partyMemberRepository;
    private PartyMemberFactory $partyMemberFactory;
    private PartyMembershipManager $manager;

    public function testAddUserToPartySuccessfully(): void
    {
        $user = $this->createMock(User::class);
        $party = $this->createMock(Party::class);
        $invitation = $this->createMock(Invitation::class);
        $partyMember = $this->createMock(PartyMember::class);

        $invitation->method('getRole')->willReturn('host');
        $invitation->method('getParty')->willReturn($party);
        $invitation->expects($this->once())->method('incrementUses');

        $this->partyMemberRepository
            ->method('isUserInParty')
            ->with($user, $party)
            ->willReturn(false);

        $this->partyMemberFactory
            ->method('createPartyMemberByRole')
            ->with('host')
            ->willReturn($partyMember);

        $partyMember->expects($this->once())->method('setUser')->with($user);
        $partyMember->expects($this->once())->method('setParty')->with($party);

        $this->entityManager->expects($this->exactly(2))->method('persist')->withConsecutive(
            [$partyMember],
            [$invitation]
        );
        $this->entityManager->expects($this->once())->method('flush');

        $this->manager->addUserToParty($user, $invitation);
    }

    public function testThrowsIfRoleIsNotString(): void
    {
        $invitation = $this->createMock(Invitation::class);
        $invitation->method('getRole')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Die Rolle der Einladung ist ungültig.');

        $this->manager->addUserToParty($this->createMock(User::class), $invitation);
    }

    public function testThrowsIfPartyIsNull(): void
    {
        $invitation = $this->createMock(Invitation::class);
        $invitation->method('getRole')->willReturn('host');
        $invitation->method('getParty')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Die Einladung ist keiner Party zugeordnet.');

        $this->manager->addUserToParty($this->createMock(User::class), $invitation);
    }

    public function testThrowsIfUserAlreadyInParty(): void
    {
        $user = $this->createMock(User::class);
        $party = $this->createMock(Party::class);
        $invitation = $this->createMock(Invitation::class);

        $invitation->method('getRole')->willReturn('host');
        $invitation->method('getParty')->willReturn($party);

        $this->partyMemberRepository
            ->method('isUserInParty')
            ->with($user, $party)
            ->willReturn(true);

        $this->expectException(UserAlreadyInPartyException::class);

        $this->manager->addUserToParty($user, $invitation);
    }

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->partyMemberRepository = $this->createMock(PartyMemberRepository::class);
        $this->partyMemberFactory = $this->createMock(PartyMemberFactory::class);

        $this->manager = new PartyMembershipManager(
            $this->entityManager,
            $this->partyMemberRepository,
            $this->partyMemberFactory
        );
    }
}
