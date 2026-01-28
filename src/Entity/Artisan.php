<?php

namespace App\Entity;

use App\Repository\ArtisanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ArtisanRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé par un autre artisan.')]
#[UniqueEntity(fields: ['telephone'], message: 'Ce numéro de téléphone est déjà utilisé par un autre artisan.')]
class Artisan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $bio = null;

    #[ORM\Column(length: 255)]
    private ?string $telephone = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $photo = null;

    #[ORM\Column]
    private array $competences = [];

    #[ORM\Column]
    private ?bool $verified = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTime $updatedAt = null;

    #[ORM\OneToOne(inversedBy: 'artisan', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\Column(length: 20)]
    private ?string $approvalStatus = 'PENDING';

    #[ORM\ManyToOne(inversedBy: 'artisan')]
    private ?Cooperative $cooperative = null;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class, inversedBy: 'artisans')]
    private Collection $creer;

    public function __construct()
    {
        $this->creer = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getCompetences(): array
    {
        return $this->competences;
    }

    public function setCompetences(array $competences): static
    {
        $this->competences = $competences;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): static
    {
        $this->verified = $verified;

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

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCooperative(): ?Cooperative
    {
        return $this->cooperative;
    }

    public function setCooperative(?Cooperative $cooperative): static
    {
        $this->cooperative = $cooperative;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getCreer(): Collection
    {
        return $this->creer;
    }

    public function addCreer(Product $creer): static
    {
        if (!$this->creer->contains($creer)) {
            $this->creer->add($creer);
        }

        return $this;
    }

    public function removeCreer(Product $creer): static
    {
        $this->creer->removeElement($creer);

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

    public function getApprovalStatus(): ?string
    {
        return $this->approvalStatus;
    }

    public function setApprovalStatus(string $approvalStatus): static
    {
        $this->approvalStatus = $approvalStatus;

        return $this;
    }
}
