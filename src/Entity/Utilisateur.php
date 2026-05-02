<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use App\Repository\UtilisateurRepository;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: 'utilisateur')]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
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

    #[ORM\Column(name: 'Username', type: 'string', nullable: false)]
    private ?string $username = null;

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    #[ORM\Column(name: 'Email', type: 'string', nullable: false)]
    private ?string $email = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    #[ORM\Column(name: 'PasswordHash', type: 'string', nullable: false)]
    private ?string $passwordHash = null;

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): self
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }

    #[ORM\Column(name: 'Role', type: 'integer', nullable: false)]
    private ?int $role = null;

    public function getRole(): ?int
    {
        return $this->role;
    }

    public function setRole(int $role): self
    {
        $this->role = $role;
        return $this;
    }

    #[ORM\Column(name: 'IsActive', type: 'boolean', nullable: true)]
    private ?bool $isActive = null;

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

  #[ORM\Column(name: 'createdAt', type: 'datetime', nullable: true)]
private ?\DateTimeInterface $createdAt = null;

public function getCreatedAt(): ?\DateTimeInterface
{
    return $this->createdAt;
}

public function setCreatedAt(?\DateTimeInterface $createdAt): self
{
    $this->createdAt = $createdAt;
    return $this;
}

#[ORM\Column(name: 'notifications_email', type: 'boolean', nullable: true)]
private ?bool $notificationsEmail = null;

// Ajoute les getter/setter
public function getNotificationsEmail(): ?bool
{
    return $this->notificationsEmail;
}

public function setNotificationsEmail(?bool $notificationsEmail): self
{
    $this->notificationsEmail = $notificationsEmail;
    return $this;
}
    // #[ORM\Column(type: 'boolean', nullable: true)]
    // private ?bool $notifications_email = null;

    // public function isNotifications_email(): ?bool
    // {
    //     return $this->notifications_email;
    // }

    // public function setNotifications_email(?bool $notifications_email): self
    // {
    //     $this->notifications_email = $notifications_email;
    //     return $this;
    // }

    #[ORM\OneToMany(targetEntity: CarnetEducatif::class, mappedBy: 'utilisateur')]
    private Collection $carnetEducatifs;

    /**
     * @return Collection<int, CarnetEducatif>
     */
    public function getCarnetEducatifs(): Collection
    {
        if (!$this->carnetEducatifs instanceof Collection) {
            $this->carnetEducatifs = new ArrayCollection();
        }
        return $this->carnetEducatifs;
    }

    public function addCarnetEducatif(CarnetEducatif $carnetEducatif): self
    {
        if (!$this->getCarnetEducatifs()->contains($carnetEducatif)) {
            $this->getCarnetEducatifs()->add($carnetEducatif);
        }
        return $this;
    }

    public function removeCarnetEducatif(CarnetEducatif $carnetEducatif): self
    {
        $this->getCarnetEducatifs()->removeElement($carnetEducatif);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: ReponseUserExercice::class, mappedBy: 'utilisateur')]
    private Collection $reponseUserExercices;

    /**
     * @return Collection<int, ReponseUserExercice>
     */
    public function getReponseUserExercices(): Collection
    {
        if (!$this->reponseUserExercices instanceof Collection) {
            $this->reponseUserExercices = new ArrayCollection();
        }
        return $this->reponseUserExercices;
    }

    public function addReponseUserExercice(ReponseUserExercice $reponseUserExercice): self
    {
        if (!$this->getReponseUserExercices()->contains($reponseUserExercice)) {
            $this->getReponseUserExercices()->add($reponseUserExercice);
        }
        return $this;
    }
 
    

    // Méthodes pour UserInterface
    public function getUserIdentifier(): string
    {
        return $this->username; // Utiliser username au lieu d'email
    }

    public function getRoles(): array
    {
        $roles = [];

        // Convertir le rôle numérique en rôle Symfony
        if ($this->role === 0) {
            $roles[] = 'ROLE_PARENT';
        } elseif ($this->role === 1) {
            $roles[] = 'ROLE_ADMIN';
        }

        // Toujours ajouter un rôle par défaut
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function eraseCredentials(): void
    {
        // Si vous stockez des données temporaires sensibles sur l'utilisateur, effacez-les ici
    }

    // Méthodes pour PasswordAuthenticatedUserInterface
    public function getPassword(): ?string
    {
        return $this->passwordHash;
    }

    public function setPassword(string $password): self
    {
        $this->passwordHash = $password;
        return $this;
    }
    
}
