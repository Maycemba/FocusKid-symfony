<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\SessionsDeCalmeRepository;

#[ORM\Entity(repositoryClass: SessionsDeCalmeRepository::class)]
#[ORM\Table(name: 'sessions_de_calme')]
class SessionsDeCalme
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

   #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'sessionsDeCalmes')]
#[ORM\JoinColumn(name: 'enfant_id', referencedColumnName: 'UserID')]   // ← corrigé
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

   #[Assert\NotBlank(message: "Le type d'activité est obligatoire")]
#[Assert\Choice(
    choices: ["musique", "coloriage", "respiration", "histoire"],
    message: "Choisissez une activité valide"
)]
#[ORM\Column(type: 'string', nullable: true)]
private ?string $type_activite = null;
    public function getType_activite(): ?string
    {
        return $this->type_activite;
    }

    public function setType_activite(?string $type_activite): self
    {
        $this->type_activite = $type_activite;
        return $this;
    }

    #[Assert\NotBlank(message: "Le déclencheur est obligatoire")]
#[Assert\Choice(
    choices: ["enfant", "parent"],
    message: "Choisissez 'enfant' ou 'parent'"
)]
#[ORM\Column(type: 'string', nullable: true)]
private ?string $declencheur = null;
    public function getDeclencheur(): ?string
    {
        return $this->declencheur;
    }

    public function setDeclencheur(?string $declencheur): self
    {
        $this->declencheur = $declencheur;
        return $this;
    }
#[Assert\NotNull(message: "La durée prévue est obligatoire")]
#[Assert\Positive(message: "La durée doit être positive")]
#[ORM\Column(type: 'integer', nullable: true)]
private ?int $duree_prevue = null;
    public function getDuree_prevue(): ?int
    {
        return $this->duree_prevue;
    }

    public function setDuree_prevue(?int $duree_prevue): self
    {
        $this->duree_prevue = $duree_prevue;
        return $this;
    }
#[Assert\PositiveOrZero(message: "La durée réelle doit être positive ou zéro")]
#[ORM\Column(type: 'integer', nullable: true)]
private ?int $duree_reelle = null;
    public function getDuree_reelle(): ?int
    {
        return $this->duree_reelle;
    }

    public function setDuree_reelle(?int $duree_reelle): self
    {
        $this->duree_reelle = $duree_reelle;
        return $this;
    }

   #[Assert\NotNull(message: "La date est obligatoire")]
#[Assert\Type(\DateTimeInterface::class, message: "Date invalide")]
#[ORM\Column(type: 'datetime', nullable: false)]
private ?\DateTimeInterface $horodatage = null;
    public function getHorodatage(): ?\DateTimeInterface
    {
        return $this->horodatage;
    }

    public function setHorodatage(\DateTimeInterface $horodatage): self
    {
        $this->horodatage = $horodatage;
        return $this;
    }
#[Assert\Range(
    min: 1,
    max: 5,
    notInRangeMessage: "Le feedback doit être entre 1 et 5"
)]
#[ORM\Column(type: 'integer', nullable: true)]
private ?int $feedback_enfant = null;
    public function getFeedback_enfant(): ?int
    {
        return $this->feedback_enfant;
    }

    public function setFeedback_enfant(?int $feedback_enfant): self
    {
        $this->feedback_enfant = $feedback_enfant;
        return $this;
    }
#[Assert\Length(
    max: 255,
    maxMessage: "La note ne doit pas dépasser 255 caractères"
)]
#[ORM\Column(type: 'text', nullable: true)]
private ?string $note_parent = null;
    public function getNote_parent(): ?string
    {
        return $this->note_parent;
    }

    public function setNote_parent(?string $note_parent): self
    {
        $this->note_parent = $note_parent;
        return $this;
    }

    public function getTypeActivite(): ?string
    {
        return $this->type_activite;
    }

    public function setTypeActivite(?string $type_activite): static
    {
        $this->type_activite = $type_activite;

        return $this;
    }

    public function getDureePrevue(): ?int
    {
        return $this->duree_prevue;
    }

    public function setDureePrevue(?int $duree_prevue): static
    {
        $this->duree_prevue = $duree_prevue;

        return $this;
    }

    public function getDureeReelle(): ?int
    {
        return $this->duree_reelle;
    }

    public function setDureeReelle(?int $duree_reelle): static
    {
        $this->duree_reelle = $duree_reelle;

        return $this;
    }

    public function getFeedbackEnfant(): ?int
    {
        return $this->feedback_enfant;
    }

    public function setFeedbackEnfant(?int $feedback_enfant): static
    {
        $this->feedback_enfant = $feedback_enfant;

        return $this;
    }

    public function getNoteParent(): ?string
    {
        return $this->note_parent;
    }

    public function setNoteParent(?string $note_parent): static
    {
        $this->note_parent = $note_parent;

        return $this;
    }

}
