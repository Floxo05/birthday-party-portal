<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Guest;
use App\Entity\Host;
use App\Entity\Party;
use App\Entity\PartyMember;
use App\Entity\User;
use App\Enum\ResponseStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
readonly class PartyMemberFactory
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function createHost(User $user, Party $party): Host
    {
        $host = new Host();

        $this->create($host, $user, $party);

        return $host;
    }

    private function create(PartyMember $pm, User $user, Party $party): void
    {
        $pm->setUser($user);
        $pm->setParty($party);
        $pm->setResponseStatus(ResponseStatus::PENDING);

        $this->em->persist($pm);
        $this->em->flush();
    }

    public function createGuest(User $user, Party $party): Guest
    {
        $guest = new Guest();

        $this->create($guest, $user, $party);

        return $guest;
    }
}