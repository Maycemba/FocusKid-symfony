<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Repository\UtilisateurRepository;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: 'utilisateur')]
#[UniqueEntity(fields: ['username'], message: 'Ce nom d\'utilisateur est déjà utilisé.')]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'Username', type: 'string', length: 255, nullable: false)]
    #[Assert\NotBlank(message: 'Le nom d\'utilisateur est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le nom d\'utilisateur doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom d\'utilisateur ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9_]+$/',
        message: 'Le nom d\'utilisateur ne peut contenir que des lettres, chiffres et underscores.'
    )]
    private ?string $username = null;

    #[ORM\Column(name: 'Email', type: 'string', length: 255, nullable: false)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'L\'adresse email « {{ value }} » n\'est pas valide.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'L\'email ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $email = null;

 
 
   #[ORM\Column(name: 'PasswordHash', type: 'string', length: 255, nullable: false)]
private ?string $passwordHash = null;

    #[ORM\Column(name: 'Role', type: 'integer', nullable: false)]
    #[Assert\NotBlank(message: 'Le rôle est obligatoire.')]
    private ?int $role = null;

    #[ORM\Column(name: 'IsActive', type: 'boolean', nullable: true)]
    private ?bool $isActive = true;

    #[ORM\Column(name: 'createdAt', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'notifications_email', type: 'boolean', nullable: true)]
    private ?bool $notificationsEmail = false;

    // ⚠️ Ces colonnes n'existent pas dans votre base de données !
    // Soit vous les ajoutez à la base, soit vous les commentez/supprimez
    
    // #[ORM\Column(type: 'string', length: 255, nullable: true)]
    // private ?string $resetToken = null;

    // #[ORM\Column(type: 'datetime', nullable: true)]
    // private ?\DateTimeInterface $resetTokenExpiry = null;

    // ── Relations ─────────────────────────────────────────

    #[ORM\OneToMany(targetEntity: CarnetEducatif::class, mappedBy: 'utilisateur')]
    private Collection $carnetEducatifs;

    #[ORM\OneToMany(targetEntity: ReponseUserExercice::class, mappedBy: 'utilisateur')]
    private Collection $reponseUserExercices;

    #[ORM\OneToMany(targetEntity: ReponseUserQuiz::class, mappedBy: 'utilisateur')]
    private Collection $reponseUserQuizs;

    #[ORM\OneToMany(targetEntity: Score::class, mappedBy: 'utilisateur')]
    private Collection $scores;

    #[ORM\OneToMany(targetEntity: SessionsDeCalme::class, mappedBy: 'utilisateur')]
    private Collection $sessionsDeCalmes;

    // ── Constructor ───────────────────────────────────────

    public function __construct()
    {
        $this->carnetEducatifs = new ArrayCollection();
        $this->reponseUserExercices = new ArrayCollection();
        $this->reponseUserQuizs = new ArrayCollection();
        $this->scores = new ArrayCollection();
        $this->sessionsDeCalmes = new ArrayCollection();
        $this->isActive = true;
        $this->notificationsEmail = false;
        $this->createdAt = new \DateTime();
    }

    // ── Getters & Setters ─────────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $password): self
{
    // Note: Ne pas hasher ici, le hasher sera utilisé dans le contrôleur
    $this->passwordHash = $password;
    return $this;
}

    public function getRole(): ?int
    {
        return $this->role;
    }

    public function setRole(int $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getNotificationsEmail(): ?bool
    {
        return $this->notificationsEmail;
    }

    public function setNotificationsEmail(?bool $notificationsEmail): self
    {
        $this->notificationsEmail = $notificationsEmail;
        return $this;
    }

    // ── Relations Getters/Setters ─────────────────────────

    public function getCarnetEducatifs(): Collection
    {
        return $this->carnetEducatifs;
    }

    public function addCarnetEducatif(CarnetEducatif $carnetEducatif): self
    {
        if (!$this->carnetEducatifs->contains($carnetEducatif)) {
            $this->carnetEducatifs->add($carnetEducatif);
        }
        return $this;
    }

    public function removeCarnetEducatif(CarnetEducatif $carnetEducatif): self
    {
        $this->carnetEducatifs->removeElement($carnetEducatif);
        return $this;
    }

    public function getReponseUserExercices(): Collection
    {
        return $this->reponseUserExercices;
    }

    public function addReponseUserExercice(ReponseUserExercice $reponseUserExercice): self
    {
        if (!$this->reponseUserExercices->contains($reponseUserExercice)) {
            $this->reponseUserExercices->add($reponseUserExercice);
        }
        return $this;
    }

    public function removeReponseUserExercice(ReponseUserExercice $reponseUserExercice): self
    {
        $this->reponseUserExercices->removeElement($reponseUserExercice);
        return $this;
    }

    public function getReponseUserQuizs(): Collection
    {
        return $this->reponseUserQuizs;
    }

    public function addReponseUserQuiz(ReponseUserQuiz $reponseUserQuiz): self
    {
        if (!$this->reponseUserQuizs->contains($reponseUserQuiz)) {
            $this->reponseUserQuizs->add($reponseUserQuiz);
        }
        return $this;
    }

    public function removeReponseUserQuiz(ReponseUserQuiz $reponseUserQuiz): self
    {
        $this->reponseUserQuizs->removeElement($reponseUserQuiz);
        return $this;
    }

    public function getScores(): Collection
    {
        return $this->scores;
    }

    public function addScore(Score $score): self
    {
        if (!$this->scores->contains($score)) {
            $this->scores->add($score);
        }
        return $this;
    }

    public function removeScore(Score $score): self
    {
        $this->scores->removeElement($score);
        return $this;
    }

    public function getSessionsDeCalmes(): Collection
    {
        return $this->sessionsDeCalmes;
    }

    public function addSessionsDeCalme(SessionsDeCalme $sessionsDeCalme): self
    {
        if (!$this->sessionsDeCalmes->contains($sessionsDeCalme)) {
            $this->sessionsDeCalmes->add($sessionsDeCalme);
        }
        return $this;
    }

    public function removeSessionsDeCalme(SessionsDeCalme $sessionsDeCalme): self
    {
        $this->sessionsDeCalmes->removeElement($sessionsDeCalme);
        return $this;
    }

    // ── UserInterface ──────────────────────────────────────

    public function getUserIdentifier(): string
    {
        return $this->username ?? '';
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_USER'];

        // Convertir le rôle numérique en rôle Symfony
        if ($this->role === 0) {
            $roles[] = 'ROLE_PARENT';
        } elseif ($this->role === 1) {
            $roles[] = 'ROLE_ADMIN';
        } elseif ($this->role === 2) {
            $roles[] = 'ROLE_ENFANT';
        }

        return array_unique($roles);
    }

    public function getPassword(): ?string
    {
        return $this->passwordHash;
    }

    public function eraseCredentials(): void
    {
        // Nothing sensitive stored in memory
    }
    // src/Entity/Utilisateur.php
// Ajoutez ces propriétés après les propriétés existantes

#[ORM\Column(name: 'firstname', type: 'string', length: 100, nullable: true)]
private ?string $firstname = null;

#[ORM\Column(name: 'lastname', type: 'string', length: 100, nullable: true)]
private ?string $lastname = null;

#[ORM\Column(name: 'phone', type: 'string', length: 20, nullable: true)]
private ?string $phone = null;

#[ORM\Column(name: 'birthdate', type: 'date', nullable: true)]
private ?\DateTimeInterface $birthdate = null;

#[ORM\Column(name: 'avatar', type: 'string', length: 255, nullable: true)]
private ?string $avatar = null;

// Ajoutez les getters et setters correspondants

public function getFirstname(): ?string
{
    return $this->firstname;
}

public function setFirstname(?string $firstname): self
{
    $this->firstname = $firstname;
    return $this;
}

public function getLastname(): ?string
{
    return $this->lastname;
}

public function setLastname(?string $lastname): self
{
    $this->lastname = $lastname;
    return $this;
}

public function getPhone(): ?string
{
    return $this->phone;
}

public function setPhone(?string $phone): self
{
    $this->phone = $phone;
    return $this;
}

public function getBirthdate(): ?\DateTimeInterface
{
    return $this->birthdate;
}

public function setBirthdate(?\DateTimeInterface $birthdate): self
{
    $this->birthdate = $birthdate;
    return $this;
}

public function getAvatar(): ?string
{
    return $this->avatar;
}

public function setAvatar(?string $avatar): self
{
    $this->avatar = $avatar;
    return $this;
}

public function getFullName(): string
{
    return trim($this->firstname . ' ' . $this->lastname);
}
// src/Entity/Utilisateur.php
// Ajoutez ces propriétés après les propriétés existantes (vers ligne 50-60)

#[ORM\Column(name: 'reset_token', type: 'string', length: 255, nullable: true)]
private ?string $resetToken = null;

#[ORM\Column(name: 'reset_token_expiry', type: 'datetime', nullable: true)]
private ?\DateTimeInterface $resetTokenExpiry = null;

// Ensuite, ajoutez les getters et setters correspondants
// (ajoutez-les vers la fin de la classe, avant la dernière accolade)

public function getResetToken(): ?string
{
    return $this->resetToken;
}

public function setResetToken(?string $resetToken): self
{
    $this->resetToken = $resetToken;
    return $this;
}

public function getResetTokenExpiry(): ?\DateTimeInterface
{
    return $this->resetTokenExpiry;
}

public function setResetTokenExpiry(?\DateTimeInterface $resetTokenExpiry): self
{
    $this->resetTokenExpiry = $resetTokenExpiry;
    return $this;
}
}