<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Media;
use App\Entity\Party;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
class MediaFactory
{
    private Generator $faker;

    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
        $this->faker = FakerFactory::create('de_DE');
    }

    public function create(User $uploader, Party $party, array $override = []): Media
    {
        $media = new Media();
        $media->setUploader($uploader);
        $media->setParty($party);
        $media->setOriginalFilename($override['originalFilename'] ?? $this->faker->word() . '.txt');
        $media->setMimeType($override['mimeType'] ?? 'text/plain');
        $media->setSize($override['size'] ?? $this->faker->numberBetween(100, 10000));
        $media->setStoragePath($override['storagePath'] ?? $this->fakeStoragePath($party));
        $media->setUploadedAt(new \DateTimeImmutable());

        $this->em->persist($media);
        $this->em->flush();

        return $media;
    }

    private function fakeStoragePath(Party $party): string
    {
        return sprintf('%s/%s.txt', $party->getId(), $this->faker->uuid());
    }
}