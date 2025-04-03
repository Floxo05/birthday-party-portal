<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Invitation\InvitationSessionManager;

use App\Service\Invitation\InvitationSessionManager\InvitationSessionManager;
use App\Service\Invitation\InvitationSessionManager\InvitationSessionManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class InvitationSessionManagerTest extends TestCase
{
    private SessionInterface $session;
    private InvitationSessionManager $manager;

    public function testStoreInvitationToken(): void
    {
        $token = 'abc123';

        $this->session
            ->expects($this->once())
            ->method('set')
            ->with(InvitationSessionManagerInterface::INVITATION_TOKEN_KEY, $token);

        $this->manager->storeInvitationToken($token);
    }

    public function testGetInvitationTokenReturnsStoredString(): void
    {
        $token = 'def456';

        $this->session
            ->method('get')
            ->with(InvitationSessionManagerInterface::INVITATION_TOKEN_KEY)
            ->willReturn($token);

        $result = $this->manager->getInvitationToken();
        $this->assertSame($token, $result);
    }

    public function testGetInvitationTokenReturnsNullIfNotSet(): void
    {
        $this->session
            ->method('get')
            ->with(InvitationSessionManagerInterface::INVITATION_TOKEN_KEY)
            ->willReturn(null);

        $result = $this->manager->getInvitationToken();
        $this->assertNull($result);
    }

    public function testGetInvitationTokenReturnsNullIfValueIsNotString(): void
    {
        $this->session
            ->method('get')
            ->with(InvitationSessionManagerInterface::INVITATION_TOKEN_KEY)
            ->willReturn(123); // z.B. manipulierte Session

        $result = $this->manager->getInvitationToken();
        $this->assertNull($result);
    }

    public function testClearInvitationToken(): void
    {
        $this->session
            ->expects($this->once())
            ->method('remove')
            ->with(InvitationSessionManagerInterface::INVITATION_TOKEN_KEY);

        $this->manager->clearInvitationToken();
    }

    protected function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack
            ->method('getSession')
            ->willReturn($this->session);

        $this->manager = new InvitationSessionManager($requestStack);
    }
}
