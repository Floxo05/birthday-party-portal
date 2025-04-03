<?php

declare(strict_types=1);

namespace App\Service\Invitation\InvitationSessionManager;

use Symfony\Component\HttpFoundation\RequestStack;

readonly class InvitationSessionManager implements InvitationSessionManagerInterface
{

    public function __construct(
        private RequestStack $requestStack
    ) {
    }

    public function storeInvitationToken(string $token): void
    {
        $this->requestStack->getSession()->set(self::INVITATION_TOKEN_KEY, $token);
    }

    public function getInvitationToken(): ?string
    {
        $val = $this->requestStack->getSession()->get(self::INVITATION_TOKEN_KEY);

        if (!is_string($val))
        {
            return null;
        }

        return $val;
    }

    public function clearInvitationToken(): void
    {
        $this->requestStack->getSession()->remove(self::INVITATION_TOKEN_KEY);
    }
}