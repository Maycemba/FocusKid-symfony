<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ReponseUserExerciceRepository;

#[ORM\Entity(repositoryClass: ReponseUserExerciceRepository::class)]
#[ORM\Table(name: 'reponse_user_exercice')]
class ReponseUserExercice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_rep_user = null;

    public function getId_rep_user(): ?int
    {
        return $this->id_rep_user;
    }

    public function setId_rep_user(int $id_rep_user): self
    {
        $this->id_rep_user = $id_rep_user;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'reponseUserExercices')]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id')]
    private ?Utilisateur $utilisateur = null;

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Exercice::class, inversedBy: 'reponseUserExercices')]
    #[ORM\JoinColumn(name: 'id_exercice', referencedColumnName: 'id_exercice')]
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

    #[ORM\ManyToOne(targetEntity: QuestionExercice::class, inversedBy: 'reponseUserExercices')]
    #[ORM\JoinColumn(name: 'id_question_exercice', referencedColumnName: 'id_question_exercice')]
    private ?QuestionExercice $questionExercice = null;

    public function getQuestionExercice(): ?QuestionExercice
    {
        return $this->questionExercice;
    }

    public function setQuestionExercice(?QuestionExercice $questionExercice): self
    {
        $this->questionExercice = $questionExercice;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $reponse = null;

    public function getReponse(): ?string
    {
        return $this->reponse;
    }

    public function setReponse(?string $reponse): self
    {
        $this->reponse = $reponse;
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

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $temps_reponse = null;

    public function getTemps_reponse(): ?int
    {
        return $this->temps_reponse;
    }

    public function setTemps_reponse(?int $temps_reponse): self
    {
        $this->temps_reponse = $temps_reponse;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date_reponse = null;

    public function getDate_reponse(): ?\DateTimeInterface
    {
        return $this->date_reponse;
    }

    public function setDate_reponse(?\DateTimeInterface $date_reponse): self
    {
        $this->date_reponse = $date_reponse;
        return $this;
    }

    public function getIdRepUser(): ?int
    {
        return $this->id_rep_user;
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

    public function getTempsReponse(): ?int
    {
        return $this->temps_reponse;
    }

    public function setTempsReponse(?int $temps_reponse): static
    {
        $this->temps_reponse = $temps_reponse;

        return $this;
    }

    public function getDateReponse(): ?\DateTime
    {
        return $this->date_reponse;
    }

    public function setDateReponse(?\DateTime $date_reponse): static
    {
        $this->date_reponse = $date_reponse;

        return $this;
    }

}
