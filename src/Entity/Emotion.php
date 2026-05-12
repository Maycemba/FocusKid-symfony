<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\EmotionRepository;


#[ORM\Entity(repositoryClass: EmotionRepository::class)]
#[ORM\Table(name: 'emotion')]
class Emotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $photo = null;

    #[ORM\OneToMany(targetEntity: HumeurJournaliere::class, mappedBy: 'emotion')]
    private Collection $humeurJournalieres;

    #[ORM\OneToMany(targetEntity: Scenario::class, mappedBy: 'emotion')]
    private Collection $scenarios;

    public function __construct()
    {
        $this->humeurJournalieres = new ArrayCollection();
        $this->scenarios = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

   public function setPhoto(?string $photo): self
{
    // Si on reçoit une chaîne vide ou null
    if ($photo === null || $photo === '') {
        $this->photo = null;
        return $this;
    }

    // Si la donnée ressemble déjà à un data URI complet, on l'utilise telle quelle (optionnel)
    if (str_starts_with($photo, 'data:image/')) {
        $this->photo = $photo;
        return $this;
    }

    // Sinon, on suppose que c'est du base64 pur (pas de préfixe)
    $this->photo = $photo;
    return $this;
}
    /**
     * Retourne la photo sous forme de base64 (prete pour l'affichage)
     */
    public function getPhotoBase64(): ?string
    {
        if (!$this->photo) {
            return null;
        }

        if (preg_match('/^[A-Za-z0-9+\/=]+$/', $this->photo)) {
            return 'data:image/jpeg;base64,' . $this->photo;
        }

        return null;
    }



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