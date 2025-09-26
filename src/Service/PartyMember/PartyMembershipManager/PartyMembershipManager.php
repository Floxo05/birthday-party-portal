<?php

declare(strict_types=1);

namespace App\Service\PartyMember\PartyMembershipManager;

use App\Entity\Invitation;
use App\Entity\Party;
use App\Entity\PartyMember;
use App\Entity\User;
use App\Enum\ResponseStatus;
use App\Exception\Party\UserAlreadyInPartyException;
use App\Repository\PartyMemberRepository;
use App\Service\PartyMember\PartyMemberFactory\PartyMemberFactory;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Party\PartyResponseFormModel;

readonly class PartyMembershipManager implements PartyMembershipManagerInterface
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private PartyMemberRepository $partyMemberRepository,
        private PartyMemberFactory $partyMemberFactory,
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
        $partyMember->setResponseStatus(ResponseStatus::PENDING);

        $invitation->incrementUses();

        $this->entityManager->persist($partyMember);
        $this->entityManager->persist($invitation);
        $this->entityManager->flush();
    }

    public function setResponseForUser(User $user, Party $party, PartyResponseFormModel $model): void
    {
        $partyMember = $this->partyMemberRepository->findOneByUserAndParty($user, $party);

        if (!$partyMember instanceof PartyMember)
        {
            throw new \RuntimeException('Mitgliedschaft nicht gefunden.');
        }

        if ($model->responseStatus === ResponseStatus::ACCEPTED)
        {
            $partyMember->setResponseStatus(ResponseStatus::ACCEPTED);
            $partyMember->setExtraGuests($model->plusGuests);
        }
        elseif ($model->responseStatus === ResponseStatus::DECLINED)
        {
            $partyMember->setResponseStatus(ResponseStatus::DECLINED);
            $partyMember->setExtraGuests(null);
        }

        $this->entityManager->persist($partyMember);
        $this->entityManager->flush();
    }

    public function getMembershipForUser(User $user, Party $party): ?PartyMember
    {
        return $this->partyMemberRepository->findOneByUserAndParty($user, $party);
    }
}