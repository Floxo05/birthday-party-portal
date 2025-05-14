<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Invitation;
use App\Entity\Party;
use App\Entity\PartyMember;
use App\Service\Invitation\InvitationManager\InvitationManagerInterface;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
readonly class InvitationFactory
{
    private Generator $faker;

    public function __construct(
        private InvitationManagerInterface $invitationManager
    ) {
        $this->faker = FakerFactory::create('de_DE');
    }

    public function create(Party $party, array $override = []): Invitation
    {
        return $this->invitationManager->createInvitation(
            $party,
            $override['role'] ?? PartyMember::ROLE_GUEST,
            \DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('+1 day', '+2 days')),
            $override['maxUses'] ?? $this->faker->numberBetween(1, 100),
        );
    }
}