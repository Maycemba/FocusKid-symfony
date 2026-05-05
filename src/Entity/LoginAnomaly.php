<?php

namespace App\Entity;

use App\Repository\LoginAnomalyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LoginAnomalyRepository::class)]
#[ORM\Table(name: 'login_anomaly')]
class LoginAnomaly
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'username', type: 'string', length: 100)]
    private string $username;

    #[ORM\Column(name: 'ip_address', type: 'string', length: 45)]
    private string $ipAddress;

    #[ORM\Column(name: 'reason', type: 'string', length: 255)]
    private string $reason;

    #[ORM\Column(name: 'detected_at', type: 'datetime')]
    private \DateTimeInterface $detectedAt;

    #[ORM\Column(name: 'alert_sent', type: 'boolean', options: ['default' => false])]
    private bool $alertSent = false;

    public function __construct(string $username, string $ipAddress, string $reason)
    {
        $this->username   = $username;
        $this->ipAddress  = $ipAddress;
        $this->reason     = $reason;
        $this->detectedAt = new \DateTime();
        $this->alertSent  = false;
    }

    public function getId(): ?int                        { return $this->id; }
    public function getUsername(): string                { return $this->username; }
    public function getIpAddress(): string               { return $this->ipAddress; }
    public function getReason(): string                  { return $this->reason; }
    public function getDetectedAt(): \DateTimeInterface  { return $this->detectedAt; }
    public function isAlertSent(): bool                  { return $this->alertSent; }

    public function setAlertSent(bool $v): static
    {
        $this->alertSent = $v;
        return $this;
    }
}