<?php

namespace App\Entity;

use App\Repository\PartyNewsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PartyNewsRepository::class)]
class PartyNews
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    // @phpstan-ignore-next-line
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'partyNews')]
    private ?Party $party = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'partyNews')]
    private ?Media $media = null;

    #[ORM\Column(nullable: true)]
    private ?bool $asPopup = null;

    /**
     * @var Collection<int, UserMessageStatus>
     */
    #[ORM\OneToMany(targetEntity: UserMessageStatus::class, mappedBy: 'partyNews')]
    private Collection $userMessageStatuses;

    public function __construct()
    {
        $this->userMessageStatuses = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getParty(): ?Party
    {
        return $this->party;
    }

    public function setParty(?Party $party): static
    {
        $this->party = $party;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): static
    {
        $this->media = $media;

        return $this;
    }

    public function getAsPopup(): bool
    {
        return $this->asPopup ?? false;
    }

    public function setAsPopup(?bool $asPopup): static
    {
        $this->asPopup = $asPopup;

        return $this;
    }

    /**
     * @return Collection<int, UserMessageStatus>
     */
    public function getUserMessageStatuses(): Collection
    {
        return $this->userMessageStatuses;
    }

    public function addUserMessageStatus(UserMessageStatus $userMessageStatus): static
    {
        if (!$this->userMessageStatuses->contains($userMessageStatus)) {
            $this->userMessageStatuses->add($userMessageStatus);
            $userMessageStatus->setPartyNews($this);
        }

        return $this;
    }

    public function removeUserMessageStatus(UserMessageStatus $userMessageStatus): static
    {
        if ($this->userMessageStatuses->removeElement($userMessageStatus)) {
            // set the owning side to null (unless already changed)
            if ($userMessageStatus->getPartyNews() === $this) {
                $userMessageStatus->setPartyNews(null);
            }
        }

        return $this;
    }
}
