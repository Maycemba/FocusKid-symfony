<?php

namespace App\Service;

use App\Entity\LoginAnomaly;
use App\Repository\LoginAnomalyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Detects suspicious login behaviour and stores + emails alerts.
 *
 * Rules:
 *  R1 — 5+ failed attempts from the same IP in 10 minutes
 *  R2 — Any login attempt between 01:00 and 05:00
 *  R3 — 10+ attempts on DIFFERENT usernames from the same IP (brute-force / enumeration)
 *
 * SAFETY GUARANTEE:
 *  Every public method is wrapped in try/catch — a DB failure or mail error
 *  will NEVER propagate to the login flow. Worst case: silent fail, user logs in normally.
 */
class SuspiciousLoginDetector
{
    // ── Thresholds ────────────────────────────────────────────────────────────
    private const FAIL_LIMIT          = 5;   // R1
    private const WINDOW_MINUTES      = 10;  // R1 & R3 rolling window
    private const UNUSUAL_HOUR_START  = 1;   // R2 start  (01:00 inclusive)
    private const UNUSUAL_HOUR_END    = 5;   // R2 end    (05:00 exclusive)
    private const USERNAME_ENUM_LIMIT = 10;  // R3

    private const ADMIN_EMAIL = 'soussiaaziz10@gmail.com';

    // Raw-event marker stored in reason so the repo can filter them out of dashboards
    private const RAW_PREFIX = '__RAW__';

