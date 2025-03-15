<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PartyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PartyRepository::class)]
class Party
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    // @phpstan-ignore-next-line
    private ?Uuid $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\GreaterThan('today')]
    private ?\DateTimeInterface $partyDate = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $title = null;

    /**
     * @var Collection<int, PartyMember>
     */
    #[ORM\OneToMany(targetEntity: PartyMember::class, mappedBy: 'party', cascade: [
        'persist',
        'remove'
    ], orphanRemoval: true)]
    private Collection $partyMembers;

    /**
     * @var Collection<int, Invitation>
     */
    #[ORM\OneToMany(targetEntity: Invitation::class, mappedBy: 'party', cascade: [
        'persist',
        'remove'
    ], orphanRemoval: true)]
    private Collection $invitations;

    public function __construct()
    {
        $this->partyMembers = new ArrayCollection();
        $this->invitations = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getPartyDate(): ?\DateTimeInterface
    {
        return $this->partyDate;
    }

    public function setPartyDate(\DateTimeInterface $partyDate): static
    {
        $this->partyDate = $partyDate;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection<int, PartyMember>
     */
    public function getPartyMembers(): Collection
    {
        return $this->partyMembers;
    }

    public function addPartyMember(PartyMember $partyMember): static
    {
        if (!$this->partyMembers->contains($partyMember)) {
            $this->partyMembers->add($partyMember);
            $partyMember->setParty($this);
        }

        return $this;
    }

    public function removePartyMember(PartyMember $partyMember): static
    {
        if ($this->partyMembers->removeElement($partyMember))
        {
            // set the owning side to null (unless already changed)
            if ($partyMember->getParty() === $this)
            {
                $partyMember->setParty(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Invitation>
     */
    public function getInvitations(): Collection
    {
        return $this->invitations;
    }

    public function addInvitation(Invitation $invitation): static
    {
        if (!$this->invitations->contains($invitation))
        {
            $this->invitations->add($invitation);
            $invitation->setParty($this);
        }

        return $this;
    }

    public function removeInvitation(Invitation $invitation): static
    {
        if ($this->invitations->removeElement($invitation))
        {
            // set the owning side to null (unless already changed)
            if ($invitation->getParty() === $this)
            {
                $invitation->setParty(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }


}
