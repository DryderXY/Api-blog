<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['list_posts','get_post','list_post_cat'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull(message: "Le titre est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 60,
        minMessage: 'Titre doit faire au moins {{ limit }} caractères',
        maxMessage: 'Titre doit faire au plus {{ limit }} caractères')]
    #[Groups(['list_posts','get_post','list_post_cat'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotNull(message: "Le contenu doit être obligatoire")]
    #[Assert\Length(
        min: 20,
        minMessage: 'Contenu doit faire au moins {{ limit }} caractères')]
    #[Groups(['list_posts','get_post','list_post_cat'])]

    private ?string $content = null;

    #[ORM\Column]
    #[Groups('get_post','list_post_cat')]
    private ?\DateTime $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[Groups(['list_posts','get_post'])]
    private ?Category $category = null;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }
}
