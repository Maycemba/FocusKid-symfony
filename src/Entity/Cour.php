<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\CourRepository;

#[ORM\Entity(repositoryClass: CourRepository::class)]
#[ORM\Table(name: 'cours')]
class Cour
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_cours = null;

    public function getId_cours(): ?int
    {
        return $this->id_cours;
    }

    public function setId_cours(int $id_cours): self
    {
        $this->id_cours = $id_cours;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $titre = null;

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
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
    private ?string $formateur = null;

    public function getFormateur(): ?string
    {
        return $this->formateur;
    }

    public function setFormateur(?string $formateur): self
    {
        $this->formateur = $formateur;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $statut = null;

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Exercice::class, mappedBy: 'cour')]
    private Collection $exercices;

    /**
     * @return Collection<int, Exercice>
     */
    public function getExercices(): Collection
    {
        if (!$this->exercices instanceof Collection) {
            $this->exercices = new ArrayCollection();
        }
        return $this->exercices;
    }

    public function addExercice(Exercice $exercice): self
    {
        if (!$this->getExercices()->contains($exercice)) {
            $this->getExercices()->add($exercice);
        }
        return $this;
    }

    public function removeExercice(Exercice $exercice): self
    {
        $this->getExercices()->removeElement($exercice);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Lecon::class, mappedBy: 'cour')]
    private Collection $lecons;

    /**
     * @return Collection<int, Lecon>
     */
    public function getLecons(): Collection
    {
        if (!$this->lecons instanceof Collection) {
            $this->lecons = new ArrayCollection();
        }
        return $this->lecons;
    }

    public function addLecon(Lecon $lecon): self
    {
        if (!$this->getLecons()->contains($lecon)) {
            $this->getLecons()->add($lecon);
        }
        return $this;
    }

    public function removeLecon(Lecon $lecon): self
    {
        $this->getLecons()->removeElement($lecon);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Quiz::class, mappedBy: 'cour')]
    private Collection $quizs;

    public function __construct()
    {
        $this->exercices = new ArrayCollection();
        $this->lecons = new ArrayCollection();
        $this->quizs = new ArrayCollection();
    }

    /**
     * @return Collection<int, Quiz>
     */
    public function getQuizs(): Collection
    {
        if (!$this->quizs instanceof Collection) {
            $this->quizs = new ArrayCollection();
        }
        return $this->quizs;
    }

    public function addQuiz(Quiz $quiz): self
    {
        if (!$this->getQuizs()->contains($quiz)) {
            $this->getQuizs()->add($quiz);
        }
        return $this;
    }

    public function removeQuiz(Quiz $quiz): self
    {
        $this->getQuizs()->removeElement($quiz);
        return $this;
    }

    public function getIdCours(): ?int
    {
        return $this->id_cours;
    }

}
