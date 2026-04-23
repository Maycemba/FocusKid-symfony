<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

use App\Repository\JeuRepository;

#[ORM\Entity(repositoryClass: JeuRepository::class)]
#[ORM\Table(name: 'jeu')]
class Jeu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: "Le titre ne peut pas être vide.")]
    #[Assert\Length(max: 255, maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $titre = null;

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $type = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: "Le niveau ne peut pas être vide.")]
    private ?string $niveau = null;

    public function getNiveau(): ?string
    {
        return $this->niveau;
    }

    public function setNiveau(?string $niveau): self
    {
        $this->niveau = $niveau;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Question::class, mappedBy: 'jeu')]
    private Collection $questions;

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        if (!$this->questions instanceof Collection) {
            $this->questions = new ArrayCollection();
        }
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setJeu($this);
        }
        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->removeElement($question)) {
            if ($question->getJeu() === $this) {
                $question->setJeu(null);
            }
        }
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Score::class, mappedBy: 'jeu')]
    private Collection $scores;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->scores = new ArrayCollection();
    }

    /**
     * @return Collection<int, Score>
     */
    public function getScores(): Collection
    {
        if (!$this->scores instanceof Collection) {
            $this->scores = new ArrayCollection();
        }
        return $this->scores;
    }

    public function addScore(Score $score): self
    {
        if (!$this->scores->contains($score)) {
            $this->scores->add($score);
            $score->setJeu($this);
        }
        return $this;
    }

    public function removeScore(Score $score): self
    {
        if ($this->scores->removeElement($score)) {
            if ($score->getJeu() === $this) {
                $score->setJeu(null);
            }
        }
        return $this;
    }

}
