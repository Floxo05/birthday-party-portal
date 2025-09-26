<?php

declare(strict_types=1);

namespace App\Service\PartyMember\PartyMembershipManager;

use App\Entity\Invitation;
use App\Entity\Party;
use App\Entity\User;
use App\Exception\Party\UserAlreadyInPartyException;
use App\Form\Party\PartyResponseFormModel;
use App\Entity\PartyMember;

interface PartyMembershipManagerInterface
{
    /**
     * @param User $user
     * @param Invitation $invitation
     * @return void
     * @throws UserAlreadyInPartyException
     */
    public function addUserToParty(User $user, Invitation $invitation): void;

    public function setResponseForUser(User $user, Party $party, PartyResponseFormModel $model): void;

    public function getMembershipForUser(User $user, Party $party): ?PartyMember;
}