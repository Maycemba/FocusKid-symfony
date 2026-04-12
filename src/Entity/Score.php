<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ScoreRepository;

#[ORM\Entity(repositoryClass: ScoreRepository::class)]
#[ORM\Table(name: 'score')]
class Score
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

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'scores')]
    #[ORM\JoinColumn(name: 'utilisateur_id', referencedColumnName: 'id')]
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

    #[ORM\ManyToOne(targetEntity: Jeu::class, inversedBy: 'scores')]
    #[ORM\JoinColumn(name: 'jeu_id', referencedColumnName: 'id')]
    private ?Jeu $jeu = null;

    public function getJeu(): ?Jeu
    {
        return $this->jeu;
    }

    public function setJeu(?Jeu $jeu): self
    {
        $this->jeu = $jeu;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $points = null;

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(?int $points): self
    {
        $this->points = $points;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $date_partie = null;

    public function getDate_partie(): ?\DateTimeInterface
    {
        return $this->date_partie;
    }

    public function setDate_partie(\DateTimeInterface $date_partie): self
    {
        $this->date_partie = $date_partie;
        return $this;
    }

    public function getDatePartie(): ?\DateTime
    {
        return $this->date_partie;
    }

    public function setDatePartie(\DateTime $date_partie): static
    {
        $this->date_partie = $date_partie;

        return $this;
    }

}
