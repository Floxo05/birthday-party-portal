<?php

declare(strict_types=1);

namespace App\Service\PartyMember\PartyMembershipManager;

use App\Entity\Invitation;
use App\Entity\User;
use App\Exception\Party\UserAlreadyInPartyException;

interface PartyMembershipManagerInterface
{
    /**
     * @param User $user
     * @param Invitation $invitation
     * @return void
     * @throws UserAlreadyInPartyException
     */
    public function addUserToParty(User $user, Invitation $invitation): void;
}