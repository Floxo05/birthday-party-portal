<?php

declare(strict_types=1);

namespace App\Service\Invitation\InvitationManager;

use App\Entity\Invitation;
use App\Entity\Party;

interface InvitationManagerInterface
{
    public function createInvitation(
        Party $party,
        string $role,
        \DateTimeImmutable $expiresAt,
        int $maxUses = 1
    ): Invitation;

    public function getValidInvitation(string $token): ?Invitation;
}