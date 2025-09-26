<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PartyGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PartyGroupRepository::class)]
class PartyGroup
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    // @phpstan-ignore-next-line
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'groups')]
    private ?Party $party = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private ?bool $isFoodVotingGroup = null;

    /**
     * @var Collection<int, PartyGroupAssignment>
     */
    #[ORM\OneToMany(targetEntity: PartyGroupAssignment::class, mappedBy: 'group', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $assignments;

    public function __construct()
    {
        $this->assignments = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getIsFoodVotingGroup(): ?bool
    {
        return $this->isFoodVotingGroup;
    }

    public function setIsFoodVotingGroup(bool $isFoodVotingGroup): static
    {
        $this->isFoodVotingGroup = $isFoodVotingGroup;

        return $this;
    }

    /**
     * @return Collection<int, PartyGroupAssignment>
     */
    public function getAssignments(): Collection
    {
        return $this->assignments;
    }

    public function addAssignment(PartyGroupAssignment $assignment): static
    {
        if (!$this->assignments->contains($assignment)) {
            $this->assignments->add($assignment);
            $assignment->setGroup($this);
        }

        return $this;
    }

    public function removeAssignment(PartyGroupAssignment $assignment): static
    {
        if ($this->assignments->removeElement($assignment)) {
            // set the owning side to null (unless already changed)
            if ($assignment->getGroup() === $this) {
                $assignment->setGroup(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}


