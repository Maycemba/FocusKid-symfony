<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\CorrectionQuizRepository;

#[ORM\Entity(repositoryClass: CorrectionQuizRepository::class)]
#[ORM\Table(name: 'correction_quiz')]
class CorrectionQuiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_correction = null;

    public function getId_correction(): ?int
    {
        return $this->id_correction;
    }

    public function setId_correction(int $id_correction): self
    {
        $this->id_correction = $id_correction;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: QuestionQuiz::class, inversedBy: 'correctionQuizs')]
    #[ORM\JoinColumn(name: 'id_question', referencedColumnName: 'id_question')]
    private ?QuestionQuiz $questionQuiz = null;

    public function getQuestionQuiz(): ?QuestionQuiz
    {
        return $this->questionQuiz;
    }

    public function setQuestionQuiz(?QuestionQuiz $questionQuiz): self
    {
        $this->questionQuiz = $questionQuiz;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $contenu = null;

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(?string $contenu): self
    {
        $this->contenu = $contenu;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $est_correcte = null;

    public function isEst_correcte(): ?bool
    {
        return $this->est_correcte;
    }

    public function setEst_correcte(?bool $est_correcte): self
    {
        $this->est_correcte = $est_correcte;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: ReponseUserQuiz::class, mappedBy: 'correctionQuiz')]
    private Collection $reponseUserQuizs;

    public function __construct()
    {
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

    public function getIdCorrection(): ?int
    {
        return $this->id_correction;
    }

    public function isEstCorrecte(): ?bool
    {
        return $this->est_correcte;
    }

    public function setEstCorrecte(?bool $est_correcte): static
    {
        $this->est_correcte = $est_correcte;

        return $this;
    }

}
