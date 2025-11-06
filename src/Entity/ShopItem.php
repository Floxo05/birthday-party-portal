<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ShopItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ShopItemRepository::class)]
class ShopItem
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    // @phpstan-ignore-next-line
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'shopItems')]
    private ?Party $party = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $pricePoints = 0;

    #[ORM\Column(options: ['default' => 0])]
    private int $quantity = 0;

    #[ORM\ManyToOne]
    private ?Media $media = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $visible = true;

    #[ORM\Column(options: ['default' => -1])]
    private int $maxPerUser = -1;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getParty(): ?Party
    {
        return $this->party;
    }

    public function setParty(?Party $party): self
    {
        $this->party = $party;
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

    public function getPricePoints(): int
    {
        return $this->pricePoints;
    }

    public function setPricePoints(int $pricePoints): self
    {
        $this->pricePoints = max(0, $pricePoints);
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = max(0, $quantity);
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

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;
        return $this;
    }

    public function getMaxPerUser(): int
    {
        return $this->maxPerUser;
    }

    public function setMaxPerUser(int $maxPerUser): self
    {
        // -1 means unlimited; otherwise clamp to at least 1
        $this->maxPerUser = $maxPerUser === -1 ? -1 : max(1, $maxPerUser);
        return $this;
    }

    public function __toString(): string
    {
        return $this->getName() ?? '';
    }
}
