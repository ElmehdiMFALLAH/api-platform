<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Annotation\ApiResouce;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Post
 *
 * @ORM\Table(name="post")
 * @ORM\Entity
 * @ApiResource(
 *  itemOperations={
 *  "GET"={
 * "access_control"="is_granted('IS_AUTHENTICATED_FULLY')",
 * "normalization_context"={"groups" = {"get-post-with-author"}}
 * },
 *  "DELETE",
 * "PUT" = {"access_control"="is_granted('ROLE_EDITOR') or (is_granted('ROLE_WRITER') and object.getAuthor() == user)"}
 * },
 * subresourceOperations = {
 *  "api_users_posts_get_subresource" = {"normalization_context" = {"groups" = {"get-user-with-posts"}}}
 * },
 *  collectionOperations={"GET","POST"={"access_control"="is_granted('ROLE_WRITER')"}}
 * )
 */
class Post
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Groups({"get-post-with-author"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=155, nullable=false)
     * @Assert\NotBlank(message="This field is mandatory!")
     * @Assert\Length(min=5, max=10, minMessage="At least {{ limit }}", maxMessage="Too long!")
     * @Groups({"get-post-with-author", "get-user-with-posts"})
     */
    private $title;

    /**
     * @var string
     * @ORM\Column(name="slug", type="string", length=140, nullable=false)
     * @Assert\NotBlank(message="This field is mandatory!")
     * @Assert\Length(min=5, max=20, minMessage="At least {{ limit }}", maxMessage="Too long!")
     * @Groups({"get-post-with-author"})
     */
    private $slug;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     * @ApiSubresource
     * @Groups({"get-post-with-author"})
     */
    private $author;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="published", type="datetime", nullable=false)
     * @Assert\DateTime()
     * @Groups({"get-post-with-author", "get-user-with-posts"})
     */
    private $published;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", length=65535, nullable=false)
     * @Groups({"get-post-with-author"})
     */
    private $content;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="post")
     * @ORM\JoinColumn(nullable=false)
     * @ApiSubresource
     * @Groups({"get-post-with-author"})
     */
    private $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPublished(): ?\DateTimeInterface
    {
        return $this->published;
    }

    public function setPublished(\DateTimeInterface $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

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

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

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
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }


}
