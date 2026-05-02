<?php

namespace App\Service;

use App\Entity\LoginAnomaly;
use App\Repository\LoginAnomalyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class AnomalyDetector
{
    private const ADMIN_EMAIL          = 'soussiaaziz10@gmail.com';
    private const MAX_FAILURES_PER_IP  = 5;
    private const MAX_USERNAMES_PER_IP = 10;
    private const SUSPICIOUS_HOUR_START = 1;
    private const SUSPICIOUS_HOUR_END   = 5;
    private const WINDOW_MINUTES        = 10;

    public function __construct(
        private EntityManagerInterface $em,
        private LoginAnomalyRepository $anomalyRepo,
        private MailerInterface $mailer
    ) {}

    public function analyze(string $username, string $ipAddress): ?string
    {
        $reasons = [];
        $since   = new \DateTime('-' . self::WINDOW_MINUTES . ' minutes');

        // ── Count BEFORE logging this attempt ───────────────────────
        $failuresFromIp  = $this->anomalyRepo->countRecentByIp($ipAddress, $since);
        $uniqueUsernames = $this->anomalyRepo->countUniqueUsernamesByIp($ipAddress, $since);
        $hour            = (int)(new \DateTime())->format('G');

        // ── Rule 1: Too many failures from same IP ──────────────────
        if ($failuresFromIp >= self::MAX_FAILURES_PER_IP - 1) {
            $reasons[] = sprintf(
                '%d tentatives échouées depuis l\'IP %s en %d minutes',
                $failuresFromIp + 1,
                $ipAddress,
                self::WINDOW_MINUTES
            );
        }

        // ── Rule 2: Unusual hour ────────────────────────────────────
        if ($hour >= self::SUSPICIOUS_HOUR_START && $hour < self::SUSPICIOUS_HOUR_END) {
            $reasons[] = sprintf('Connexion à une heure inhabituelle (%dh)', $hour);
        }

        // ── Rule 3: Brute force — many different usernames ──────────
        if ($uniqueUsernames >= self::MAX_USERNAMES_PER_IP - 1) {
            $reasons[] = sprintf(
                'Force brute : %d noms d\'utilisateur différents depuis %s',
                $uniqueUsernames + 1,
                $ipAddress
            );
        }

        $reason = count($reasons) > 0
            ? implode(' | ', $reasons)
            : 'Tentative échouée normale.';

        // ── Log this attempt ────────────────────────────────────────
        $anomaly = new LoginAnomaly($username, $ipAddress, $reason);
        $this->em->persist($anomaly);
        $this->em->flush();

        // ── Send alert if suspicious ────────────────────────────────
        if (count($reasons) > 0) {
            $this->sendAlertEmail($username, $ipAddress, $reason, $anomaly);
            return $reason;
        }

        return null;
    }

    private function sendAlertEmail(
        string $username,
        string $ipAddress,
        string $reason,
        LoginAnomaly $anomaly
    ): void {
        try {
            $now = (new \DateTime())->format('d/m/Y à H:i:s');

            $email = (new Email())
                ->from(new Address('soussiaaziz10@gmail.com', 'FocusKid Security'))
                ->to(self::ADMIN_EMAIL)
                ->subject('🚨 FocusKid — Activité suspecte détectée')
                ->html("
                    <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px'>
                        <div style='background:linear-gradient(135deg,#e53e3e,#c53030);
                                    border-radius:16px;padding:32px;text-align:center;margin-bottom:24px'>
                            <h1 style='color:white;margin:0;font-size:28px'>🚨 Alerte Sécurité</h1>
                            <p style='color:rgba(255,255,255,0.85);margin:8px 0 0'>
                                FocusKid — Activité suspecte détectée
                            </p>
                        </div>

                        <div style='background:#fff5f5;border-left:4px solid #e53e3e;
                                    border-radius:8px;padding:20px;margin-bottom:20px'>
                            <p style='margin:0;color:#c53030;font-weight:bold;font-size:16px'>
                                ⚠️ " . htmlspecialchars($reason) . "
                            </p>
                        </div>

                        <table style='width:100%;border-collapse:collapse;background:#f7fafc;border-radius:8px'>
                            <tr style='background:#edf2f7'>
                                <td style='padding:12px 16px;font-weight:bold;color:#4a5568;width:40%'>👤 Nom d'utilisateur</td>
                                <td style='padding:12px 16px;color:#2d3748'>" . htmlspecialchars($username) . "</td>
                            </tr>
                            <tr>
                                <td style='padding:12px 16px;font-weight:bold;color:#4a5568'>🌐 Adresse IP</td>
                                <td style='padding:12px 16px;color:#2d3748'>" . htmlspecialchars($ipAddress) . "</td>
                            </tr>
                            <tr style='background:#edf2f7'>
                                <td style='padding:12px 16px;font-weight:bold;color:#4a5568'>🕐 Date & Heure</td>
                                <td style='padding:12px 16px;color:#2d3748'>" . $now . "</td>
                            </tr>
                        </table>

                        <div style='margin-top:24px;padding:16px;background:#f0fff4;
                                    border-radius:8px;border-left:4px solid #38a169'>
                            <p style='margin:0;color:#276749;font-size:14px'>
                                💡 Connectez-vous au tableau de bord admin pour gérer cet utilisateur.
                            </p>
                        </div>

                        <p style='color:#a0aec0;font-size:12px;text-align:center;margin-top:24px'>
                            FocusKid Security System — Alerte automatique
                        </p>
                    </div>
                ")
                ->priority(Email::PRIORITY_HIGH);

            $this->mailer->send($email);
            $anomaly->setAlertSent(true);
            $this->em->flush();

        } catch (\Exception $e) {
            // Silently fail — don't break login flow
        }
    }
}