<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\UtilisateurRepository;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: 'utilisateur')]
class Utilisateur
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

    #[ORM\Column(type: 'string', nullable: false)]
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

    #[ORM\Column(type: 'string', nullable: false)]
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

    #[ORM\Column(type: 'string', nullable: false, name: 'passwordHash')]
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

    #[ORM\Column(type: 'string', nullable: false)]
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
    private ?bool $notifications_email = null;

    public function isNotifications_email(): ?bool
    {
        return $this->notifications_email;
    }

    public function setNotifications_email(?bool $notifications_email): self
    {
        $this->notifications_email = $notifications_email;
        return $this;
    }

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

    public function removeReponseUserExercice(ReponseUserExercice $reponseUserExercice): self
    {
        $this->getReponseUserExercices()->removeElement($reponseUserExercice);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: ReponseUserQuiz::class, mappedBy: 'utilisateur')]
    private Collection $reponseUserQuizs;

    /**
     * @return Collection<int, ReponseUserQuiz>
     */
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

    #[ORM\OneToMany(targetEntity: Score::class, mappedBy: 'utilisateur')]
    private Collection $scores;

    /**
     * @return Collection<int, Score>
     */
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

    #[ORM\OneToMany(targetEntity: SessionsDeCalme::class, mappedBy: 'utilisateur')]
    private Collection $sessionsDeCalmes;

    public function __construct()
    {
        $this->carnetEducatifs = new ArrayCollection();
        $this->reponseUserExercices = new ArrayCollection();
        $this->reponseUserQuizs = new ArrayCollection();
        $this->scores = new ArrayCollection();
        $this->sessionsDeCalmes = new ArrayCollection();
    }

    /**
     * @return Collection<int, SessionsDeCalme>
     */
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

    public function isActive(): ?bool
    {
        return $this->isActive;
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

}
