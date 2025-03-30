<?php

declare(strict_types=1);

namespace App\Service\PartyMember\PartyMembershipManager;

use App\Entity\Invitation;
use App\Entity\User;
use App\Exception\Party\UserAlreadyInPartyException;
use App\Repository\PartyMemberRepository;
use App\Service\PartyMember\PartyMemberFactory\PartyMemberFactory;
use Doctrine\ORM\EntityManagerInterface;

class PartyMembershipManager implements PartyMembershipManagerInterface
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PartyMemberRepository $partyMemberRepository,
        private readonly PartyMemberFactory $partyMemberFactory,
    ) {
    }

    public function addUserToParty(User $user, Invitation $invitation): void
    {
        $party = $invitation->getParty();
        $role = $invitation->getRole();

        if (!is_string($role))
        {
            throw new \InvalidArgumentException('Die Rolle der Einladung ist ungültig.');
        }

        if ($party === null)
        {
            throw new \InvalidArgumentException('Die Einladung ist keiner Party zugeordnet.');
        }

        if ($this->partyMemberRepository->isUserInParty($user, $party))
        {
            throw new UserAlreadyInPartyException();
        }

        $partyMember = $this->partyMemberFactory->createPartyMemberByRole($role);

        $partyMember->setUser($user);
        $partyMember->setParty($party);

        $invitation->incrementUses();

        $this->entityManager->persist($partyMember);
        $this->entityManager->persist($invitation);
        $this->entityManager->flush();
    }
}