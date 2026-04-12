<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ReponseExerciceRepository;

#[ORM\Entity(repositoryClass: ReponseExerciceRepository::class)]
#[ORM\Table(name: 'reponse_exercice')]
class ReponseExercice
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

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $exercice_id = null;

    public function getExercice_id(): ?int
    {
        return $this->exercice_id;
    }

    public function setExercice_id(int $exercice_id): self
    {
        $this->exercice_id = $exercice_id;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $enfant_id = null;

    public function getEnfant_id(): ?int
    {
        return $this->enfant_id;
    }

    public function setEnfant_id(int $enfant_id): self
    {
        $this->enfant_id = $enfant_id;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $score = null;

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(?int $score): self
    {
        $this->score = $score;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $temps_passe = null;

    public function getTemps_passe(): ?int
    {
        return $this->temps_passe;
    }

    public function setTemps_passe(?int $temps_passe): self
    {
        $this->temps_passe = $temps_passe;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $reponses = null;

    public function getReponses(): ?string
    {
        return $this->reponses;
    }

    public function setReponses(?string $reponses): self
    {
        $this->reponses = $reponses;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $reussite = null;

    public function isReussite(): ?bool
    {
        return $this->reussite;
    }

    public function setReussite(?bool $reussite): self
    {
        $this->reussite = $reussite;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $date_passage = null;

    public function getDate_passage(): ?\DateTimeInterface
    {
        return $this->date_passage;
    }

    public function setDate_passage(\DateTimeInterface $date_passage): self
    {
        $this->date_passage = $date_passage;
        return $this;
    }

    public function getExerciceId(): ?int
    {
        return $this->exercice_id;
    }

    public function setExerciceId(int $exercice_id): static
    {
        $this->exercice_id = $exercice_id;

        return $this;
    }

    public function getEnfantId(): ?int
    {
        return $this->enfant_id;
    }

    public function setEnfantId(int $enfant_id): static
    {
        $this->enfant_id = $enfant_id;

        return $this;
    }

    public function getTempsPasse(): ?int
    {
        return $this->temps_passe;
    }

    public function setTempsPasse(?int $temps_passe): static
    {
        $this->temps_passe = $temps_passe;

        return $this;
    }

    public function getDatePassage(): ?\DateTime
    {
        return $this->date_passage;
    }

    public function setDatePassage(\DateTime $date_passage): static
    {
        $this->date_passage = $date_passage;

        return $this;
    }

}
