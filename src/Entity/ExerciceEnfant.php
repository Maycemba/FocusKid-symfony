<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ExerciceEnfantRepository;

#[ORM\Entity(repositoryClass: ExerciceEnfantRepository::class)]
#[ORM\Table(name: 'exercice_enfant')]
class ExerciceEnfant
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

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $date_attribution = null;

    public function getDate_attribution(): ?\DateTimeInterface
    {
        return $this->date_attribution;
    }

    public function setDate_attribution(\DateTimeInterface $date_attribution): self
    {
        $this->date_attribution = $date_attribution;
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

    public function getDateAttribution(): ?\DateTime
    {
        return $this->date_attribution;
    }

    public function setDateAttribution(\DateTime $date_attribution): static
    {
        $this->date_attribution = $date_attribution;

        return $this;
    }

}
