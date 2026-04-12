<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\QuestionQuizRepository;

#[ORM\Entity(repositoryClass: QuestionQuizRepository::class)]
#[ORM\Table(name: 'question_quiz')]
class QuestionQuiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_question = null;

    public function getId_question(): ?int
    {
        return $this->id_question;
    }

    public function setId_question(int $id_question): self
    {
        $this->id_question = $id_question;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Quiz::class, inversedBy: 'questionQuizs')]
    #[ORM\JoinColumn(name: 'id_quiz', referencedColumnName: 'id_quiz')]
    private ?Quiz $quiz = null;

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }

    public function setQuiz(?Quiz $quiz): self
    {
        $this->quiz = $quiz;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $enonce = null;

    public function getEnonce(): ?string
    {
        return $this->enonce;
    }

    public function setEnonce(?string $enonce): self
    {
        $this->enonce = $enonce;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $type_question = null;

    public function getType_question(): ?string
    {
        return $this->type_question;
    }

    public function setType_question(?string $type_question): self
    {
        $this->type_question = $type_question;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $points = null;

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(?int $points): self
    {
        $this->points = $points;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $temps_limite = null;

    public function getTemps_limite(): ?int
    {
        return $this->temps_limite;
    }

    public function setTemps_limite(?int $temps_limite): self
    {
        $this->temps_limite = $temps_limite;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $ordre = null;

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(?int $ordre): self
    {
        $this->ordre = $ordre;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: CorrectionQuiz::class, mappedBy: 'questionQuiz')]
    private Collection $correctionQuizs;

    /**
     * @return Collection<int, CorrectionQuiz>
     */
    public function getCorrectionQuizs(): Collection
    {
        if (!$this->correctionQuizs instanceof Collection) {
            $this->correctionQuizs = new ArrayCollection();
        }
        return $this->correctionQuizs;
    }

    public function addCorrectionQuiz(CorrectionQuiz $correctionQuiz): self
    {
        if (!$this->getCorrectionQuizs()->contains($correctionQuiz)) {
            $this->getCorrectionQuizs()->add($correctionQuiz);
        }
        return $this;
    }

    public function removeCorrectionQuiz(CorrectionQuiz $correctionQuiz): self
    {
        $this->getCorrectionQuizs()->removeElement($correctionQuiz);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: ReponseUserQuiz::class, mappedBy: 'questionQuiz')]
    private Collection $reponseUserQuizs;

    public function __construct()
    {
        $this->correctionQuizs = new ArrayCollection();
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

    public function getIdQuestion(): ?int
    {
        return $this->id_question;
    }

    public function getTypeQuestion(): ?string
    {
        return $this->type_question;
    }

    public function setTypeQuestion(?string $type_question): static
    {
        $this->type_question = $type_question;

        return $this;
    }

    public function getTempsLimite(): ?int
    {
        return $this->temps_limite;
    }

    public function setTempsLimite(?int $temps_limite): static
    {
        $this->temps_limite = $temps_limite;

        return $this;
    }

}
