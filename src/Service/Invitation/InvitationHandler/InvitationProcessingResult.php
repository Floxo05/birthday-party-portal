<?php

declare(strict_types=1);

namespace App\Service\Invitation\InvitationHandler;

use App\Entity\Invitation;

readonly class InvitationProcessingResult
{

    public function __construct(
        private bool $userJoined,
        private Invitation $invitation,
    ) {
    }

    public function userJoinedSuccessfully(): bool
    {
        return $this->userJoined;
    }

    public function needsRegistration(): bool
    {
        return !$this->userJoined;
    }

    public function getInvitation(): ?Invitation
    {
        return $this->invitation;
    }
}