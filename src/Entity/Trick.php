<?php

namespace App\Entity;

use DateTimeImmutable;
use App\Entity\ImageLink;
use App\Entity\VideoLink;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Slug;
use App\Repository\TrickRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TrickRepository::class)]
#[UniqueEntity(fields: ['name'], message: 'Ce nom est déjà utilisé !')]
class Trick
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $trickgroup = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $modifiedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $discussionChannel = null;

    #[ORM\Column]
    private ?bool $deleted = null;

    #[ORM\OneToMany(mappedBy: 'trick', targetEntity: ImageLink::class,cascade: ['persist'], orphanRemoval: true)]
    private Collection $imageLinks;

    #[ORM\Column(length: 100, unique: true)]
    #[Slug(fields: ['name'])]
    private ?string $slug = null;

    #[ORM\OneToMany(mappedBy: 'trick', targetEntity: Comment::class)]
    private Collection $comments;

    #[ORM\ManyToOne(inversedBy: 'tricks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $creator = null;

    #[ORM\OneToMany(mappedBy: 'trick', targetEntity: VideoLink::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $videoLinks;

    public function __construct()
    {
        $this->imageLinks = new ArrayCollection();
        $this->videoLinks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getTrickgroup(): ?string
    {
        return $this->trickgroup;
    }

    public function setTrickgroup(string $trickgroup): self
    {
        $this->trickgroup = $trickgroup;

        return $this;
    }
    
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getmodifiedAt(): ?\DateTimeImmutable
    {
        return $this->modifiedAt;
    }

    public function setmodifiedAt(\DateTimeImmutable $modifiedAt): self
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    public function getDiscussionChannel(): ?string
    {
        return $this->discussionChannel;
    }

    public function setDiscussionChannel(string $discussionChannel): self
    {
        $this->discussionChannel = $discussionChannel;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return Collection<int, ImageLink>
     */
    public function getImageLinks(): Collection
    {
        return $this->imageLinks;
    }

    public function addImageLink(ImageLink $imageLink): self
    {
        if (!$this->imageLinks->contains($imageLink)) {
            $this->imageLinks->add($imageLink);
            $imageLink->setTrick($this);
        }

        return $this;
    }

    public function removeImageLink(ImageLink $imageLink): self
    {
        if ($this->imageLinks->removeElement($imageLink)) {
            // set the owning side to null (unless already changed)
            if ($imageLink->getTrick() === $this) {
                $imageLink->setTrick(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setTrick($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getTrick() === $this) {
                $comment->setTrick(null);
            }
        }

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * @return Collection<int, VideoLink>
     */
    public function getVideoLinks(): Collection
    {
        return $this->videoLinks;
    }

    public function addVideoLink(VideoLink $videoLink): self
    {
        if (!$this->videoLinks->contains($videoLink)) {
            $this->videoLinks->add($videoLink);
            $videoLink->setTrick($this);
        }

        return $this;
    }

    public function removeVideoLink(VideoLink $videoLink): self
    {
        if ($this->videoLinks->removeElement($videoLink)) {
            // set the owning side to null (unless already changed)
            if ($videoLink->getTrick() === $this) {
                $videoLink->setTrick(null);
            }
        }

        return $this;
    }
}
