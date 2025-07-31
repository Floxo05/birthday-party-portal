<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface, \Stringable, EquatableInterface
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    // @phpstan-ignore-next-line
    private ?Uuid $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    private ?string $username = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $phoneNumber = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, PartyMember>
     */
    #[ORM\OneToMany(targetEntity: PartyMember::class, mappedBy: 'user')]
    private Collection $partyMembers;

    /**
     * @var Collection<int, Media>
     */
    #[ORM\OneToMany(targetEntity: Media::class, mappedBy: 'user')]
    private Collection $media;

    /**
     * @var Collection<int, UserMessageStatus>
     */
    #[ORM\OneToMany(targetEntity: UserMessageStatus::class, mappedBy: 'user')]
    private Collection $userMessageStatuses;

    public function __construct()
    {
        $this->partyMembers = new ArrayCollection();
        $this->media = new ArrayCollection();
        $this->userMessageStatuses = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return non-empty-array<int<0, max>, string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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
            $partyMember->setUser($this);
        }

        return $this;
    }

    public function removePartyMember(PartyMember $partyMember): static
    {
        if ($this->partyMembers->removeElement($partyMember)) {
            // set the owning side to null (unless already changed)
            if ($partyMember->getUser() === $this) {
                $partyMember->setUser(null);
            }
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->name . ' (' . $this->username . ')';
    }

    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self)
        {
            return false;
        }

        return $this->id && $user->getId() && $this->id->equals($user->getId());
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
            $medium->setUploader($this);
        }

        return $this;
    }

    public function removeMedium(Media $medium): static
    {
        if ($this->media->removeElement($medium))
        {
            // set the owning side to null (unless already changed)
            if ($medium->getUploader() === $this)
            {
                $medium->setUploader(null);
            }
        }

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
            $userMessageStatus->setUser($this);
        }

        return $this;
    }

    public function removeUserMessageStatus(UserMessageStatus $userMessageStatus): static
    {
        if ($this->userMessageStatuses->removeElement($userMessageStatus)) {
            // set the owning side to null (unless already changed)
            if ($userMessageStatus->getUser() === $this) {
                $userMessageStatus->setUser(null);
            }
        }

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }
}
