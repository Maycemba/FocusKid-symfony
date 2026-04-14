<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\CarnetEducatifRepository;

#[ORM\Entity(repositoryClass: CarnetEducatifRepository::class)]
#[ORM\Table(name: 'carnet_educatif')]
class CarnetEducatif
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

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'carnetEducatifs')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
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

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $date_etude = null;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        if (!$this->commentaires instanceof Collection) {
            $this->commentaires = new ArrayCollection();
        }
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): self
    {
        if (!$this->getCommentaires()->contains($commentaire)) {
            $this->getCommentaires()->add($commentaire);
        }
        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): self
    {
        $this->getCommentaires()->removeElement($commentaire);
        return $this;
    }
}