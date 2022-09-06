<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use App\Controller\ResetPasswordAction;

/**
 * @ApiResource(
 *  itemOperations={
 * "GET" = {
 *  "access_control"="is_granted('IS_AUTHENTICATED_FULLY')",
 *  "normalization_context"={"groups" = {"get"}}
 * },
 * "PUT" = {
 *  "access_control"="is_granted('IS_AUTHENTICATED_FULLY') and object == user",
 *  "denormalization_context"={"groups" = {"put"}},
 *  "normalization_context"={"groups" = {"get"}}
 * },
 * "password-reset" = {
 *      "access_control"="is_granted('IS_AUTHENTICATED_FULLY') and object == user",
 *      "method" = "PUT",
 *      "path" = "/users/{id}/reset-password",
 *      "controller" = ResetPasswordAction::class,
 *      "denormalization_context"={"groups" = {"password-reset"}}
 * },
 * "DELETE" = {
 *  "access_control" = "is_granted('ROLE_SUPERADMIN')"
 * }},
 * collectionOperations={
 *  "GET" = {"normalization-context"={"groups"={"get"}}}, 
 *  "POST" = {
 *      "denormalization_context"={"groups" = {"post"}},
 *      "normalization_context"={"groups" = {"get"}}
 *  },
 * },
 * normalizationContext={"groups" = {"get"}}
 * )
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity("username", message="This username already exists!")
 */
class User implements UserInterface
{
    const ROLE_COMMENTATOR = "ROLE_COMMENTATOR";
    const ROLE_WRITER = "ROLE_WRITER";
    const ROLE_EDITOR = "ROLE_EDITOR";
    const ROLE_ADMIN = "ROLE_ADMIN";
    const ROLE_SUPERADMIN = "ROLE_SUPERADMIN";

    const DEFAULT_ROLES = [self::ROLE_COMMENTATOR];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"get"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="This field is mandatory!", groups = {"post", "put"})
     * @Assert\Length(min=6, max=10, minMessage="At least {{ limit }}", maxMessage="Too long!")
     * @Groups({"get", "post", "get-comment-with-author", "get-post-with-author"})
     */
    private $username;

    /**
     * @Assert\NotBlank(groups = {"post"})
     * @ORM\Column(type="string", length=255)
     * @Groups({"post"})
     */
    private $password;

    /**
     * @Assert\NotBlank(groups = {"post"})
     * @Assert\Expression(
     *  "this.getPassword() === this.getRetypedPassword()"
     * , message = "The two passwords do not match",
     * groups = {"post"}
     * )
     * @Groups({"post"})
     */
    private $retypedPassword;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"get", "put", "post", "get-comment-with-author"})
     * @Assert\NotBlank(groups = {"post", "put"})
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="author")
     * @ORM\JoinColumn(nullable=false)
     * @ApiSubresource
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="author")
     * @ORM\JoinColumn(nullable=false)
     */
    private $comments;
    
    /**
     * @ORM\Column(type = "simple_array", length = 200, nullable = true)
     */
    private $roles;
    
    /**
     * @Assert\NotBlank()
     * @Groups({"password-reset"})
     */
    private $newPassword;

    /**
     * @Assert\NotBlank()
     * @Assert\Expression(
     *  "this.getNewPassword() === this.getNewRetypedPassword()"
     * , message = "The two passwords do not match")
     * @Groups({"password-reset"})
     */
    private $newRetypedPassword;

    /**
     * @Assert\NotBlank()
     * @Groups({"password-reset"})
     * @UserPassword()
     */
    private $oldPassword;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->roles = self::DEFAULT_ROLES;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getRetypedPassword(): ?string
    {
        return $this->retypedPassword;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function setRetypedPassword(string $retypedPassword): self
    {
        $this->retypedPassword = $retypedPassword;

        return $this;
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

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setAuthor($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getAuthor() === $this) {
                $post->setAuthor(null);
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

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * Returns the roles granted to the user.
     *
     *     public function getRoles()
     *     {
     *         return ['ROLE_USER'];
     *     }
     *
     * Alternatively, the roles might be stored in a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return array<Role|string> The user roles
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {

    }

    public function getNewRetypedPassword(): ?string
    {
        return $this->newRetypedPassword;
    }

    public function setNewRetypedPassword(string $newRetypedPassword): self
    {
        $this->newRetypedPassword = $newRetypedPassword;

        return $this;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword(string $newPassword): self
    {
        $this->newPassword = $newPassword;

        return $this;
    }

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldPassword(string $oldPassword): self
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

}
