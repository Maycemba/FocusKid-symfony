<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Repository\UtilisateurRepository;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: 'utilisateur')]
#[UniqueEntity(fields: ['username'], message: 'Ce nom d\'utilisateur est déjà utilisé.')]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
class Utilisateur implements UserInterface
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

    #[ORM\Column(type: 'string', length: 50, nullable: false, unique: true)]
    #[Assert\NotBlank(message: 'Le nom d\'utilisateur est obligatoire.')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Le nom d\'utilisateur doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le nom d\'utilisateur ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9_]+$/',
        message: 'Le nom d\'utilisateur ne peut contenir que des lettres, chiffres et underscores.'
    )]
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

    #[ORM\Column(type: 'string', length: 100, nullable: false, unique: true)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'L\'adresse email « {{ value }} » n\'est pas valide.')]
    #[Assert\Length(
        max: 100,
        maxMessage: 'L\'email ne peut pas dépasser {{ limit }} caractères.'
    )]
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

    #[ORM\Column(type: 'string', length: 255, nullable: false, name: 'passwordHash')]
    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire.')]
    #[Assert\Length(
        min: 4,
        max: 255,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'Le mot de passe ne peut pas dépasser {{ limit }} caractères.'
    )]
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

    #[ORM\Column(type: 'string', length: 100, nullable: false)]
    #[Assert\NotBlank(message: 'Le rôle est obligatoire.')]
    #[Assert\Choice(
        choices: ['admin', 'enfant', 'parent'],
        message: 'Le rôle doit être admin, enfant ou parent.'
    )]
    private ?string $role = null;

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: true, name: 'isActive')]
    private ?bool $isActive = true;

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    #[ORM\Column(type: 'datetime', nullable: true, name: 'createdAt')]
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

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $notifications_email = false;

    public function isNotifications_email(): ?bool
    {
        return $this->notifications_email;
    }

    public function setNotifications_email(?bool $notifications_email): self
    {
        $this->notifications_email = $notifications_email;
        return $this;
    }

    public function isNotificationsEmail(): ?bool
    {
        return $this->notifications_email;
    }

    public function setNotificationsEmail(?bool $notifications_email): static
    {
        $this->notifications_email = $notifications_email;
        return $this;
    }
    #[ORM\Column(type: 'string', length: 10, nullable: true)]
private ?string $resetToken = null;

public function getResetToken(): ?string
{
    return $this->resetToken;
}

public function setResetToken(?string $resetToken): self
{
    $this->resetToken = $resetToken;
    return $this;
}

#[ORM\Column(type: 'datetime', nullable: true)]
private ?\DateTimeInterface $resetTokenExpiry = null;

public function getResetTokenExpiry(): ?\DateTimeInterface
{
    return $this->resetTokenExpiry;
}

public function setResetTokenExpiry(?\DateTimeInterface $resetTokenExpiry): self
{
    $this->resetTokenExpiry = $resetTokenExpiry;
    return $this;
}

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

    public function __construct()
    {
        $this->carnetEducatifs      = new ArrayCollection();
        $this->reponseUserExercices = new ArrayCollection();
        $this->reponseUserQuizs     = new ArrayCollection();
        $this->scores               = new ArrayCollection();
        $this->sessionsDeCalmes     = new ArrayCollection();
        $this->isActive             = true;
        $this->notifications_email  = false;
        $this->createdAt            = new \DateTime();
    }

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

    public function removeReponseUserExercice(ReponseUserExercice $reponseUserExercice): self
    {
        $this->getReponseUserExercices()->removeElement($reponseUserExercice);
        return $this;
    }

    public function getReponseUserQuizs(): Collection
    {
        if (!$this->reponseUserQuizs instanceof Collection) {
            $this->reponseUserQuizs = new ArrayCollection();
        }
        return $this->reponseUserQuizs;
    }

    public function addReponseUserQuiz(ReponseUserQuiz $reponseUserQuiz): self
    {
        if (!$this->getReponseUserQuizs()->contains($reponseUserQuiz)) {
            $this->getReponseUserQuizs()->add($reponseUserQuiz);
        }
        return $this;
    }

    public function removeReponseUserQuiz(ReponseUserQuiz $reponseUserQuiz): self
    {
        $this->getReponseUserQuizs()->removeElement($reponseUserQuiz);
        return $this;
    }

    public function getScores(): Collection
    {
        if (!$this->scores instanceof Collection) {
            $this->scores = new ArrayCollection();
        }
        return $this->scores;
    }

    public function addScore(Score $score): self
    {
        if (!$this->getScores()->contains($score)) {
            $this->getScores()->add($score);
        }
        return $this;
    }

    public function removeScore(Score $score): self
    {
        $this->getScores()->removeElement($score);
        return $this;
    }

    public function getSessionsDeCalmes(): Collection
    {
        if (!$this->sessionsDeCalmes instanceof Collection) {
            $this->sessionsDeCalmes = new ArrayCollection();
        }
        return $this->sessionsDeCalmes;
    }

    public function addSessionsDeCalme(SessionsDeCalme $sessionsDeCalme): self
    {
        if (!$this->getSessionsDeCalmes()->contains($sessionsDeCalme)) {
            $this->getSessionsDeCalmes()->add($sessionsDeCalme);
        }
        return $this;
    }

    public function removeSessionsDeCalme(SessionsDeCalme $sessionsDeCalme): self
    {
        $this->getSessionsDeCalmes()->removeElement($sessionsDeCalme);
        return $this;
    }

    // ── UserInterface ──────────────────────────────────────

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

public function getRoles(): array
{
    $roles = ['ROLE_USER']; // MUST always be present

    $roles[] = match($this->role) {
        'admin'  => 'ROLE_ADMIN',
        'parent' => 'ROLE_PARENT',
        'enfant' => 'ROLE_ENFANT',
        default  => 'ROLE_USER',
    };

    return array_unique($roles);
}

    public function getPassword(): ?string
    {
        return $this->passwordHash;
    }

    public function eraseCredentials(): void
    {
        // nothing sensitive stored in memory
    }
}