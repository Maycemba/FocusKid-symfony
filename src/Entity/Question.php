<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\QuestionRepository;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
#[ORM\Table(name: 'question')]
class Question
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

    #[ORM\ManyToOne(targetEntity: Jeu::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(name: 'jeu_id', referencedColumnName: 'id')]
    private ?Jeu $jeu = null;

    public function getJeu(): ?Jeu
    {
        return $this->jeu;
    }

    public function setJeu(?Jeu $jeu): self
    {
        $this->jeu = $jeu;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $question_text = null;

    public function getQuestion_text(): ?string
    {
        return $this->question_text;
    }

    public function setQuestion_text(?string $question_text): self
    {
        $this->question_text = $question_text;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $option_a = null;

    public function getOption_a(): ?string
    {
        return $this->option_a;
    }

    public function setOption_a(?string $option_a): self
    {
        $this->option_a = $option_a;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $option_b = null;

    public function getOption_b(): ?string
    {
        return $this->option_b;
    }

    public function setOption_b(?string $option_b): self
    {
        $this->option_b = $option_b;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $option_c = null;

    public function getOption_c(): ?string
    {
        return $this->option_c;
    }

    public function setOption_c(?string $option_c): self
    {
        $this->option_c = $option_c;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $bonne_reponse = null;

    public function getBonne_reponse(): ?string
    {
        return $this->bonne_reponse;
    }

    public function setBonne_reponse(?string $bonne_reponse): self
    {
        $this->bonne_reponse = $bonne_reponse;
        return $this;
    }

    public function getQuestionText(): ?string
    {
        return $this->question_text;
    }

    public function setQuestionText(?string $question_text): static
    {
        $this->question_text = $question_text;

        return $this;
    }

    public function getOptionA(): ?string
    {
        return $this->option_a;
    }

    public function setOptionA(?string $option_a): static
    {
        $this->option_a = $option_a;

        return $this;
    }

    public function getOptionB(): ?string
    {
        return $this->option_b;
    }

    public function setOptionB(?string $option_b): static
    {
        $this->option_b = $option_b;

        return $this;
    }

    public function getOptionC(): ?string
    {
        return $this->option_c;
    }

    public function setOptionC(?string $option_c): static
    {
        $this->option_c = $option_c;

        return $this;
    }

    public function getBonneReponse(): ?string
    {
        return $this->bonne_reponse;
    }

    public function setBonneReponse(?string $bonne_reponse): static
    {
        $this->bonne_reponse = $bonne_reponse;

        return $this;
    }

}
