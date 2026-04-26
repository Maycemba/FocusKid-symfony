<?php

namespace App\Entity;

use App\Repository\DetectionEmotionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetectionEmotionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class DetectionEmotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Lien vers HumeurJournaliere — SANS toucher à cette entité
    #[ORM\ManyToOne(targetEntity: HumeurJournaliere::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?HumeurJournaliere $humeurJournaliere = null;

    // Émotion choisie par l'enfant (ex: "joie", "tristesse")
    #[ORM\Column(length: 50)]
    private ?string $emotionChoisie = null;

    // Émotion détectée par Face++ (ex: "happiness", "sadness")
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $emotionDetectee = null;

    // Scores bruts retournés par Face++ (JSON)
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $scoresDetection = null;

    // Photo capturée (base64 ou chemin)
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $photoCapture = null;

    // La détection correspond-elle à l'émotion choisie ?
    #[ORM\Column(nullable: true)]
    private ?bool $correspondance = null;

    // Score de confiance de Face++ (0-100)
    #[ORM\Column(nullable: true)]
    private ?float $scoreConfiance = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // --- Getters / Setters ---

    public function getId(): ?int { return $this->id; }

    public function getHumeurJournaliere(): ?HumeurJournaliere
    {
        return $this->humeurJournaliere;
    }

    public function setHumeurJournaliere(?HumeurJournaliere $humeurJournaliere): static
    {
        $this->humeurJournaliere = $humeurJournaliere;
        return $this;
    }

    public function getEmotionChoisie(): ?string { return $this->emotionChoisie; }
    public function setEmotionChoisie(string $emotionChoisie): static
    {
        $this->emotionChoisie = $emotionChoisie;
        return $this;
    }

    public function getEmotionDetectee(): ?string { return $this->emotionDetectee; }
    public function setEmotionDetectee(?string $emotionDetectee): static
    {
        $this->emotionDetectee = $emotionDetectee;
        return $this;
    }

    public function getScoresDetection(): ?array { return $this->scoresDetection; }
    public function setScoresDetection(?array $scoresDetection): static
    {
        $this->scoresDetection = $scoresDetection;
        return $this;
    }

    public function getPhotoCapture(): ?string { return $this->photoCapture; }
    public function setPhotoCapture(?string $photoCapture): static
    {
        $this->photoCapture = $photoCapture;
        return $this;
    }

    public function isCorrespondance(): ?bool { return $this->correspondance; }
    public function setCorrespondance(?bool $correspondance): static
    {
        $this->correspondance = $correspondance;
        return $this;
    }

    public function getScoreConfiance(): ?float { return $this->scoreConfiance; }
    public function setScoreConfiance(?float $scoreConfiance): static
    {
        $this->scoreConfiance = $scoreConfiance;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
}