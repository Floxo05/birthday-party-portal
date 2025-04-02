<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Invitation\InvitationManager;

use App\Entity\Invitation;
use App\Entity\Party;
use App\Entity\PartyMember;
use App\Repository\InvitationRepository;
use App\Service\Invitation\InvitationManager\InvitationManager;
use App\Service\Invitation\TokenGenerator\TokenGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\ClockInterface;

class InvitationManagerTest extends TestCase
{
    private TokenGeneratorInterface $tokenGenerator;
    private InvitationRepository $invitationRepository;
    private EntityManagerInterface $entityManager;
    private ClockInterface $clock;

    private InvitationManager $invitationManager;

    public function testCreateInvitation(): void
    {
        $this->tokenGenerator->method('generate')->willReturn('token');
        $this->entityManager->expects($this->once())->method('persist')->with($this->isInstanceOf(Invitation::class));
        $this->entityManager->expects($this->once())->method('flush');

        $party = $this->createMock(Party::class);
        $now = new \DateTimeImmutable();
        $invitation = $this->invitationManager->createInvitation($party, PartyMember::ROLE_HOST, $now, 5);

        $this->assertInstanceOf(Invitation::class, $invitation);
        $this->assertSame($party, $invitation->getParty());
        $this->assertSame(PartyMember::ROLE_HOST, $invitation->getRole());
        $this->assertSame('token', $invitation->getToken());
        $this->assertSame($now, $invitation->getExpiresAt());
        $this->assertSame(0, $invitation->getUses());
        $this->assertSame(5, $invitation->getMaxUses());
    }

    public function testGetInvitationFromValidToken(): void
    {
        $token = 'token';
        $now = new \DateTimeImmutable();

        $this->clock->method('now')->willReturn($now);

        $mockedInvitation = $this->createMock(Invitation::class);
        $mockedInvitation->method('getExpiresAt')->willReturn($now->modify('+1 day'));
        $this->invitationRepository->method('findOneBy')->willReturn($mockedInvitation);

        $invitation = $this->invitationManager->getValidInvitation($token);
        $this->assertInstanceOf(Invitation::class, $invitation);
    }

    public function testGetNullIfTokenIsInvalid(): void
    {
        $token = 'token';

        $this->invitationRepository->method('findOneBy')->willReturn(null);

        $invitation = $this->invitationManager->getValidInvitation($token);
        $this->assertNull($invitation);
    }

    public function testGetNullIfInvitationIsExpired(): void
    {
        $token = 'token';

        $now = new \DateTimeImmutable();

        $this->clock->method('now')->willReturn($now);

        $mockedInvitation = $this->createMock(Invitation::class);
        $mockedInvitation->method('getExpiresAt')->willReturn($now->modify('-1 day'));

        $this->invitationRepository->method('findOneBy')->willReturn($mockedInvitation);

        $invitation = $this->invitationManager->getValidInvitation($token);
        $this->assertNull($invitation);
    }


    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenGenerator = $this->createMock(TokenGeneratorInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->invitationRepository = $this->createMock(InvitationRepository::class);
        $this->clock = $this->createMock(ClockInterface::class);

        $this->invitationManager = new InvitationManager(
            $this->entityManager,
            $this->invitationRepository,
            $this->tokenGenerator,
            $this->clock
        );
    }


}