    public function __construct(
        private LoginAnomalyRepository $repo,
        private EntityManagerInterface $em,
        private MailerInterface        $mailer
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // PUBLIC API — call these from LoginAuthenticator, NOTHING ELSE NEEDED
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Call on every FAILED login attempt.
     */
    public function handleFailedAttempt(string $ip, string $username): void
    {
        try {
            // 1. Persist the raw failure event (used as the counter for R1 & R3)
            $this->persistRaw($ip, $username);

            // 2. Run all three rules
            $this->checkR1($ip, $username);
            $this->checkR2($ip, $username, successful: false);
            $this->checkR3($ip, $username);

        } catch (\Throwable) {
            // NEVER break the login flow
        }
    }

    /**
     * Call on every SUCCESSFUL login.
     * Only R2 (unusual hour) is meaningful here.
     */
    public function handleSuccessfulLogin(string $ip, string $username): void
    {
        try {
            $this->checkR2($ip, $username, successful: true);
        } catch (\Throwable) {
            // NEVER break the login flow
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DETECTION RULES
    // ─────────────────────────────────────────────────────────────────────────

    /** R1 — Too many failures from the same IP */
    private function checkR1(string $ip, string $username): void
    {
        $count = $this->repo->countRecentFailuresByIp($ip, self::WINDOW_MINUTES);

        if ($count >= self::FAIL_LIMIT) {
            $reason = sprintf(
                '🔴 R1 — %d tentatives échouées depuis l\'IP %s en %d minutes.',
                $count, $ip, self::WINDOW_MINUTES
            );
            $this->flagAndAlert($ip, $username, $reason, '🔴 R1');
        }
    }

    /** R2 — Attempt during unusual hours (01:00–05:00) */
    private function checkR2(string $ip, string $username, bool $successful): void
    {
        $hour = (int) (new \DateTime())->format('G'); // 0–23

        if ($hour >= self::UNUSUAL_HOUR_START && $hour < self::UNUSUAL_HOUR_END) {
            $type   = $successful ? 'réussie' : 'échouée';
            $reason = sprintf(
                '🔴 R2 — Connexion %s à heure inhabituelle (%02dh) pour "%s" depuis %s.',
                $type, $hour, $username, $ip
            );
            $this->flagAndAlert($ip, $username, $reason, '🔴 R2');
        }
    }

    /** R3 — Many different usernames tried from the same IP */
    private function checkR3(string $ip, string $username): void
    {
        $distinct = $this->repo->countDistinctUsernamesFromIp($ip, self::WINDOW_MINUTES);

        if ($distinct >= self::USERNAME_ENUM_LIMIT) {
            $reason = sprintf(
                '🔴 R3 — %d noms d\'utilisateur distincts essayés depuis %s en %d minutes (brute-force détecté).',
                $distinct, $ip, self::WINDOW_MINUTES
            );
            $this->flagAndAlert($ip, $username, $reason, '🔴 R3');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Persist + alert, but only once per rule per IP per window (dedup).
     */
    private function flagAndAlert(string $ip, string $username, string $reason, string $ruleTag): void
    {
        if ($this->repo->existsRecentAlert($ip, $ruleTag, self::WINDOW_MINUTES)) {
            return; // already alerted for this rule+IP in this window — skip
        }

        $anomaly = new LoginAnomaly($username, $ip, $reason);
        $this->em->persist($anomaly);
        $this->em->flush();

        $this->sendAlertEmail($anomaly);
    }

    /**
     * Persist a raw failure counter row (never shown in dashboard).
     */
    private function persistRaw(string $ip, string $username): void
    {
        $raw = new LoginAnomaly($username, $ip, self::RAW_PREFIX . '|' . $username);
        $this->em->persist($raw);
        $this->em->flush();
    }

    private function sendAlertEmail(LoginAnomaly $anomaly): void
    {
        $html = sprintf('
        <div style="font-family:Arial,sans-serif;max-width:580px;margin:0 auto;padding:20px">

            <div style="background:linear-gradient(135deg,#c53030,#e53e3e);
                        border-radius:16px;padding:28px;text-align:center;margin-bottom:24px">
                <h1 style="color:white;margin:0;font-size:26px">🚨 FocusKid — Alerte Sécurité</h1>
                <p style="color:rgba(255,255,255,0.85);margin:8px 0 0;font-size:14px">
                    Comportement suspect détecté lors d\'une tentative de connexion
                </p>
            </div>

            <div style="background:#fff5f5;border-left:4px solid #e53e3e;
                        border-radius:8px;padding:20px;margin-bottom:20px">
                <p style="margin:0 0 6px;color:#742a2a;font-weight:bold;font-size:14px">
                    ⚠️ Raison de l\'alerte
                </p>
                <p style="margin:0;color:#2d3748;font-size:15px;line-height:1.6">
                    %s
                </p>
            </div>

            <table style="width:100%%;border-collapse:collapse;font-size:13px;color:#4a5568">
                <tr style="background:#f7fafc">
                    <td style="padding:10px 14px;border:1px solid #e2e8f0;font-weight:bold;width:140px">👤 Utilisateur</td>
                    <td style="padding:10px 14px;border:1px solid #e2e8f0">%s</td>
                </tr>
                <tr>
                    <td style="padding:10px 14px;border:1px solid #e2e8f0;font-weight:bold">🌐 Adresse IP</td>
                    <td style="padding:10px 14px;border:1px solid #e2e8f0">%s</td>
                </tr>
                <tr style="background:#f7fafc">
                    <td style="padding:10px 14px;border:1px solid #e2e8f0;font-weight:bold">🕐 Détecté le</td>
                    <td style="padding:10px 14px;border:1px solid #e2e8f0">%s</td>
                </tr>
                <tr>
                    <td style="padding:10px 14px;border:1px solid #e2e8f0;font-weight:bold">🆔 Anomalie #</td>
                    <td style="padding:10px 14px;border:1px solid #e2e8f0">%d</td>
                </tr>
            </table>

            <p style="color:#a0aec0;font-size:11px;margin-top:24px;text-align:center">
                Alerte générée automatiquement par le système de sécurité FocusKid.
            </p>
        </div>',
            htmlspecialchars($anomaly->getReason()),
            htmlspecialchars($anomaly->getUsername()),
            htmlspecialchars($anomaly->getIpAddress()),
            $anomaly->getDetectedAt()->format('d/m/Y à H:i:s'),
            $anomaly->getId()
        );

        try {
            $mail = (new Email())
                ->from(new Address(self::ADMIN_EMAIL, 'FocusKid Sécurité'))
                ->to(self::ADMIN_EMAIL)
                ->subject('🚨 FocusKid — Alerte: ' . substr($anomaly->getReason(), 0, 70))
                ->html($html)
                ->priority(Email::PRIORITY_HIGH);

            $this->mailer->send($mail);

            $anomaly->setAlertSent(true);
            $this->em->flush();

        } catch (\Throwable) {
            // Mail failure must never break anything
        }
    }
}