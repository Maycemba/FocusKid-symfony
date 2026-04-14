<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

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

    // ============ TITRE ============
    
    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "Le titre est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le titre ne doit pas dépasser {{ limit }} caractères"
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZÀ-ÿ0-9\s\-_.,!?]+$/",
        message: "Le titre contient des caractères non autorisés"
    )]
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

    // ============ NIVEAU ============
    
    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: "Le niveau est obligatoire")]
    #[Assert\Choice(
        choices: ["1", "2", "3", "4", "5", "CP", "CE1", "CE2", "CM1", "CM2", "6ème", "5ème", "4ème", "3ème"],
        message: "Choisissez un niveau valide"
    )]
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

    // ============ DESCRIPTION ============
    
    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    #[Assert\Length(
        min: 10,
        max: 500,
        minMessage: "La description doit contenir au moins {{ limit }} caractères",
        maxMessage: "La description ne doit pas dépasser {{ limit }} caractères"
    )]
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

    // ============ FORMATEUR ============
    
    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: "Le formateur est obligatoire")]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: "Le nom du formateur doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom du formateur ne doit pas dépasser {{ limit }} caractères"
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZÀ-ÿ\s\-]+$/",
        message: "Le nom du formateur ne doit contenir que des lettres et des espaces mpolkijhngbfvdlkjfds"
    )]
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
    #[Assert\NotBlank(message: "Le statut est obligatoire")]
    #[Assert\Choice(
        choices: ["brouillon", "publié", "archive", "Brouillon", "Publié", "Archive"],
        message: "Choisissez un statut valide (brouillon, publié, archive)"
    )]
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

    #[ORM\OneToMany(targetEntity: Lecon::class, mappedBy: 'cour')]
    private Collection $lecons;

    #[ORM\OneToMany(targetEntity: Quiz::class, mappedBy: 'cour')]
    private Collection $quizs;

    public function __construct()
    {
        $this->exercices = new ArrayCollection();
        $this->lecons = new ArrayCollection();
        $this->quizs = new ArrayCollection();
    }

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

    // ============ ALIAS ============
    
    public function getIdCours(): ?int
    {
        return $this->id_cours;
    }
}