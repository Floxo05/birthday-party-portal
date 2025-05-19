<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\Party;
use App\Entity\PartyNews;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
class PartyNewsFactory
{
    private Generator $faker;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PartyFactory $partyFactory
    ) {
        $this->faker = FakerFactory::create('de_DE');
    }

    public function create(Party $party = null, array $override = []): PartyNews
    {
        if ($party === null) {
            $party = $this->partyFactory->create($override);
        }

        $date = $override['date'] ?? new \DateTimeImmutable('now');
        $partyNews = new PartyNews();
        $partyNews->setParty($party);
        $partyNews->setCreatedAt($date);
        $partyNews->setText($override['text'] ?? $this->faker->text());
        $partyNews->setMedia($override['media'] ?? null);
        $partyNews->setAsPopup($override['asPopup'] ?? false);


        $this->em->persist($partyNews);
        $this->em->flush();

        return $partyNews;
    }
}