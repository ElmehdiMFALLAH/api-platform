<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *  itemOperations={
 * "GET" = {
 *  "access_control"="is_granted('IS_AUTHENTICATED_FULLY')",
 *  "normalization_context"={"groups" = {"get"}}
 * },
 * "PUT" = {
 *  "access_control"="is_granted('ROLE_EDITOR') or (is_granted('ROLE_COMMENTATOR') and object.getAuthor() == user)",
 *  "denormalization_context"={"groups" = {"put"}},
 *  "normalization_context"={"groups" = {"get"}}
 * },
 * "DELETE"},
 * collectionOperations={
 *  "GET" = {"normalization-context"={"groups"={"get"}}}, 
 *  "POST" = {
 *      "denormalization-context"={"groups" = {"post"}},
 *      "normalization_context"={"groups" = {"get"}},
 *      "access_control"="is_granted('ROLE_COMMENTATOR')"
 *  },
 * },
 * subresourceOperations = {
 *  "api_posts_comments_get_subresource" = {"normalization_context" = {"groups" = {"get-comment-with-author"}}}
 * },
 * normalizationContext={"groups" = {"get"}}
 * )
 * @ORM\Entity
 */
class Comment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"get", "get-comment-with-author", "get-post-with-author", "put"})
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get-comment-with-author", "get"})
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Post", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"get"})
     */
    private $post;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"get"})
     * @Groups({"get-comment-with-author"})
     */
    private $published;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPublished(): ?\DateTimeInterface
    {
        return $this->published;
    }

    public function setPublished(\DateTimeInterface $published): self
    {
        $this->published = $published;

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

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

        return $this;
    }
}
