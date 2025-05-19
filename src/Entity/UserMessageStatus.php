<?php

namespace App\Entity;

use App\Repository\UserMessageStatusRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserMessageStatusRepository::class)]
class UserMessageStatus
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    // @phpstan-ignore-next-line
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'userMessageStatuses')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'userMessageStatuses')]
    private ?PartyNews $partyNews = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $readAt = null;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getPartyNews(): ?PartyNews
    {
        return $this->partyNews;
    }

    public function setPartyNews(?PartyNews $partyNews): static
    {
        $this->partyNews = $partyNews;

        return $this;
    }

    public function isRead(): bool
    {
        return $this->readAt !== null;
    }

    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    public function setReadAt(\DateTimeImmutable $readAt): static
    {
        $this->readAt = $readAt;

        return $this;
    }
}
