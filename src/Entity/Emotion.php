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

    public function setPhoto($photo): self
    {
        if ($photo === null) {
            $this->photo = null;
            return $this;
        }

        // Si c'est un objet UploadedFile de Symfony
        if (is_object($photo) && method_exists($photo, 'getPathname')) {
            $photo = file_get_contents($photo->getPathname());
        }
        
        // Si c'est un chemin de fichier
        if (is_string($photo) && file_exists($photo)) {
            $photo = file_get_contents($photo);
        }
        
        // Si c'est une ressource (flux)
        if (is_resource($photo)) {
            $photo = stream_get_contents($photo);
        }
        
        // Si c'est une chaîne binaire, convertir en base64
        if (is_string($photo) && !empty($photo)) {
            // Vérifier si ce n'est pas déjà du base64
            if (!preg_match('/^[A-Za-z0-9+\/=]+$/', $photo) || strlen($photo) % 4 != 0) {
                $photo = base64_encode($photo);
            }
        }
        
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