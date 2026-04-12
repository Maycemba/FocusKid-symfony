<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\CommentaireRepository;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
#[ORM\Table(name: 'commentaire')]
class Commentaire
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

    #[ORM\ManyToOne(targetEntity: CarnetEducatif::class, inversedBy: 'commentaires')]
    #[ORM\JoinColumn(name: 'carnet_id', referencedColumnName: 'id')]
    private ?CarnetEducatif $carnetEducatif = null;

    public function getCarnetEducatif(): ?CarnetEducatif
    {
        return $this->carnetEducatif;
    }

    public function setCarnetEducatif(?CarnetEducatif $carnetEducatif): self
    {
        $this->carnetEducatif = $carnetEducatif;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $date_commentaire = null;

    public function getDate_commentaire(): ?\DateTimeInterface
    {
        return $this->date_commentaire;
    }

    public function setDate_commentaire(?\DateTimeInterface $date_commentaire): self
    {
        $this->date_commentaire = $date_commentaire;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $texte_commentaire = null;

    public function getTexte_commentaire(): ?string
    {
        return $this->texte_commentaire;
    }

    public function setTexte_commentaire(?string $texte_commentaire): self
    {
        $this->texte_commentaire = $texte_commentaire;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $type_commentaire = null;

    public function getType_commentaire(): ?string
    {
        return $this->type_commentaire;
    }

    public function setType_commentaire(?string $type_commentaire): self
    {
        $this->type_commentaire = $type_commentaire;
        return $this;
    }

    public function getDateCommentaire(): ?\DateTime
    {
        return $this->date_commentaire;
    }

    public function setDateCommentaire(?\DateTime $date_commentaire): static
    {
        $this->date_commentaire = $date_commentaire;

        return $this;
    }

    public function getTexteCommentaire(): ?string
    {
        return $this->texte_commentaire;
    }

    public function setTexteCommentaire(?string $texte_commentaire): static
    {
        $this->texte_commentaire = $texte_commentaire;

        return $this;
    }

    public function getTypeCommentaire(): ?string
    {
        return $this->type_commentaire;
    }

    public function setTypeCommentaire(?string $type_commentaire): static
    {
        $this->type_commentaire = $type_commentaire;

        return $this;
    }

}
