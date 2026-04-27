<?php
// src/Service/EmailService.php

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Psr\Log\LoggerInterface;

class EmailService
{
    private $logger;
    private $mailerConfig;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        
        // Configuration depuis le .env
        $this->mailerConfig = [
            'host' => $_ENV['MAILER_HOST'] ?? 'smtp.gmail.com',
            'port' => $_ENV['MAILER_PORT'] ?? 587,
            'username' => $_ENV['MAILER_USERNAME'] ?? '',
            'password' => $_ENV['MAILER_PASSWORD'] ?? '',
            'from_email' => $_ENV['MAILER_FROM_EMAIL'] ?? 'noreply@focuskids.com',
            'from_name' => $_ENV['MAILER_FROM_NAME'] ?? 'FocusKids'
        ];
    }

    /**
     * Envoie un email à l'enfant pour un nouvel exercice
     */
    public function sendNewExerciseNotification(string $toEmail, string $enfantNom, string $exerciceTitre, string $exerciceType, string $exerciceConsigne, string $dateAttribution, string $jours): bool
    {
        // Traduire le type d'exercice
        $typeLabels = [
            'MEMOIRE' => '🧠 Mémoire',
            'ATTENTION' => '🎯 Attention',
            'LOGIQUE' => '🧩 Logique',
            'CHRONO' => '⏱️ Chrono'
        ];
        $typeLabel = $typeLabels[$exerciceType] ?? $exerciceType;

        $html = $this->getEmailTemplate($enfantNom, $exerciceTitre, $typeLabel, $exerciceConsigne, $dateAttribution, $jours);

        $mail = new PHPMailer(true);

        try {
            // Configuration SMTP
            $mail->isSMTP();
            $mail->Host = $this->mailerConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->mailerConfig['username'];
            $mail->Password = $this->mailerConfig['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->mailerConfig['port'];
            $mail->CharSet = 'UTF-8';

            // Expéditeur et destinataire
            $mail->setFrom($this->mailerConfig['from_email'], $this->mailerConfig['from_name']);
            $mail->addAddress($toEmail, $enfantNom);

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = '📚 Nouvel exercice assigné - FocusKids';
            $mail->Body = $html;
            $mail->AltBody = strip_tags($html);

            $mail->send();
            $this->logger->info('Email envoyé à ' . $toEmail . ' pour l\'exercice ' . $exerciceTitre);
            return true;

        } catch (Exception $e) {
            $this->logger->error('Erreur lors de l\'envoi d\'email: ' . $mail->ErrorInfo);
            return false;
        }
    }

    private function getEmailTemplate(string $enfantNom, string $exerciceTitre, string $exerciceType, string $consigne, string $dateAttribution, string $jours): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nouvel exercice FocusKids</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .info-card {
            background: #f0f7ff;
            border-radius: 12px;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #667eea;
        }
        .info-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .info-value {
            color: #555;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #888;
            font-size: 12px;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 10px 25px;
            border-radius: 25px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 FocusKids</h1>
            <p>Nouvel exercice disponible</p>
        </div>
        <div class="content">
            <h2>Bonjour $enfantNom ! 👋</h2>
            <p>Un nouvel exercice vient de t'être assigné.</p>
            
            <div class="info-card">
                <div class="info-title">📖 Exercice</div>
                <div class="info-value"><strong>$exerciceTitre</strong> ($exerciceType)</div>
            </div>
            
            <div class="info-card">
                <div class="info-title">📝 Consigne</div>
                <div class="info-value">$consigne</div>
            </div>
            
            <div class="info-card">
                <div class="info-title">📅 Date d'attribution</div>
                <div class="info-value">$dateAttribution</div>
            </div>
            
            <div class="info-card">
                <div class="info-title">📅 Jours planifiés</div>
                <div class="info-value">$jours</div>
            </div>
            
            <div style="text-align: center;">
                <a href="#" class="btn">🎮 Commencer l'exercice</a>
            </div>
        </div>
        <div class="footer">
            <p>FocusKids - Plateforme éducative pour enfants</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}