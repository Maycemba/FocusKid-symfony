<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\EmotionRepository;

#[ORM\Entity(repositoryClass: EmotionRepository::class)]
#[ORM\Table(name: 'emotion')]
class Emotion
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

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $nom = null;

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    #[ORM\Column(type: 'blob', nullable: true)]
    private ?string $photo = null;

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: HumeurJournaliere::class, mappedBy: 'emotion')]
    private Collection $humeurJournalieres;

    /**
     * @return Collection<int, HumeurJournaliere>
     */
    public function getHumeurJournalieres(): Collection
    {
        if (!$this->humeurJournalieres instanceof Collection) {
            $this->humeurJournalieres = new ArrayCollection();
        }
        return $this->humeurJournalieres;
    }

    public function addHumeurJournaliere(HumeurJournaliere $humeurJournaliere): self
    {
        if (!$this->getHumeurJournalieres()->contains($humeurJournaliere)) {
            $this->getHumeurJournalieres()->add($humeurJournaliere);
        }
        return $this;
    }

    public function removeHumeurJournaliere(HumeurJournaliere $humeurJournaliere): self
    {
        $this->getHumeurJournalieres()->removeElement($humeurJournaliere);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Scenario::class, mappedBy: 'emotion')]
    private Collection $scenarios;

    public function __construct()
    {
        $this->humeurJournalieres = new ArrayCollection();
        $this->scenarios = new ArrayCollection();
    }

    /**
     * @return Collection<int, Scenario>
     */
    public function getScenarios(): Collection
    {
        if (!$this->scenarios instanceof Collection) {
            $this->scenarios = new ArrayCollection();
        }
        return $this->scenarios;
    }

    public function addScenario(Scenario $scenario): self
    {
        if (!$this->getScenarios()->contains($scenario)) {
            $this->getScenarios()->add($scenario);
        }
        return $this;
    }

    public function removeScenario(Scenario $scenario): self
    {
        $this->getScenarios()->removeElement($scenario);
        return $this;
    }

}
