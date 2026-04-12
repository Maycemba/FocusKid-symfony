<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\AlertesEmailRepository;

#[ORM\Entity(repositoryClass: AlertesEmailRepository::class)]
#[ORM\Table(name: 'alertes_email')]
class AlertesEmail
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

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $enfant_id = null;

    public function getEnfant_id(): ?int
    {
        return $this->enfant_id;
    }

    public function setEnfant_id(int $enfant_id): self
    {
        $this->enfant_id = $enfant_id;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $type_alerte = null;

    public function getType_alerte(): ?string
    {
        return $this->type_alerte;
    }

    public function setType_alerte(string $type_alerte): self
    {
        $this->type_alerte = $type_alerte;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date_envoi = null;

    public function getDate_envoi(): ?\DateTimeInterface
    {
        return $this->date_envoi;
    }

    public function setDate_envoi(?\DateTimeInterface $date_envoi): self
    {
        $this->date_envoi = $date_envoi;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $email_envoye = null;

    public function getEmail_envoye(): ?string
    {
        return $this->email_envoye;
    }

    public function setEmail_envoye(?string $email_envoye): self
    {
        $this->email_envoye = $email_envoye;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $session_ids = null;

    public function getSession_ids(): ?string
    {
        return $this->session_ids;
    }

    public function setSession_ids(?string $session_ids): self
    {
        $this->session_ids = $session_ids;
        return $this;
    }

    public function getEnfantId(): ?int
    {
        return $this->enfant_id;
    }

    public function setEnfantId(int $enfant_id): static
    {
        $this->enfant_id = $enfant_id;

        return $this;
    }

    public function getTypeAlerte(): ?string
    {
        return $this->type_alerte;
    }

    public function setTypeAlerte(string $type_alerte): static
    {
        $this->type_alerte = $type_alerte;

        return $this;
    }

    public function getDateEnvoi(): ?\DateTime
    {
        return $this->date_envoi;
    }

    public function setDateEnvoi(?\DateTime $date_envoi): static
    {
        $this->date_envoi = $date_envoi;

        return $this;
    }

    public function getEmailEnvoye(): ?string
    {
        return $this->email_envoye;
    }

    public function setEmailEnvoye(?string $email_envoye): static
    {
        $this->email_envoye = $email_envoye;

        return $this;
    }

    public function getSessionIds(): ?string
    {
        return $this->session_ids;
    }

    public function setSessionIds(?string $session_ids): static
    {
        $this->session_ids = $session_ids;

        return $this;
    }

}
