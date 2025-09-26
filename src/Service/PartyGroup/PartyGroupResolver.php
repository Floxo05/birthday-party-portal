<?php

declare(strict_types=1);

namespace App\Service\PartyGroup;

use App\Entity\Host;
use App\Entity\PartyGroup;
use App\Entity\PartyMember;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Resolves the effective members for a party group.
 * Includes all explicitly assigned members plus all hosts of the party.
 */
final class PartyGroupResolver
{
    /**
     * @return Collection<int, PartyMember>
     */
    public function resolveMembers(PartyGroup $group): Collection
    {
        $party = $group->getParty();
        $members = new ArrayCollection();

        foreach ($group->getAssignments() as $assignment)
        {
            $member = $assignment->getPartyMember();
            if ($member !== null && !$members->contains($member))
            {
                $members->add($member);
            }
        }

        if ($party !== null)
        {
            foreach ($party->getPartyMembers() as $partyMember)
            {
                if ($partyMember instanceof Host && !$members->contains($partyMember))
                {
                    $members->add($partyMember);
                }
            }
        }

        return $members;
    }
}


