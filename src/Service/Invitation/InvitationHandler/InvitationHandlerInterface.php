<?php

declare(strict_types=1);

namespace App\Service\Invitation\InvitationHandler;

use App\Entity\Invitation;
use App\Entity\Party;
use App\Entity\User;
use App\Exception\Invitation\InvitationExpiredException;
use App\Exception\Invitation\InvitationLimitReachedException;
use App\Exception\Invitation\InvitationNotFoundException;
use App\Exception\Party\UserAlreadyInPartyException;

interface InvitationHandlerInterface
{
    public function createInvitation(
        Party $party,
        string $role,
        \DateTimeImmutable $expiresAt,
        int $maxUses = 1
    ): Invitation;

    public function getInvitationLink(Invitation $invitation): string;

    /**
     * @param string $token
     * @param User|null $user
     * @return InvitationProcessingResult
     * @throws InvitationNotFoundException
     * @throws InvitationExpiredException
     * @throws InvitationLimitReachedException
     * @throws UserAlreadyInPartyException
     */
    public function handleInvitation(string $token, ?User $user): InvitationProcessingResult;
}