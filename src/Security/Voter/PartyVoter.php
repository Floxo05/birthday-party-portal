<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Party;
use App\Entity\User;
use App\Repository\PartyMemberRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class PartyVoter extends Voter
{
    public const ACCESS = 'PARTY_ACCESS';

    public function __construct(private readonly PartyMemberRepository $partyMemberRepository)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::ACCESS && $subject instanceof Party;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $party = $subject; /** @var Party $party */

        return $this->partyMemberRepository->isUserInParty($user, $party);
    }
}


