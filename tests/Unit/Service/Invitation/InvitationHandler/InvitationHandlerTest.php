<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Invitation\InvitationHandler;

use App\Entity\Invitation;
use App\Entity\Party;
use App\Entity\User;
use App\Exception\Invitation\InvitationExpiredException;
use App\Exception\Invitation\InvitationLimitReachedException;
use App\Exception\Invitation\InvitationNotFoundException;
use App\Exception\Party\UserAlreadyInPartyException;
use App\Service\Invitation\InvitationHandler\InvitationHandler;
use App\Service\Invitation\InvitationHandler\InvitationProcessingResult;
use App\Service\Invitation\InvitationLinkGenerator\InvitationLinkGeneratorInterface;
use App\Service\Invitation\InvitationManager\InvitationManagerInterface;
use App\Service\PartyMember\PartyMembershipManager\PartyMembershipManagerInterface;
use PHPUnit\Framework\TestCase;

class InvitationHandlerTest extends TestCase
{
    private InvitationManagerInterface $invitationManager;
    private PartyMembershipManagerInterface $membershipManager;
    private InvitationHandler $handler;
    private InvitationLinkGeneratorInterface $linkGenerator;

    public function testThrowsExceptionWhenInvitationNotFound(): void
    {
        $this->invitationManager
            ->method('getValidInvitation')
            ->willReturn(null);

        $this->expectException(InvitationNotFoundException::class);

        $this->handler->handleInvitation('invalid-token', null);
    }

    public function testThrowsExceptionWhenInvitationIsExpired(): void
    {
        $invitation = $this->createConfiguredMock(Invitation::class, [
            'getExpiresAt' => new \DateTimeImmutable('-1 day'),
        ]);

        $this->invitationManager
            ->method('getValidInvitation')
            ->willReturn($invitation);

        $this->expectException(InvitationExpiredException::class);

        $this->handler->handleInvitation('expired-token', null);
    }

    public function testThrowsExceptionWhenInvitationIsUsedUp(): void
    {
        $invitation = $this->createConfiguredMock(Invitation::class, [
            'getExpiresAt' => new \DateTimeImmutable('+1 day'),
            'getUses' => 3,
            'getMaxUses' => 3,
        ]);

        $this->invitationManager
            ->method('getValidInvitation')
            ->willReturn($invitation);

        $this->expectException(InvitationLimitReachedException::class);

        $this->handler->handleInvitation('used-up-token', null);
    }

    public function testThrowsExceptionWhenUserIsAlreadyInParty(): void
    {
        $invitation = $this->createConfiguredMock(Invitation::class, [
            'getExpiresAt' => new \DateTimeImmutable('+1 day'),
            'getUses' => 0,
            'getMaxUses' => 1,
        ]);

        $this->invitationManager
            ->method('getValidInvitation')
            ->willReturn($invitation);

        $this->membershipManager
            ->method('addUserToParty')
            ->willThrowException(new UserAlreadyInPartyException());

        $this->expectException(UserAlreadyInPartyException::class);

        $this->handler->handleInvitation('valid-token', $this->createMock(User::class));
    }

    public function testReturnsResultWhenUserIsAdded(): void
    {
        $invitation = $this->createConfiguredMock(Invitation::class, [
            'getExpiresAt' => new \DateTimeImmutable('+1 day'),
            'getUses' => 0,
            'getMaxUses' => 5,
        ]);

        $user = $this->createMock(User::class);

        $this->invitationManager
            ->method('getValidInvitation')
            ->willReturn($invitation);

        $this->membershipManager
            ->expects($this->once())
            ->method('addUserToParty')
            ->with($user, $invitation);

        $result = $this->handler->handleInvitation('valid-token', $user);

        $this->assertInstanceOf(InvitationProcessingResult::class, $result);
        $this->assertTrue($result->userJoinedSuccessfully());
        $this->assertSame($invitation, $result->getInvitation());
    }

    public function testReturnsResultWhenNoUserIsGiven(): void
    {
        $invitation = $this->createConfiguredMock(Invitation::class, [
            'getExpiresAt' => new \DateTimeImmutable('+1 day'),
            'getUses' => 0,
            'getMaxUses' => 5,
        ]);

        $this->invitationManager
            ->method('getValidInvitation')
            ->willReturn($invitation);

        $this->membershipManager
            ->expects($this->never())
            ->method('addUserToParty');

        $result = $this->handler->handleInvitation('valid-token', null);

        $this->assertInstanceOf(InvitationProcessingResult::class, $result);
        $this->assertFalse($result->userJoinedSuccessfully());
        $this->assertSame($invitation, $result->getInvitation());
    }

    public function testCreateInvitationDelegatesToManager(): void
    {
        $party = $this->createMock(Party::class);
        $invitation = $this->createMock(Invitation::class);

        $this->invitationManager
            ->expects($this->once())
            ->method('createInvitation')
            ->with($party, 'guest', $this->isInstanceOf(\DateTimeImmutable::class), 1)
            ->willReturn($invitation);

        $result = $this->handler->createInvitation($party, 'guest', new \DateTimeImmutable(), 1);

        $this->assertSame($invitation, $result);
    }

    public function testGetInvitationLinkReturnsGeneratedLink(): void
    {
        $invitation = $this->createMock(Invitation::class);

        $this->linkGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($invitation)
            ->willReturn('https://example.com/invite');

        $result = $this->handler->getInvitationLink($invitation);

        $this->assertSame('https://example.com/invite', $result);
    }

    protected function setUp(): void
    {
        $this->invitationManager = $this->createMock(InvitationManagerInterface::class);
        $this->membershipManager = $this->createMock(PartyMembershipManagerInterface::class);
        $this->linkGenerator = $this->createMock(InvitationLinkGeneratorInterface::class);

        $this->handler = new InvitationHandler(
            $this->invitationManager,
            $this->membershipManager,
            $this->linkGenerator
        );
    }

}
