<?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

use Symfony\Component\Validator\Constraints as Assert;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`users`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'user:write', 'user:identifier', 'image:read', 'post:read','comment:read','viewuser:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\Choice(choices: ['user', 'admin'], message: 'The role must be user or admin.')]
    private ?string $role = null;

    #[ORM\Column(length: 100)]
    #[Groups(['user:read', 'user:write', 'user:identifier', 'image:read', 'post:read','comment:read','viewuser:read'])]
    #[Assert\NotBlank(message: 'The name cannot be empty.')]
    #[Assert\Length(min: 3, max: 100, minMessage: 'The name must be at least 3 characters long', maxMessage: 'The name must be at most 100 characters long')]
    private ?string $name = null;

    #[ORM\Column(length: 200)]
    #[Groups(['user:read', 'user:write', 'user:identifier', 'image:read', 'post:read','comment:read','viewuser:read'])]
    #[Assert\NotBlank(message: 'The surname cannot be empty.')]
    #[Assert\Length(min: 3, max: 200, minMessage: 'The surname must be at least 3 characters long', maxMessage: 'The surname must be at most 200 characters long')]
    private ?string $surname = null;

    #[ORM\Column(length: 100)]
    #[Groups(['user:read', 'user:write', 'user:identifier', 'image:read', 'post:read','comment:read','viewuser:read'])]
    #[Assert\NotBlank(message: 'The nick cannot be empty.')]
    #[Assert\Length(min: 3, max: 100, minMessage: 'The nick must be at least 3 characters long', maxMessage: 'The nick must be at most 100 characters long')]
    private ?string $nick = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write', 'user:identifier', 'image:read', 'post:read','comment:read','viewuser:read'])]
    #[Assert\Email(message: 'The email {{ value }} is not a valid email.')]
    #[Assert\NotBlank(message: 'The email cannot be empty.')]
    private ?string $email = null;


    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 255, minMessage: 'The password must be at least 8 characters long', maxMessage: 'The password must be at most 255 characters long')]
    #[Assert\Regex(pattern: "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d-]{8,}$/", message: 'The password must contain at least one uppercase letter, one lowercase letter and one number')]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write', 'user:identifier', 'image:read', 'post:read','comment:read','viewuser:read'])]
    #[Assert\NotBlank(message: 'The image cannot be empty.')]
    private ?string $image = null;

    #[ORM\Column]
    #[Groups(['user:read', 'user:write', 'user:identifier','viewuser:read'])]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    #[Groups(['user:read', 'user:write', 'user:identifier'])]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $remember_token = null;

    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'user')]

    private Collection $images;

    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'user')]

    private Collection $likes;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'user')]

    private Collection $comments;

    #[ORM\OneToMany(targetEntity: Follow::class, mappedBy: 'user')]
    private Collection $followers;

    #[ORM\OneToMany(targetEntity: Follow::class, mappedBy: 'following')]
    private Collection $following;

    public function __construct()
    {
        $this->likes = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->following = new ArrayCollection();
    }




    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): ?string
    {
        $role = $this->role;
        $role = 'ROLE_' . strtoupper($role);
        return $role;
    }

    public function setRole(string $role): static
    {
        $role = str_replace('ROLE_', '', $role);
        $role = strtolower($role);
        $this->role = $role;
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

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): static
    {
        $this->surname = $surname;

        return $this;
    }

    public function getNick(): ?string
    {
        return $this->nick;
    }

    public function setNick(string $nick): static
    {
        $this->nick = $nick;

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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

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

    public function getRememberToken(): ?string
    {
        return $this->remember_token;
    }

    public function setRememberToken(string $remember_token): static
    {
        $this->remember_token = $remember_token;

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setUser($this);
        }

        return $this;
    }

    public function removeImage(Image $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getUser() === $this) {
                $image->setUser(null);
            }
        }

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
            $like->setUser($this);
        }

        return $this;
    }

    public function removeLike(Like $like): static
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getUser() === $this) {
                $like->setUser(null);
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
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    #[Groups(['user:read', 'user:write','viewuser:read'])]
    public function getImagesCount(): int
    {

        return count($this->images);
    }

    #[Groups(['user:read', 'user:write','viewuser:read'])]
    public function getLikesCount(): int
    {

        return count($this->likes);
    }

    #[Groups(['user:read', 'user:write','viewuser:read'])]
    public function getCommentsCount(): int
    {

        return count($this->comments);
    }

    #[Groups(['user:read', 'user:identifier','viewuser:read'])]
    public function getRoles(): array
    {
        $role = [$this->getRole()];
        return array_unique($role);

    }

    public function eraseCredentials(): void
    {

    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @return Collection<int, Follow>
     */
    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    // public function addFollowers(Follow $follow): static
    // {
    //     if (!$this->followers->contains($follow)) {
    //         $this->followers->add($follow);
    //         $follow->setUser($this);
    //     }

    //     return $this;
    // }

    // public function removeFollowers(Follow $follow): static
    // {
    //     if ($this->followers->removeElement($follow)) {
    //         // set the owning side to null (unless already changed)
    //         if ($follow->getUser() === $this) {
    //             $follow->setUser(null);
    //         }
    //     }

    //     return $this;
    // }

    /**
     * @return Collection|Follow[]
     */
    public function getFollowing(): Collection
    {
        return $this->following;
    }

    public function addFollowing(Follow $follow): static
    {
        if (!$this->following->contains($follow)) {
            $this->following->add($follow);
            $follow->setFollowing($this);
        }

        return $this;
    }

    // public function addFollowing(Follow $follow): static
    // {
    //     // Verificar si el usuario actual ya sigue al usuario objetivo
    //     $existingFollow = $this->following->filter(function (Follow $existingFollow) use ($follow) {
    //         return $existingFollow->getFollowing() === $follow->getFollowing();
    //     });

    //     if ($existingFollow->isEmpty()) {
    //         $this->following->add($follow);
    //         $follow->setFollowing($this);
    //     }

    //     return $this;
    // }

    // public function addFollowing(Follow $follow): static
    // {
    //     $this->following->add($follow);
    //     $follow->setFollowing($this);

    //     return $this;
    // }


    public function removeFollowing(Follow $follow): static
    {
        $this->following->removeElement($follow);
        
        return $this;
    }

    // public function removeFollowing(Follow $follow): static
    // {
    //     // Verificar si el usuario identificado es el propietario del seguimiento
    //     if ($follow->getFollowing() === $this) {
    //         $this->following->removeElement($follow);
    //         // También puedes establecer el lado inverso a null aquí si es necesario
    //     }

    //     return $this;
    // }



}
