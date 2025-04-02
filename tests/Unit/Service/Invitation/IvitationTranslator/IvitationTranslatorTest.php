<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Invitation\InvitationTranslator;

use App\Entity\Invitation;
use App\Entity\Party;
use App\Service\Invitation\InvitationTranslator\InvitationTranslator;
use App\Service\PartyMember\PartyMemberRoleTranslator\PartyMemberRoleTranslatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class InvitationTranslatorTest extends TestCase
{
    private PartyMemberRoleTranslatorInterface $roleTranslator;
    private TranslatorInterface $translator;
    private InvitationTranslator $invitationTranslator;

    public function testTranslateReturnsFormattedString(): void
    {
        $invitation = $this->createMock(Invitation::class);
        $party = $this->createMock(Party::class);
        $date = new \DateTimeImmutable('2025-06-01');

        $invitation->method('getParty')->willReturn($party);
        $party->method('getPartyDate')->willReturn($date);
        $party->method('getTitle')->willReturn('Sommerfest');
        $invitation->method('getRole')->willReturn('guest');

        $this->roleTranslator
            ->method('translate')
            ->with('guest')
            ->willReturn('Gast');

        $this->translator
            ->method('trans')
            ->with('invitation.message', [
                '%party%' => 'Sommerfest',
                '%date%' => '01.06.2025',
                '%role%' => 'Gast',
            ], 'admin')
            ->willReturn('Du bist als Gast zum Sommerfest am 01.06.2025 eingeladen.');

        $result = $this->invitationTranslator->translate($invitation);

        $this->assertSame('Du bist als Gast zum Sommerfest am 01.06.2025 eingeladen.', $result);
    }

    public function testTranslateThrowsIfPartyIsNull(): void
    {
        $invitation = $this->createMock(Invitation::class);
        $invitation->method('getParty')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Die Einladung kann nicht übersetzt werden.');

        $this->invitationTranslator->translate($invitation);
    }

    public function testTranslateThrowsIfPartyDateIsNull(): void
    {
        $party = $this->createMock(Party::class);
        $party->method('getPartyDate')->willReturn(null);

        $invitation = $this->createMock(Invitation::class);
        $invitation->method('getParty')->willReturn($party);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Die Einladung kann nicht übersetzt werden.');

        $this->invitationTranslator->translate($invitation);
    }

    public function testTranslateThrowsIfRoleIsNull(): void
    {
        $party = $this->createMock(Party::class);
        $party->method('getPartyDate')->willReturn(new \DateTimeImmutable());

        $invitation = $this->createMock(Invitation::class);
        $invitation->method('getParty')->willReturn($party);
        $invitation->method('getRole')->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Die Einladung kann nicht übersetzt werden.');

        $this->invitationTranslator->translate($invitation);
    }

    protected function setUp(): void
    {
        $this->roleTranslator = $this->createMock(PartyMemberRoleTranslatorInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->invitationTranslator = new InvitationTranslator(
            $this->roleTranslator,
            $this->translator
        );
    }
}
