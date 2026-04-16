<?php
// src/Entity/CarnetEducatif.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\CarnetEducatifRepository;

#[ORM\Entity(repositoryClass: CarnetEducatifRepository::class)]
#[ORM\Table(name: 'carnet_educatif')]
#[ORM\HasLifecycleCallbacks]
class CarnetEducatif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'carnetEducatifs')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'UserID', nullable: false)]
    #[Assert\NotNull(message: 'L\'utilisateur est obligatoire.')]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(type: 'date', nullable: false)]
    #[Assert\NotNull(message: 'La date d\'étude est obligatoire.')]
    #[Assert\LessThanOrEqual('today', message: 'La date ne peut pas être dans le futur.')]
    private ?\DateTimeInterface $date_etude = null;

    #[ORM\Column(type: 'time', nullable: false)]
    #[Assert\NotNull(message: 'L\'heure de début est obligatoire.')]
    private ?\DateTimeInterface $heure_debut = null;
    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTimeInterface $heure_debut = null;  // CHANGÉ: ?string -> ?\DateTimeInterface

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTimeInterface $heure_fin = null;    // CHANGÉ: ?string -> ?\DateTimeInterface

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $duree_totale = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $lieu = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $matiere = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $type_activite = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $niveau_difficulte = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $niveau_concentration = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $niveau_agitation = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $nombre_interruptions = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $temps_avant_perte_concentration = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $travaille_seul = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $demande_aide = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $niveau_autonomie = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $travail_termine = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $difficultes = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $points_positifs = null;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'carnetEducatif')]
    private Collection $commentaires;

    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
        $this->created_at = new \DateTime();
    }

    // ============ GETTERS ET SETTERS ============

    public function getDateEtude(): ?\DateTimeInterface
    {
        return $this->date_etude;
    }

    public function setDateEtude(?\DateTimeInterface $date_etude): self
    {
        $this->date_etude = $date_etude;
        return $this;
    }

    #[ORM\Column(type: 'time', nullable: false)]
    #[Assert\NotNull(message: 'L\'heure de fin est obligatoire.')]
    #[Assert\GreaterThan(propertyPath: 'heure_debut', message: 'L\'heure de fin doit être postérieure à l\'heure de début.')]
    private ?\DateTimeInterface $heure_fin = null;
    public function getHeureDebut(): ?\DateTimeInterface
    {
        return $this->heure_debut;
    }

    public function setHeureDebut(?\DateTimeInterface $heure_debut): self
    {
        $this->heure_debut = $heure_debut;
        return $this;
    }

    public function getHeureFin(): ?\DateTimeInterface
    {
        return $this->heure_fin;
    }

    public function setHeureFin(?\DateTimeInterface $heure_fin): self
    {
        $this->heure_fin = $heure_fin;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $duree_totale = null;  // Calculé automatiquement

    #[ORM\Column(type: 'string', length: 100, nullable: false)]
    #[Assert\NotNull(message: 'Le lieu est obligatoire.')]
    #[Assert\Length(max: 100, maxMessage: 'Le lieu ne doit pas dépasser 100 caractères.')]
    private ?string $lieu = null;
    public function getDureeTotale(): ?int
    {
        return $this->duree_totale;
    }

    public function setDureeTotale(?int $duree_totale): self
    {
        $this->duree_totale = $duree_totale;
        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }
    #[ORM\Column(type: 'string', length: 50, nullable: false)]
    #[Assert\NotNull(message: 'La matière est obligatoire.')]
    #[Assert\Length(max: 50)]
    private ?string $matiere = null;

    public function getMatiere(): ?string
    {
        return $this->matiere;
    }

    public function setMatiere(?string $matiere): self
    {
        $this->matiere = $matiere;
        return $this;
    }

    public function getTypeActivite(): ?string
    {
        return $this->type_activite;
    }

    public function setTypeActivite(?string $type_activite): self
    {
        $this->type_activite = $type_activite;
        return $this;
    }

    public function getNiveauDifficulte(): ?string
    {
        return $this->niveau_difficulte;
    }

    public function setNiveauDifficulte(?string $niveau_difficulte): self
    {
        $this->niveau_difficulte = $niveau_difficulte;
        return $this;
    }

    public function getNiveauConcentration(): ?int
    {
        return $this->niveau_concentration;
    }

    public function setNiveauConcentration(?int $niveau_concentration): self
    {
        $this->niveau_concentration = $niveau_concentration;
        return $this;
    }

    public function getNiveauAgitation(): ?int
    {
        return $this->niveau_agitation;
    }

    public function setNiveauAgitation(?int $niveau_agitation): self
    {
        $this->niveau_agitation = $niveau_agitation;
        return $this;
    }

    public function getNombreInterruptions(): ?int
    {
        return $this->nombre_interruptions;
    }

    public function setNombreInterruptions(?int $nombre_interruptions): self
    {
        $this->nombre_interruptions = $nombre_interruptions;
        return $this;
    }
    #[ORM\Column(type: 'text', nullable: false)]
    #[Assert\NotNull(message: 'Le type d\'activité est obligatoire.')]
    private ?string $type_activite = null;

    #[ORM\Column(type: 'string', length: 20, nullable: false)]
    #[Assert\NotNull(message: 'Le niveau de difficulté est obligatoire.')]
    #[Assert\Choice(choices: ['Facile', 'Moyen', 'Difficile'], message: 'Choisissez Facile, Moyen ou Difficile.')]
    private ?string $niveau_difficulte = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\NotNull(message: 'Le niveau de concentration est obligatoire (1-5).')]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'La concentration doit être entre 1 et 5.')]
    private ?int $niveau_concentration = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\NotNull(message: 'Le niveau d\'agitation est obligatoire (1-5).')]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'L\'agitation doit être entre 1 et 5.')]
    private ?int $niveau_agitation = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\NotNull(message: 'Le nombre d\'interruptions est obligatoire.')]
    #[Assert\PositiveOrZero(message: 'Le nombre d\'interruptions ne peut pas être négatif.')]
    private ?int $nombre_interruptions = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\PositiveOrZero(message: 'Le temps avant perte de concentration doit être positif ou nul.')]
    private ?int $temps_avant_perte_concentration = null;

    #[ORM\Column(type: 'boolean', nullable: false)]
    #[Assert\NotNull(message: 'Précisez si l\'élève a travaillé seul.')]
    private ?bool $travaille_seul = null;

    #[ORM\Column(type: 'boolean', nullable: false)]
    #[Assert\NotNull(message: 'Précisez si l\'élève a demandé de l\'aide.')]
    private ?bool $demande_aide = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\NotNull(message: 'Le niveau d\'autonomie est obligatoire (1-5).')]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'L\'autonomie doit être entre 1 et 5.')]
    private ?int $niveau_autonomie = null;

    #[ORM\Column(type: 'boolean', nullable: false)]
    #[Assert\NotNull(message: 'Précisez si le travail est terminé.')]
    private ?bool $travail_termine = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $difficultes = null;
    public function getTempsAvantPerteConcentration(): ?int
    {
        return $this->temps_avant_perte_concentration;
    }

    public function setTempsAvantPerteConcentration(?int $temps_avant_perte_concentration): self
    {
        $this->temps_avant_perte_concentration = $temps_avant_perte_concentration;
        return $this;
    }

    public function isTravailleSeul(): ?bool
    {
        return $this->travaille_seul;
    }

    public function setTravailleSeul(?bool $travaille_seul): self
    {
        $this->travaille_seul = $travaille_seul;
        return $this;
    }

    public function isDemandeAide(): ?bool
    {
        return $this->demande_aide;
    }

    public function setDemandeAide(?bool $demande_aide): self
    {
        $this->demande_aide = $demande_aide;
        return $this;
    }

    public function getNiveauAutonomie(): ?int
    {
        return $this->niveau_autonomie;
    }

    public function setNiveauAutonomie(?int $niveau_autonomie): self
    {
        $this->niveau_autonomie = $niveau_autonomie;
        return $this;
    }

    public function isTravailTermine(): ?bool
    {
        return $this->travail_termine;
    }

    public function setTravailTermine(?bool $travail_termine): self
    {
        $this->travail_termine = $travail_termine;
        return $this;
    }

    public function getDifficultes(): ?string
    {
        return $this->difficultes;
    }

    public function setDifficultes(?string $difficultes): self
    {
        $this->difficultes = $difficultes;
        return $this;
    }

    public function getPointsPositifs(): ?string
    {
        return $this->points_positifs;
    }

    public function setPointsPositifs(?string $points_positifs): self
    {
        $this->points_positifs = $points_positifs;
        return $this;
    }
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $points_positifs = null;

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }
    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'carnetEducatif', cascade: ['remove'])]
    private Collection $commentaires;

    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
    }

    // --- Getters et setters (tous nécessaires) ---

    public function getId(): ?int { return $this->id; }

    public function getUtilisateur(): ?Utilisateur { return $this->utilisateur; }
    public function setUtilisateur(?Utilisateur $utilisateur): self
    { $this->utilisateur = $utilisateur; return $this; }

    public function getDateEtude(): ?\DateTimeInterface { return $this->date_etude; }
    public function setDateEtude(?\DateTimeInterface $date_etude): self
    { $this->date_etude = $date_etude; return $this; }

    public function getHeureDebut(): ?\DateTimeInterface { return $this->heure_debut; }
    public function setHeureDebut(?\DateTimeInterface $heure_debut): self
    { $this->heure_debut = $heure_debut; return $this; }

    public function getHeureFin(): ?\DateTimeInterface { return $this->heure_fin; }
    public function setHeureFin(?\DateTimeInterface $heure_fin): self
    { $this->heure_fin = $heure_fin; return $this; }

    public function getDureeTotale(): ?int { return $this->duree_totale; }
    // Pas de setter public car calculé automatiquement (on garde un setter privé ou on le retire)
    // Mais pour Doctrine, on laisse un setter (utilisé par le callback)
    public function setDureeTotale(?int $duree_totale): self
    { $this->duree_totale = $duree_totale; return $this; }

    public function getLieu(): ?string { return $this->lieu; }
    public function setLieu(?string $lieu): self
    { $this->lieu = $lieu; return $this; }

    public function getMatiere(): ?string { return $this->matiere; }
    public function setMatiere(?string $matiere): self
    { $this->matiere = $matiere; return $this; }

    public function getTypeActivite(): ?string { return $this->type_activite; }
    public function setTypeActivite(?string $type_activite): self
    { $this->type_activite = $type_activite; return $this; }

    public function getNiveauDifficulte(): ?string { return $this->niveau_difficulte; }
    public function setNiveauDifficulte(?string $niveau_difficulte): self
    { $this->niveau_difficulte = $niveau_difficulte; return $this; }

    public function getNiveauConcentration(): ?int { return $this->niveau_concentration; }
    public function setNiveauConcentration(?int $niveau_concentration): self
    { $this->niveau_concentration = $niveau_concentration; return $this; }

    public function getNiveauAgitation(): ?int { return $this->niveau_agitation; }
    public function setNiveauAgitation(?int $niveau_agitation): self
    { $this->niveau_agitation = $niveau_agitation; return $this; }

    public function getNombreInterruptions(): ?int { return $this->nombre_interruptions; }
    public function setNombreInterruptions(?int $nombre_interruptions): self
    { $this->nombre_interruptions = $nombre_interruptions; return $this; }

    public function getTempsAvantPerteConcentration(): ?int { return $this->temps_avant_perte_concentration; }
    public function setTempsAvantPerteConcentration(?int $temps_avant_perte_concentration): self
    { $this->temps_avant_perte_concentration = $temps_avant_perte_concentration; return $this; }

    public function isTravailleSeul(): ?bool { return $this->travaille_seul; }
    public function setTravailleSeul(?bool $travaille_seul): self
    { $this->travaille_seul = $travaille_seul; return $this; }

    public function isDemandeAide(): ?bool { return $this->demande_aide; }
    public function setDemandeAide(?bool $demande_aide): self
    { $this->demande_aide = $demande_aide; return $this; }

    public function getNiveauAutonomie(): ?int { return $this->niveau_autonomie; }
    public function setNiveauAutonomie(?int $niveau_autonomie): self
    { $this->niveau_autonomie = $niveau_autonomie; return $this; }

    public function isTravailTermine(): ?bool { return $this->travail_termine; }
    public function setTravailTermine(?bool $travail_termine): self
    { $this->travail_termine = $travail_termine; return $this; }

    public function getDifficultes(): ?string { return $this->difficultes; }
    public function setDifficultes(?string $difficultes): self
    { $this->difficultes = $difficultes; return $this; }

    public function getPointsPositifs(): ?string { return $this->points_positifs; }
    public function setPointsPositifs(?string $points_positifs): self
    { $this->points_positifs = $points_positifs; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->created_at; }
    public function setCreatedAt(\DateTimeInterface $created_at): self
    { $this->created_at = $created_at; return $this; }

    // --- Callbacks ---

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function calculateDureeTotale(): void
    {
        if ($this->heure_debut && $this->heure_fin) {
            $diff = $this->heure_debut->diff($this->heure_fin);
            // $diff->invert = 1 signifie que $this->heure_fin < $this->heure_debut
            // Dans ce cas, on force une durée nulle ou on laisse la validation l'empêcher
            if ($diff->invert === 1) {
                // Si la validation n'a pas déjà bloqué, on met 0 pour éviter une valeur négative
                $this->duree_totale = 0;
            } else {
                $minutes = $diff->h * 60 + $diff->i;
                $this->duree_totale = $minutes;
            }
        }
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->created_at = new \DateTime();
    }

    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): self
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setCarnetEducatif($this);
        }
        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): self
    {
        if ($this->commentaires->removeElement($commentaire)) {
            if ($commentaire->getCarnetEducatif() === $this) {
                $commentaire->setCarnetEducatif(null);
            }
        }
        return $this;
    }
}
        $this->getCommentaires()->removeElement($commentaire);
        return $this;
    }
}