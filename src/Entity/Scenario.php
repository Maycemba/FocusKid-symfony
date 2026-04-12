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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    #[ORM\Column(type: 'blob', nullable: false)]
    private ?string $animation = null;

    public function getAnimation(): ?string
    {
        return $this->animation;
    }

    public function setAnimation(string $animation): self
    {
        $this->animation = $animation;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Emotion::class, inversedBy: 'scenarios')]
    #[ORM\JoinColumn(name: 'emotionId', referencedColumnName: 'id')]
    private ?Emotion $emotion = null;

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
