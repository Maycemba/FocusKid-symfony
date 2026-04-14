<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ExerciceRepository;

#[ORM\Entity(repositoryClass: ExerciceRepository::class)]
#[ORM\Table(name: 'exercices')]
class Exercice
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

    // ============ JOINTURE AVEC UTILISATEUR ============
    
   #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'exercices')]
#[ORM\JoinColumn(name: 'cree_par', referencedColumnName: 'id', nullable: true)]
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

    // ============ CHAMPS AVEC VALIDATION ============

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

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $type = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $consigne = null;

    public function getConsigne(): ?string
    {
        return $this->consigne;
    }

    public function setConsigne(?string $consigne): self
    {
        $this->consigne = $consigne;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $difficulte = null;

    public function getDifficulte(): ?int
    {
        return $this->difficulte;
    }

    public function setDifficulte(?int $difficulte): self
    {
        $this->difficulte = $difficulte;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $duree = null;

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): self
    {
        $this->duree = $duree;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
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
    private ?bool $pour_tous_enfants = null;

    public function isPour_tous_enfants(): ?bool
    {
        return $this->pour_tous_enfants;
    }

    public function setPour_tous_enfants(?bool $pour_tous_enfants): self
    {
        $this->pour_tous_enfants = $pour_tous_enfants;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $actif = null;

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(?bool $actif): self
    {
        $this->actif = $actif;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $cree_par = null;

    public function getCree_par(): ?int
    {
        return $this->cree_par;
    }

    public function setCree_par(?int $cree_par): self
    {
        $this->cree_par = $cree_par;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $date_creation = null;

    public function getDate_creation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDate_creation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $complete = null;

    public function isComplete(): ?bool
    {
        return $this->complete;
    }

    public function setComplete(?bool $complete): self
    {
        $this->complete = $complete;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $archive = null;

    public function isArchive(): ?bool
    {
        return $this->archive;
    }

    public function setArchive(?bool $archive): self
    {
        $this->archive = $archive;
        return $this;
    }

    public function isPourTousEnfants(): ?bool
    {
        return $this->pour_tous_enfants;
    }

    public function setPourTousEnfants(?bool $pour_tous_enfants): static
    {
        $this->pour_tous_enfants = $pour_tous_enfants;

        return $this;
    }

    public function getCreePar(): ?int
    {
        return $this->cree_par;
    }

    public function setCreePar(?int $cree_par): static
    {
        $this->cree_par = $cree_par;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTime $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }

}
