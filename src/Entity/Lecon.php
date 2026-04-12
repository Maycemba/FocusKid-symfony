<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\LeconRepository;

#[ORM\Entity(repositoryClass: LeconRepository::class)]
#[ORM\Table(name: 'lecon')]
class Lecon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_lecon = null;

    public function getId_lecon(): ?int
    {
        return $this->id_lecon;
    }

    public function setId_lecon(int $id_lecon): self
    {
        $this->id_lecon = $id_lecon;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $titre_lecon = null;

    public function getTitre_lecon(): ?string
    {
        return $this->titre_lecon;
    }

    public function setTitre_lecon(string $titre_lecon): self
    {
        $this->titre_lecon = $titre_lecon;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $contenu = null;

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(?string $contenu): self
    {
        $this->contenu = $contenu;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Cour::class, inversedBy: 'lecons')]
    #[ORM\JoinColumn(name: 'id_cours', referencedColumnName: 'id_cours')]
    private ?Cour $cour = null;

    public function getCour(): ?Cour
    {
        return $this->cour;
    }

    public function setCour(?Cour $cour): self
    {
        $this->cour = $cour;
        return $this;
    }

    public function getIdLecon(): ?int
    {
        return $this->id_lecon;
    }

    public function getTitreLecon(): ?string
    {
        return $this->titre_lecon;
    }

    public function setTitreLecon(string $titre_lecon): static
    {
        $this->titre_lecon = $titre_lecon;

        return $this;
    }

}
