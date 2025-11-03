<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ResponseStatus;
use App\Repository\PartyMemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;


#[ORM\Entity(repositoryClass: PartyMemberRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['host' => PartyMember::ROLE_HOST, 'guest' => PartyMember::ROLE_GUEST])]
abstract class PartyMember
{
    const string ROLE_HOST = 'Host';
    const string ROLE_GUEST = 'Guest';

    public function __construct()
    {
        $this->purchasedItems = new ArrayCollection();
    }

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    // @phpstan-ignore-next-line
    private ?Uuid $id = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'partyMembers')]
    private ?Party $party = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'partyMembers')]
    private ?User $user = null;

    #[ORM\Column(enumType: ResponseStatus::class, options: ['default' => ResponseStatus::PENDING])]
    private ?ResponseStatus $responseStatus = null;

    #[ORM\Column(nullable: true)]
    private ?int $extraGuests = null;

    #[ORM\Column(name: 'clash_team', type: 'string', length: 20, nullable: true)]
    private ?string $clashTeam = null;

    #[ORM\Column(name: 'clash_points', type: 'integer', options: ['default' => 0])]
    private int $clashPoints = 0;

    #[ORM\Column(name: 'points_spend', type: 'integer', options: ['default' => 0])]
    private int $pointsSpend = 0;

    /**
     * @var Collection<int, PurchasedItem>
     */
    #[ORM\OneToMany(targetEntity: PurchasedItem::class, mappedBy: 'owner', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $purchasedItems;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    abstract public function getRole(): string;

    public function getResponseStatus(): ?ResponseStatus
    {
        return $this->responseStatus;
    }

    public function setResponseStatus(ResponseStatus $responseStatus): static
    {
        $this->responseStatus = $responseStatus;

        return $this;
    }

    public function getExtraGuests(): ?int
    {
        return $this->extraGuests;
    }

    public function setExtraGuests(?int $extraGuests): static
    {
        $this->extraGuests = $extraGuests;

        return $this;
    }

    public function getClashTeam(): ?string
    {
        return $this->clashTeam;
    }

    public function setClashTeam(?string $clashTeam): static
    {
        $this->clashTeam = $clashTeam;
        return $this;
    }

    public function getClashPoints(): int
    {
        return $this->clashPoints;
    }

    public function setClashPoints(int $clashPoints): static
    {
        $this->clashPoints = max(0, $clashPoints);
        // ensure pointsSpend does not exceed clashPoints
        if ($this->pointsSpend > $this->clashPoints) {
            $this->pointsSpend = $this->clashPoints;
        }
        return $this;
    }

    public function getPointsSpend(): int
    {
        return $this->pointsSpend;
    }

    public function setPointsSpend(int $pointsSpend): static
    {
        $pointsSpend = max(0, $pointsSpend);
        // cannot spend more than clash points
        $this->pointsSpend = min($pointsSpend, $this->clashPoints);
        return $this;
    }

    public function getBalance(): int
    {
        return max(0, $this->clashPoints - $this->pointsSpend);
    }

    /**
     * @return Collection<int, PurchasedItem>
     */
    public function getPurchasedItems(): Collection
    {
        return $this->purchasedItems;
    }

    public function addPurchasedItem(PurchasedItem $item): static
    {
        if (!$this->purchasedItems->contains($item))
        {
            $this->purchasedItems->add($item);
            $item->setOwner($this);
        }
        return $this;
    }

    public function removePurchasedItem(PurchasedItem $item): static
    {
        if ($this->purchasedItems->removeElement($item))
        {
            if ($item->getOwner() === $this)
            {
                $item->setOwner(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return sprintf('PartyMember %s for Party %s, Role %s', $this->getUser()?->getName(), $this->getParty()?->getTitle(), $this->getRole());
    }
}
