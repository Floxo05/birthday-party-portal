<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PurchasedItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PurchasedItemRepository::class)]
class PurchasedItem
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    // @phpstan-ignore-next-line
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'purchasedItems')]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', nullable: false)]
    private ?PartyMember $owner = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne]
    private ?Media $media = null;

    #[ORM\Column(name: 'acquired_at', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $acquiredAt = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getOwner(): ?PartyMember
    {
        return $this->owner;
    }

    public function setOwner(?PartyMember $owner): self
    {
        $this->owner = $owner;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): self
    {
        $this->media = $media;
        return $this;
    }

    public function getAcquiredAt(): ?\DateTimeImmutable
    {
        return $this->acquiredAt;
    }

    public function setAcquiredAt(\DateTimeImmutable $acquiredAt): self
    {
        $this->acquiredAt = $acquiredAt;
        return $this;
    }

    public function __toString(): string
    {
        return $this->getName() ?? '';
    }
}
