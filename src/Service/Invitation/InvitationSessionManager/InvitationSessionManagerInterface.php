<?php

declare(strict_types=1);

namespace App\Service\Invitation\InvitationSessionManager;

interface InvitationSessionManagerInterface
{
    public const INVITATION_TOKEN_KEY = 'invitation_token';

    public function storeInvitationToken(string $token): void;

    public function getInvitationToken(): ?string;

    public function clearInvitationToken(): void;
}