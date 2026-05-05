<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\QuestionExerciceRepository;

#[ORM\Entity(repositoryClass: QuestionExerciceRepository::class)]
#[ORM\Table(name: 'question_exercice')]
class QuestionExercice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_question_exercice = null;

    public function getId_question_exercice(): ?int
    {
        return $this->id_question_exercice;
    }

    public function setId_question_exercice(int $id_question_exercice): self
    {
        $this->id_question_exercice = $id_question_exercice;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Exercice::class, inversedBy: 'questionExercices')]
    #[ORM\JoinColumn(name: 'id_exercice', referencedColumnName: 'id')]
    private ?Exercice $exercice = null;

    public function getExercice(): ?Exercice
    {
        return $this->exercice;
    }

    public function setExercice(?Exercice $exercice): self
    {
        $this->exercice = $exercice;
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

    #[ORM\OneToMany(targetEntity: ReponseUserExercice::class, mappedBy: 'questionExercice')]
    private Collection $reponseUserExercices;

    public function __construct()
    {
        $this->reponseUserExercices = new ArrayCollection();
    }

    /**
     * @return Collection<int, ReponseUserExercice>
     */
    public function getReponseUserExercices(): Collection
    {
        if (!$this->reponseUserExercices instanceof Collection) {
            $this->reponseUserExercices = new ArrayCollection();
        }
        return $this->reponseUserExercices;
    }

    public function addReponseUserExercice(ReponseUserExercice $reponseUserExercice): self
    {
        if (!$this->getReponseUserExercices()->contains($reponseUserExercice)) {
            $this->getReponseUserExercices()->add($reponseUserExercice);
        }
        return $this;
    }

    public function removeReponseUserExercice(ReponseUserExercice $reponseUserExercice): self
    {
        $this->getReponseUserExercices()->removeElement($reponseUserExercice);
        return $this;
    }

    public function getIdQuestionExercice(): ?int
    {
        return $this->id_question_exercice;
    }

}