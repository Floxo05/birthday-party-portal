<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Party;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
class PartyFactory
{
    private Generator $faker;

    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
        $this->faker = FakerFactory::create('de_DE');
    }

    public function create(array $override = []): Party
    {
        $date = $override['date'] ?? $this->faker->dateTimeBetween('+1 day', '+1 year');
        $party = new Party();
        $party->setTitle($override['title'] ?? $this->faker->company());
        $party->setPartyDate($date);
        $party->setRsvpDeadline($date);


        $this->em->persist($party);
        $this->em->flush();

        return $party;
    }
}
