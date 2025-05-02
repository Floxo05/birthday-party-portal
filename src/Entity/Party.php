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
class Party implements \Stringable
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

    /**
     * @var Collection<int, Media>
     */
    #[ORM\OneToMany(targetEntity: Media::class, mappedBy: 'party')]
    private Collection $media;

    /**
     * @var Collection<int, PartyNews>
     */
    #[ORM\OneToMany(targetEntity: PartyNews::class, mappedBy: 'party', orphanRemoval: true)]
    private Collection $partyNews;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Assert\Expression('this.getRsvpDeadline() < this.getPartyDate()')]
    #[Assert\NotBlank]
    private ?\DateTimeImmutable $rsvpDeadline = null;

    public function __construct()
    {
        $this->partyMembers = new ArrayCollection();
        $this->invitations = new ArrayCollection();
        $this->media = new ArrayCollection();
        $this->partyNews = new ArrayCollection();
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

    /**
     * @return Collection<int, Media>
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(Media $medium): static
    {
        if (!$this->media->contains($medium))
        {
            $this->media->add($medium);
            $medium->setParty($this);
        }

        return $this;
    }

    public function removeMedium(Media $medium): static
    {
        if ($this->media->removeElement($medium))
        {
            // set the owning side to null (unless already changed)
            if ($medium->getParty() === $this)
            {
                $medium->setParty(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PartyNews>
     */
    public function getPartyNews(): Collection
    {
        return $this->partyNews;
    }

    public function addPartyNews(PartyNews $partyNews): static
    {
        if (!$this->partyNews->contains($partyNews))
        {
            $this->partyNews->add($partyNews);
            $partyNews->setParty($this);
        }

        return $this;
    }

    public function removePartyNews(PartyNews $partyNews): static
    {
        if ($this->partyNews->removeElement($partyNews))
        {
            // set the owning side to null (unless already changed)
            if ($partyNews->getParty() === $this)
            {
                $partyNews->setParty(null);
            }
        }

        return $this;
    }

    public function getRsvpDeadline(): ?\DateTimeImmutable
    {
        return $this->rsvpDeadline;
    }

    public function setRsvpDeadline(?\DateTimeImmutable $rsvpDeadline): static
    {
        $this->rsvpDeadline = $rsvpDeadline;

        return $this;
    }


}
