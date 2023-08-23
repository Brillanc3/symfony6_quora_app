<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez saisir un titre')]
    #[Assert\Length(min: 10, minMessage: 'Le titre doit faire au moins 10 caractères', max: 200, maxMessage: 'Le titre doit faire au plus 200 caractères')]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Veuillez saisir un contenu')]
    #[Assert\Length(min: 10, minMessage: 'Le contenu doit faire au moins 10 caractères', max: 500, maxMessage: 'Le contenu doit faire au plus 500 caractères')]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?int $ratting = null;

    #[ORM\Column]
    private ?int $nbResponse = null;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Comment::class, orphanRemoval: true)]
    private Collection $comments;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getRatting(): ?int
    {
        return $this->ratting;
    }

    public function setRatting(int $ratting): static
    {
        $this->ratting = $ratting;

        return $this;
    }

    public function getNbResponse(): ?int
    {
        return $this->nbResponse;
    }

    public function setNbResponse(int $nbResponse): static
    {
        $this->nbResponse = $nbResponse;

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
            $comment->setQuestion($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getQuestion() === $this) {
                $comment->setQuestion(null);
            }
        }

        return $this;
    }
}
