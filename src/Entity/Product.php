<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 4)]
    private ?string $prix = null;

    #[ORM\Column]
    private ?int $stock = null;

    #[ORM\Column(length: 255)]
    private ?string $sku = null;

    #[ORM\Column(length: 255)]
    private ?string $dimensions = null;

    #[ORM\Column]
    private array $materiaux = [];

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'possede')]
    private ?Cooperative $cooperative = null;

    /**
     * @var Collection<int, Artisan>
     */
    #[ORM\ManyToMany(targetEntity: Artisan::class, mappedBy: 'creer')]
    private Collection $artisans;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?ProductMedia $a = null;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'product')]
    private Collection $recoit;

    public function __construct()
    {
        $this->artisans = new ArrayCollection();
        $this->recoit = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(string $sku): static
    {
        $this->sku = $sku;

        return $this;
    }

    public function getDimensions(): ?string
    {
        return $this->dimensions;
    }

    public function setDimensions(string $dimensions): static
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    public function getMateriaux(): array
    {
        return $this->materiaux;
    }

    public function setMateriaux(array $materiaux): static
    {
        $this->materiaux = $materiaux;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
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
     * @return Collection<int, Artisan>
     */
    public function getArtisans(): Collection
    {
        return $this->artisans;
    }

    public function addArtisan(Artisan $artisan): static
    {
        if (!$this->artisans->contains($artisan)) {
            $this->artisans->add($artisan);
            $artisan->addCreer($this);
        }

        return $this;
    }

    public function removeArtisan(Artisan $artisan): static
    {
        if ($this->artisans->removeElement($artisan)) {
            $artisan->removeCreer($this);
        }

        return $this;
    }

    public function getA(): ?ProductMedia
    {
        return $this->a;
    }

    public function setA(?ProductMedia $a): static
    {
        $this->a = $a;

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getRecoit(): Collection
    {
        return $this->recoit;
    }

    public function addRecoit(Review $recoit): static
    {
        if (!$this->recoit->contains($recoit)) {
            $this->recoit->add($recoit);
            $recoit->setProduct($this);
        }

        return $this;
    }

    public function removeRecoit(Review $recoit): static
    {
        if ($this->recoit->removeElement($recoit)) {
            // set the owning side to null (unless already changed)
            if ($recoit->getProduct() === $this) {
                $recoit->setProduct(null);
            }
        }

        return $this;
    }
}
