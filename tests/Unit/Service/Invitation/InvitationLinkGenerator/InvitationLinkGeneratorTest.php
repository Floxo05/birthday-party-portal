<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\nvitation\InvitationLinkGenerator;

use App\Entity\Invitation;
use App\Service\Invitation\InvitationLinkGenerator\InvitationLinkGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InvitationLinkGeneratorTest extends TestCase
{
    public function testGenerateLinkFromInvitation(): void
    {
        $invitation = $this->createMock(Invitation::class);
        $invitation->method('getToken')->willReturn('token');

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with('app_invite', ['token' => $invitation->getToken()], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('https://example.com/invite/token');

        $invitationLinkGenerator = new InvitationLinkGenerator($urlGenerator);
        $link = $invitationLinkGenerator->generate($invitation);

        $this->assertSame('https://example.com/invite/token', $link);
    }

    public function testThrowExceptionIfTokenIsNull(): void
    {
        $invitation = $this->createMock(Invitation::class);
        $invitation->method('getToken')->willReturn(null);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->never())->method('generate');

        $this->expectException(\LogicException::class);

        $invitationLinkGenerator = new InvitationLinkGenerator($urlGenerator);
        $invitationLinkGenerator->generate($invitation);
    }


}