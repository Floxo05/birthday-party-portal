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
use App\Service\Invitation\InvitationLinkGenerator\InvitationLinkGeneratorInterface;
use App\Service\Invitation\InvitationManager\InvitationManagerInterface;
use App\Service\PartyMember\PartyMembershipManager\PartyMembershipManagerInterface;

class InvitationHandler implements InvitationHandlerInterface
{

    public function __construct(
        private readonly InvitationManagerInterface $invitationManager,
        private readonly PartyMembershipManagerInterface $partyMembershipService,
        private readonly InvitationLinkGeneratorInterface $invitationLinkGenerator
    ) {
    }

    public function createInvitation(
        Party $party,
        string $role,
        \DateTimeImmutable $expiresAt,
        int $maxUses = 1
    ): Invitation {
        return $this->invitationManager->createInvitation($party, $role, $expiresAt, $maxUses);
    }

    public function getInvitationLink(Invitation $invitation): string
    {
        return $this->invitationLinkGenerator->generate($invitation);
    }

    public function handleInvitation(string $token, ?User $user): InvitationProcessingResult
    {
        $invitation = $this->invitationManager->getValidInvitation($token);

        if (!$invitation)
        {
            throw new InvitationNotFoundException();
        }

        if ($this->isInvitationExpired($invitation))
        {
            throw new InvitationExpiredException();
        }

        if (!$this->isInvitationUsable($invitation))
        {
            throw new InvitationLimitReachedException();
        }

        if ($user)
        {
            try
            {
                $this->partyMembershipService->addUserToParty($user, $invitation);
                return new InvitationProcessingResult(true, $invitation);
            } catch (UserAlreadyInPartyException $e)
            {
                throw $e;
            }
        }

        return new InvitationProcessingResult(false, $invitation);
    }

    private function isInvitationExpired(Invitation $invitation): bool
    {
        return $invitation->getExpiresAt() < new \DateTimeImmutable();
    }

    private function isInvitationUsable(Invitation $invitation): bool
    {
        return $invitation->getUses() < $invitation->getMaxUses();
    }
}