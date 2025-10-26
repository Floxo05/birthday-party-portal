<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\GameConfigRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: GameConfigRepository::class)]
#[ORM\Table(name: 'game_config')]
#[ORM\UniqueConstraint(name: 'uniq_game_config_slug', columns: ['slug'])]
class GameConfig
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(type: 'string', length: 64)]
    private string $slug;

    #[ORM\Column(name: 'start_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $startAt = null;

    #[ORM\Column(name: 'end_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $endAt = null;

    #[ORM\Column(name: 'closed', type: 'boolean', options: ['default' => false])]
    private bool $closed = false;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(?\DateTimeImmutable $startAt): self
    {
        $this->startAt = $startAt;
        return $this;
    }

    public function getEndAt(): ?\DateTimeImmutable
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTimeImmutable $endAt): self
    {
        $this->endAt = $endAt;
        return $this;
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }

    public function setClosed(bool $closed): self
    {
        $this->closed = $closed;
        return $this;
    }

    public function __toString(): string
    {
        return sprintf('GameConfig %s', $this->slug ?? '');
    }
}
