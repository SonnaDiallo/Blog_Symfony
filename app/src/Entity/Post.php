<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Table(name: 'post')]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    // CHANGEMENT : category est maintenant une relation ManyToOne
    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Category $category = null;

    #[ORM\Column(nullable: true)]
    private ?int $readingTime = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $price = null;

    #[ORM\Column(nullable: true)]
    private ?int $stock = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'post', targetEntity: PostLike::class, orphanRemoval: true)]
    private Collection $likes;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->likes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    // CHANGEMENT : category retourne maintenant un objet Category
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getReadingTime(): ?int
    {
        return $this->readingTime;
    }

    public function setReadingTime(?int $readingTime): static
    {
        $this->readingTime = $readingTime;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): static
    {
        $this->stock = $stock;
        return $this;
    }

    public function getFormattedDate(): string
    {
        return $this->createdAt->format('d F Y');
    }

    public function getReadTime(): string
    {
        if ($this->readingTime) {
            return $this->readingTime . ' min';
        }
        
        // Calcul automatique basé sur le contenu (250 mots/min)
        $wordCount = str_word_count(strip_tags($this->content));
        $minutes = max(1, ceil($wordCount / 250));
        
        return $minutes . ' min';
    }

    public function getAuthor(): string
    {
        if ($this->user) {
            return $this->user->getFirstName() . ' ' . $this->user->getLastName();
        }
        return 'Auteur inconnu';
    }

    public function getExcerpt(int $length = 150): string
    {
        $text = strip_tags($this->content);
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . '...';
    }

    public function isForSale(): bool
    {
        return $this->price !== null && (float)$this->price > 0;
    }

    public function isInStock(): bool
    {
        return $this->stock === null || $this->stock > 0;
    }

    public function getFormattedPrice(): string
    {
        if (!$this->price) {
            return 'Gratuit';
        }
        return number_format((float)$this->price, 2, ',', ' ') . ' €';
    }

    // Vérifier si l'utilisateur peut modifier cet article
    public function canEdit(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        
        // L'auteur ou un admin peut modifier
        return $this->user === $user || in_array('ROLE_ADMIN', $user->getRoles());
    }

    /**
     * @return Collection<int, PostLike>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(PostLike $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setPost($this);
        }
        return $this;
    }

    public function removeLike(PostLike $like): static
    {
        if ($this->likes->removeElement($like)) {
            if ($like->getPost() === $this) {
                $like->setPost(null);
            }
        }
        return $this;
    }

    public function getLikesCount(): int
    {
        return $this->likes->count();
    }

    public function isLikedByUser(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        foreach ($this->likes as $like) {
            if ($like->getUser() === $user) {
                return true;
            }
        }
        return false;
    }
}