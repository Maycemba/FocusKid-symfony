<?php
// src/Entity/Commentaire.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\CommentaireRepository;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
#[ORM\Table(name: 'commentaire')]
class Commentaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CarnetEducatif::class, inversedBy: 'commentaires')]
    #[ORM\JoinColumn(name: 'carnet_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    private ?CarnetEducatif $carnetEducatif = null;

    #[ORM\Column(type: 'datetime', nullable: false)]
    #[Assert\NotNull(message: 'La date et l\'heure du commentaire sont obligatoires.')]
    private ?\DateTimeInterface $date_commentaire = null;

    #[ORM\Column(type: 'text', nullable: false)]
    #[Assert\NotBlank(message: 'Veuillez saisir un commentaire.')]
    #[Assert\Length(min: 3, max: 2000, minMessage: 'Minimum 3 caractères.')]
    private ?string $texte_commentaire = null;

    #[ORM\Column(type: 'string', length: 50, nullable: false)]
    #[Assert\NotBlank(message: 'Veuillez choisir un type.')]
    #[Assert\Choice(choices: ['Observation', 'Problème', 'Suggestion', 'Amélioration'], message: 'Type invalide.')]
    private ?string $type_commentaire = null;

    // Getters / setters
    public function getId(): ?int { return $this->id; }

    public function getCarnetEducatif(): ?CarnetEducatif { return $this->carnetEducatif; }
    public function setCarnetEducatif(?CarnetEducatif $carnetEducatif): self
    { $this->carnetEducatif = $carnetEducatif; return $this; }

    public function getDateCommentaire(): ?\DateTimeInterface { return $this->date_commentaire; }
    public function setDateCommentaire(?\DateTimeInterface $date_commentaire): self
    { $this->date_commentaire = $date_commentaire; return $this; }

    public function getTexteCommentaire(): ?string { return $this->texte_commentaire; }
    public function setTexteCommentaire(?string $texte_commentaire): self
    { $this->texte_commentaire = $texte_commentaire; return $this; }

    public function getTypeCommentaire(): ?string { return $this->type_commentaire; }
    public function setTypeCommentaire(?string $type_commentaire): self
    { $this->type_commentaire = $type_commentaire; return $this; }
}