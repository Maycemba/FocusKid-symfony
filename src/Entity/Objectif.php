<?php

namespace App\Entity;

use App\Repository\ObjectifRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ObjectifRepository::class)]
#[ORM\Table(name: 'objectifs')]
#[ORM\HasLifecycleCallbacks]
class Objectif
{
    // Constantes pour les statuts
    const STATUT_EN_COURS = 'EN_COURS';
    const STATUT_ATTEINT = 'ATTEINT';
    const STATUT_NON_ATTEINT = 'NON_ATTEINT';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    #[Assert\NotBlank(message: 'La description est obligatoire')]
    #[Assert\Length(min: 3, max: 255, minMessage: 'La description doit faire au moins 3 caractères', maxMessage: 'La description ne peut pas dépasser 255 caractères')]
    private ?string $description = null;

    #[ORM\Column(type: 'date', nullable: false)]
    #[Assert\NotNull(message: 'La date de début est obligatoire')]
    private ?\DateTimeInterface $date_debut = null;

    #[ORM\Column(type: 'date', nullable: false)]
    #[Assert\NotNull(message: 'La date de fin est obligatoire')]
    #[Assert\GreaterThan(propertyPath: 'date_debut', message: 'La date de fin doit être postérieure à la date de début')]
    private ?\DateTimeInterface $date_fin = null;

    #[ORM\Column(type: 'string', length: 20, columnDefinition: "ENUM('EN_COURS', 'ATTEINT', 'NON_ATTEINT') DEFAULT 'EN_COURS'")]
    private ?string $statut = self::STATUT_EN_COURS;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $created_at = null;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->statut = self::STATUT_EN_COURS;
    }

    // ========== GETTERS ==========

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    // ========== SETTERS ==========

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setDateDebut(\DateTimeInterface $date_debut): self
    {
        $this->date_debut = $date_debut;
        return $this;
    }

    public function setDateFin(\DateTimeInterface $date_fin): self
    {
        $this->date_fin = $date_fin;
        return $this;
    }

    public function setStatut(string $statut): self
    {
        if (!in_array($statut, [self::STATUT_EN_COURS, self::STATUT_ATTEINT, self::STATUT_NON_ATTEINT])) {
            throw new \InvalidArgumentException("Statut invalide");
        }
        $this->statut = $statut;
        return $this;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    // ========== MÉTHODES UTILITAIRES ==========

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->created_at === null) {
            $this->created_at = new \DateTime();
        }
    }

    public function isEnCours(): bool
    {
        return $this->statut === self::STATUT_EN_COURS;
    }

    public function isAtteint(): bool
    {
        return $this->statut === self::STATUT_ATTEINT;
    }

    public function isNonAtteint(): bool
    {
        return $this->statut === self::STATUT_NON_ATTEINT;
    }

    public function estTermine(): bool
    {
        return !$this->isEnCours();
    }

    public function estDansPeriode(): bool
    {
        $aujourdhui = new \DateTime();
        return $aujourdhui >= $this->date_debut && $aujourdhui <= $this->date_fin;
    }

    public function getStatutLabel(): string
    {
        return match($this->statut) {
            self::STATUT_EN_COURS => 'En cours',
            self::STATUT_ATTEINT => 'Atteint',
            self::STATUT_NON_ATTEINT => 'Non atteint',
            default => 'Inconnu'
        };
    }

    public function getStatutBadgeClass(): string
    {
        return match($this->statut) {
            self::STATUT_EN_COURS => 'bg-primary',
            self::STATUT_ATTEINT => 'bg-success',
            self::STATUT_NON_ATTEINT => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    public function getDureeTotale(): int
    {
        $interval = $this->date_debut->diff($this->date_fin);
        return $interval->days + 1;
    }

    public function getJoursRestants(): ?int
    {
        if ($this->estTermine()) {
            return null;
        }
        
        $aujourdhui = new \DateTime();
        if ($aujourdhui > $this->date_fin) {
            return 0;
        }
        
        $interval = $aujourdhui->diff($this->date_fin);
        return $interval->days;
    }

    public function getProgression(): float
    {
        $total = $this->getDureeTotale();
        $ecoule = (new \DateTime())->diff($this->date_debut)->days;
        
        if ($ecoule < 0) return 0;
        if ($ecoule >= $total) return 100;
        
        return round(($ecoule / $total) * 100, 2);
    }
}