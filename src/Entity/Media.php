<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
class Media implements \Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    // @phpstan-ignore-next-line
    private ?Uuid $id = null;

    #[ORM\ManyToOne(inversedBy: 'media')]
    private ?Party $party = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'media')]
    private ?User $uploader = null;

    #[ORM\Column(length: 255)]
    private ?string $originalFilename = null;

    #[ORM\Column(length: 255)]
    private ?string $mimeType = null;

    #[ORM\Column]
    private ?int $size = null;

    #[ORM\Column(length: 255)]
    private ?string $storagePath = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $uploadedAt = null;

    /**
     * @var Collection<int, PartyNews>
     */
    #[ORM\OneToMany(targetEntity: PartyNews::class, mappedBy: 'media')]
    private Collection $partyNews;

    public function __construct()
    {
        $this->partyNews = new ArrayCollection();
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

    public function getUploader(): ?User
    {
        return $this->uploader;
    }

    public function setUploader(?User $uploader): static
    {
        $this->uploader = $uploader;

        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): static
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getStoragePath(): ?string
    {
        return $this->storagePath;
    }

    public function setStoragePath(string $storagePath): static
    {
        $this->storagePath = $storagePath;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeImmutable $uploadedAt): static
    {
        $this->uploadedAt = $uploadedAt;

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
            $partyNews->setMedia($this);
        }

        return $this;
    }

    public function removePartyNews(PartyNews $partyNews): static
    {
        if ($this->partyNews->removeElement($partyNews))
        {
            // set the owning side to null (unless already changed)
            if ($partyNews->getMedia() === $this)
            {
                $partyNews->setMedia(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getOriginalFilename() ?? '';
    }
}
