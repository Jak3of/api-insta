<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
#[ORM\Table(name: '`images`')]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['image:read', 'post:read', 'image:write', 'user:read', 'user:write'])]
    private ?int $id = null;
    
    #[ORM\ManyToOne(inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['image:read','post:read', 'image:write',  'user:write'])]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    #[Groups(['image:read','post:read', 'image:write', 'user:read', 'user:write'])]
    private ?string $image_path = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['image:read','post:read', 'image:write', 'user:read', 'user:write'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['image:read','post:read', 'image:write', 'user:read', 'user:write'])]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    #[Groups(['image:read','post:read', 'image:write', 'user:read', 'user:write'])]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'image')]
    private Collection $likes;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'image')]
    #[Groups(['post:read'])]
    private Collection $comments;


    private bool $likedByCurrentUser;

    public function __construct()
    {
        $this->likes = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getImagePath(): ?string
    {
        return $this->image_path;
    }

    public function setImagePath(string $image_path): static
    {
        $this->image_path = $image_path;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection<int, Like>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setImage($this);
        }

        return $this;
    }

    public function removeLike(Like $like): static
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getImage() === $this) {
                $like->setImage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setImage($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getImage() === $this) {
                $comment->setImage(null);
            }
        }

        return $this;
    }

    #[Groups(['user:read','image:read','post:read'])]
    public function getLikesCount() : int
    {

        return count($this->likes);
    }

    #[Groups(['user:read','image:read'])]
    public function getCommentsCount() : int
    {

        return count($this->comments);
    }

    public function setlikedByCurrentUser(
        string $email
        
        ): static
    {
        
           
       

        // 1000ms - 1200ms
        $this->likedByCurrentUser = false;
        $this->likes->filter(function ($like) use ($email) {

            if ($like->getUser()->getEmail() === $email) {
                $this->likedByCurrentUser = true;
            }
        });

        // 3000ms - 3400ms
        // $this->likedByCurrentUser = false;
        // foreach ($this->likes as $like) {
        //     if ($like->getUser()->getEmail() === $email) {
        //         $this->likedByCurrentUser = true;
        //     }
        // }

        return $this;
    }

    

    
    #[Groups(['user:read','image:read','post:read'])]
    public function getlikedByCurrentUser( ) : bool
    {
        
        return $this->likedByCurrentUser;
    }


}
