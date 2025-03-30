<?php

declare(strict_types=1);

namespace App\Service\Invitation\InvitationSessionManager;

use Symfony\Component\HttpFoundation\RequestStack;

class InvitationSessionManager implements InvitationSessionManagerInterface
{

    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public function storeInvitationToken(string $token): void
    {
        $this->requestStack->getSession()->set(self::INVITATION_TOKEN_KEY, $token);
    }

    public function getInvitationToken(): ?string
    {
        $val = $this->requestStack->getSession()->get(self::INVITATION_TOKEN_KEY);
        return is_string($val) ? $val : null;
    }

    public function clearInvitationToken(): void
    {
        $this->requestStack->getSession()->remove(self::INVITATION_TOKEN_KEY);
    }
}