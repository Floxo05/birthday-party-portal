<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\PartyMember\PartyMemberRoleTranslator;

use App\Entity\PartyMember;
use App\Service\PartyMember\PartyMemberRoleTranslator\PartyMemberRoleTranslator;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class PartyMemberRoleTranslatorTest extends TestCase
{
    private TranslatorInterface $translator;
    private PartyMemberRoleTranslator $roleTranslator;

    public function testTranslatesGuestRole(): void
    {
        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('party.roles.guest', [], 'admin')
            ->willReturn('Gast');

        $result = $this->roleTranslator->translate(PartyMember::ROLE_GUEST);
        $this->assertSame('Gast', $result);
    }

    public function testTranslatesHostRole(): void
    {
        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('party.roles.host', [], 'admin')
            ->willReturn('Veranstalter');

        $result = $this->roleTranslator->translate(PartyMember::ROLE_HOST);
        $this->assertSame('Veranstalter', $result);
    }

    public function testFallbackForUnknownRole(): void
    {
        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('moderator', [], 'admin')
            ->willReturn('Moderator');

        $result = $this->roleTranslator->translate('moderator');
        $this->assertSame('Moderator', $result);
    }

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->roleTranslator = new PartyMemberRoleTranslator($this->translator);
    }
}
