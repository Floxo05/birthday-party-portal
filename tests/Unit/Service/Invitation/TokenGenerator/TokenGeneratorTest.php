<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Invitation\TokenGenerator;

use App\Repository\InvitationRepository;
use App\Service\Invitation\TokenGenerator\TokenGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class TokenGeneratorTest extends TestCase
{
    public function testGeneratesTokenThatDoesNotExistImmediately(): void
    {
        $repo = $this->createMock(InvitationRepository::class);
        $repo
            ->method('findOneBy')
            ->willReturn(null); // Token ist frei

        $generator = new TokenGenerator($repo);

        $token = $generator->generate();

        $this->assertTrue(Uuid::isValid($token));
    }

    public function testRetriesIfTokenAlreadyExists(): void
    {
        $repo = $this->createMock(InvitationRepository::class);

        $repo
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturnOnConsecutiveCalls(
                new \stdClass(), // Token belegt
                null             // Token frei
            );

        $generator = new TokenGenerator($repo);
        $token = $generator->generate();

        $this->assertTrue(Uuid::isValid($token));
    }
}
