<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\HumeurJournaliereRepository;

#[ORM\Entity(repositoryClass: HumeurJournaliereRepository::class)]
#[ORM\Table(name: 'humeur_journaliere')]
class HumeurJournaliere
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /*#[ORM\ManyToOne(targetEntity: Emotion::class, inversedBy: 'humeurJournalieres')]
    #[ORM\JoinColumn(name: 'emotionId', referencedColumnName: 'id')]
    private ?Emotion $emotion = null;*/

    #[ORM\Column(type: 'datetime', nullable: true, name: 'dateHeure')]
    private ?\DateTimeInterface $dateHeure = null;

    public function __construct()
    {
        // Définir la date par défaut à maintenant
        $this->dateHeure = new \DateTime();
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

    public function getEmotion(): ?Emotion
    {
        return $this->emotion;
    }

    public function setEmotion(?Emotion $emotion): self
    {
        $this->emotion = $emotion;
        return $this;
    }

    public function getDateHeure(): ?\DateTimeInterface
    {
        return $this->dateHeure;
    }

    public function setDateHeure(?\DateTimeInterface $dateHeure): self
    {
        $this->dateHeure = $dateHeure;
        return $this;
    }
}