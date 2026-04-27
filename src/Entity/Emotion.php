<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'emotion')]
class Emotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $nom = null;

    // ⬇️ AJOUTE CETTE PROPRIÉTÉ ⬇️
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $photo = null;

    #[ORM\OneToMany(mappedBy: 'emotion', targetEntity: Scenario::class)]
    private Collection $scenarios;

    #[ORM\OneToMany(mappedBy: 'emotion', targetEntity: HumeurJournaliere::class)]
    private Collection $humeurJournalieres;

    public function __construct()
    {
        $this->scenarios = new ArrayCollection();
        $this->humeurJournalieres = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    // ⬇️ AJOUTE CES GETTER/SETTER ⬇️
    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;
        return $this;
    }
}