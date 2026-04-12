<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\QuizRepository;

#[ORM\Entity(repositoryClass: QuizRepository::class)]
#[ORM\Table(name: 'quiz')]
class Quiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_quiz = null;

    public function getId_quiz(): ?int
    {
        return $this->id_quiz;
    }

    public function setId_quiz(int $id_quiz): self
    {
        $this->id_quiz = $id_quiz;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Cour::class, inversedBy: 'quizs')]
    #[ORM\JoinColumn(name: 'id_cours', referencedColumnName: 'id_cours')]
    private ?Cour $cour = null;

    public function getCour(): ?Cour
    {
        return $this->cour;
    }

    public function setCour(?Cour $cour): self
    {
        $this->cour = $cour;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
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

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $difficulte = null;

    public function getDifficulte(): ?string
    {
        return $this->difficulte;
    }

    public function setDifficulte(?string $difficulte): self
    {
        $this->difficulte = $difficulte;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $points_total = null;

    public function getPoints_total(): ?int
    {
        return $this->points_total;
    }

    public function setPoints_total(?int $points_total): self
    {
        $this->points_total = $points_total;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $duree_totale = null;

    public function getDuree_totale(): ?int
    {
        return $this->duree_totale;
    }

    public function setDuree_totale(?int $duree_totale): self
    {
        $this->duree_totale = $duree_totale;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $est_actif = null;

    public function isEst_actif(): ?bool
    {
        return $this->est_actif;
    }

    public function setEst_actif(?bool $est_actif): self
    {
        $this->est_actif = $est_actif;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: QuestionQuiz::class, mappedBy: 'quiz')]
    private Collection $questionQuizs;

    /**
     * @return Collection<int, QuestionQuiz>
     */
    public function getQuestionQuizs(): Collection
    {
        if (!$this->questionQuizs instanceof Collection) {
            $this->questionQuizs = new ArrayCollection();
        }
        return $this->questionQuizs;
    }

    public function addQuestionQuiz(QuestionQuiz $questionQuiz): self
    {
        if (!$this->getQuestionQuizs()->contains($questionQuiz)) {
            $this->getQuestionQuizs()->add($questionQuiz);
        }
        return $this;
    }

    public function removeQuestionQuiz(QuestionQuiz $questionQuiz): self
    {
        $this->getQuestionQuizs()->removeElement($questionQuiz);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: ReponseUserQuiz::class, mappedBy: 'quiz')]
    private Collection $reponseUserQuizs;

    public function __construct()
    {
        $this->questionQuizs = new ArrayCollection();
        $this->reponseUserQuizs = new ArrayCollection();
    }

    /**
     * @return Collection<int, ReponseUserQuiz>
     */
    public function getReponseUserQuizs(): Collection
    {
        if (!$this->reponseUserQuizs instanceof Collection) {
            $this->reponseUserQuizs = new ArrayCollection();
        }
        return $this->reponseUserQuizs;
    }

    public function addReponseUserQuiz(ReponseUserQuiz $reponseUserQuiz): self
    {
        if (!$this->getReponseUserQuizs()->contains($reponseUserQuiz)) {
            $this->getReponseUserQuizs()->add($reponseUserQuiz);
        }
        return $this;
    }

    public function removeReponseUserQuiz(ReponseUserQuiz $reponseUserQuiz): self
    {
        $this->getReponseUserQuizs()->removeElement($reponseUserQuiz);
        return $this;
    }

    public function getIdQuiz(): ?int
    {
        return $this->id_quiz;
    }

    public function getPointsTotal(): ?int
    {
        return $this->points_total;
    }

    public function setPointsTotal(?int $points_total): static
    {
        $this->points_total = $points_total;

        return $this;
    }

    public function getDureeTotale(): ?int
    {
        return $this->duree_totale;
    }

    public function setDureeTotale(?int $duree_totale): static
    {
        $this->duree_totale = $duree_totale;

        return $this;
    }

    public function isEstActif(): ?bool
    {
        return $this->est_actif;
    }

    public function setEstActif(?bool $est_actif): static
    {
        $this->est_actif = $est_actif;

        return $this;
    }

}
