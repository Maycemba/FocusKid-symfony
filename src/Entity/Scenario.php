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

    public function setAnimation($animation): self
    {
        if ($animation === null) {
            $this->animation = null;
            return $this;
        }

        // Si c'est un objet UploadedFile de Symfony
        if (is_object($animation) && method_exists($animation, 'getPathname')) {
            $animation = file_get_contents($animation->getPathname());
        }
        
        // Si c'est un chemin de fichier
        if (is_string($animation) && file_exists($animation)) {
            $animation = file_get_contents($animation);
        }
        
        // Si c'est une ressource (flux)
        if (is_resource($animation)) {
            $animation = stream_get_contents($animation);
        }
        
        // Si c'est une chaîne binaire, convertir en base64
        if (is_string($animation) && !empty($animation)) {
            // Vérifier si ce n'est pas déjà du base64
            if (!preg_match('/^[A-Za-z0-9+\/=]+$/', $animation) || strlen($animation) % 4 != 0) {
                $animation = base64_encode($animation);
            }
        }
        
        $this->animation = $animation;
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