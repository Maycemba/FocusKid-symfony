<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\ScenarioRepository;

#[ORM\Entity(repositoryClass: ScenarioRepository::class)]
#[ORM\Table(name: 'scenario')]
class Scenario
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $animation = null;

    private mixed $uploadedAnimation = null;

    #[ORM\ManyToOne(targetEntity: Emotion::class, inversedBy: 'scenarios')]
    #[ORM\JoinColumn(name: 'emotionId', referencedColumnName: 'id')]
    private ?Emotion $emotion = null;

    public function __construct()
    {
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getAnimation(): ?string
    {
        return $this->animation;
    }

    public function getUploadedAnimation(): mixed
    {
        return $this->uploadedAnimation;
    }

    public function setAnimation($animation): self
{
    if ($animation === null) {
        $this->animation = null;
        $this->uploadedAnimation = null;
        return $this;
    }

    // Si c'est déjà un data URI complet, on stocke directement
    if (is_string($animation) && str_starts_with($animation, 'data:')) {
        $this->animation = $animation;
        $this->uploadedAnimation = null;
        return $this;
    }

    // Si c'est un UploadedFile
    if (is_object($animation) && method_exists($animation, 'getPathname')) {
        $this->uploadedAnimation = $animation; // on laisse le controller gérer
        return $this;
    }

    if (is_string($animation)) {
        $this->animation = $animation;
        $this->uploadedAnimation = null;
    }
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
}