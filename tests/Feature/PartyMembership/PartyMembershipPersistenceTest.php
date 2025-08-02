<?php

declare(strict_types=1);

namespace App\Tests\Feature\PartyMembership;

use App\Enum\ResponseStatus;
use App\Factory\InvitationFactory;
use App\Factory\PartyFactory;
use App\Factory\UserFactory;
use App\Repository\PartyMemberRepository;
use App\Service\PartyMember\PartyMembershipManager\PartyMembershipManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PartyMembershipPersistenceTest extends KernelTestCase
{
    public function testAddUserToPartyPersistsPartyMember(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        /** @var PartyMembershipManagerInterface $partyMembershipManager */
        $partyMembershipManager = $container->get(PartyMembershipManagerInterface::class);
        /** @var PartyMemberRepository $partyMemberRepository */
        $partyMemberRepository = $container->get(PartyMemberRepository::class);
        /** @var UserFactory $userFactory */
        $userFactory = $container->get(UserFactory::class);
        /** @var PartyFactory $partyFactory */
        $partyFactory = $container->get(PartyFactory::class);
        /** @var InvitationFactory $invitationFactory */
        $invitationFactory = $container->get(InvitationFactory::class);

        $user = $userFactory->create();
        $party = $partyFactory->create();
        $invitation = $invitationFactory->create($party);

        // Methode ausführen
        $partyMembershipManager->addUserToParty($user, $invitation);

        // Datenbank prüfen: Gibt es einen PartyMember mit diesem User und dieser Party?
        $partyMember = $partyMemberRepository->findOneBy([
            'user' => $user,
            'party' => $party,
        ]);

        $this->assertSame(ResponseStatus::PENDING, $partyMember->getResponseStatus());
        $this->assertNotNull($partyMember, 'Ein PartyMember sollte existieren.');
    }
}
