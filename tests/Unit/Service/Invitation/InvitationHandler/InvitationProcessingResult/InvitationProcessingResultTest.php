<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Invitation\InvitationHandler;

use App\Entity\Invitation;
use App\Service\Invitation\InvitationHandler\InvitationProcessingResult;
use PHPUnit\Framework\TestCase;

class InvitationProcessingResultTest extends TestCase
{
    public function testReturnsCorrectStateWhenUserJoined(): void
    {
        $invitation = $this->createMock(Invitation::class);
        $result = new InvitationProcessingResult(true, $invitation);

        $this->assertTrue($result->userJoinedSuccessfully());
        $this->assertFalse($result->needsRegistration());
        $this->assertSame($invitation, $result->getInvitation());
    }

    public function testReturnsCorrectStateWhenUserDidNotJoin(): void
    {
        $invitation = $this->createMock(Invitation::class);
        $result = new InvitationProcessingResult(false, $invitation);

        $this->assertFalse($result->userJoinedSuccessfully());
        $this->assertTrue($result->needsRegistration());
        $this->assertSame($invitation, $result->getInvitation());
    }
}
