<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\PartyMember\PartyMemberFactory;

use App\Entity\Guest;
use App\Entity\Host;
use App\Entity\PartyMember;
use App\Service\PartyMember\PartyMemberFactory\PartyMemberFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PartyMemberFactoryTest extends TestCase
{
    private PartyMemberFactory $factory;

    public function testCreatesGuestForGuestRole(): void
    {
        $member = $this->factory->createPartyMemberByRole(PartyMember::ROLE_GUEST);

        $this->assertInstanceOf(Guest::class, $member);
    }

    public function testCreatesHostForHostRole(): void
    {
        $member = $this->factory->createPartyMemberByRole(PartyMember::ROLE_HOST);

        $this->assertInstanceOf(Host::class, $member);
    }

    public function testThrowsExceptionForInvalidRole(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unbekannte Rolle: invalid_role');

        $this->factory->createPartyMemberByRole('invalid_role');
    }

    protected function setUp(): void
    {
        $this->factory = new PartyMemberFactory();
    }
}
