<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Media;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Media>
 */
class MediaVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === 'view' && $subject instanceof Media;
    }

    /**
     * @param Media $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User)
        {
            return false;
        }

        $party = $subject->getParty();

        foreach ($party?->getPartyMembers() ?? [] as $member)
        {
            if ($member->getUser() === $user)
            {
                return true;
            }
        }

        return false;
    }
}