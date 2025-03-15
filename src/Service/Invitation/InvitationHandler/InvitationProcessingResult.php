<?php

declare(strict_types=1);

namespace App\Service\Invitation\InvitationHandler;

use App\Entity\Invitation;

class InvitationProcessingResult
{

    public function __construct(
        private readonly bool $userJoined,
        private readonly ?Invitation $pendingInvitation = null
    ) {
    }

    public function userJoinedSuccessfully(): bool
    {
        return $this->userJoined;
    }

    public function needsRegistration(): bool
    {
        return !$this->userJoined && $this->pendingInvitation !== null;
    }

    public function getPendingInvitation(): ?Invitation
    {
        return $this->pendingInvitation;
    }
}