<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MailerService
{
    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function sendParentNotification(
        string $parentEmail, 
        string $parentName, 
        string $childName, 
        array $sessionsStats, 
        string $verdict
    ): void {
        $subject = $verdict === 'success' 
            ? "🎉 Félicitations ! Progrès de votre enfant {$childName}"
            : "⚠️ Alerte - Difficultés détectées pour {$childName}";

        $template = $verdict === 'success' ? 'success' : 'warning';
        
        $email = (new Email())
            ->from('noreply@votresite.com')
            ->to($parentEmail)
            ->subject($subject)
            ->html($this->getEmailTemplate($parentName, $childName, $sessionsStats, $template))
            ->priority($verdict === 'warning' ? Email::PRIORITY_HIGH : Email::PRIORITY_NORMAL);

        $this->mailer->send($email);
    }

    private function getEmailTemplate(string $parentName, string $childName, array $sessionsStats, string $type): string
    {
        if ($type === 'success') {
            return <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; background-color: #f0f8f0; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; background: white; border-radius: 10px; }
                    .header { background: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { padding: 20px; }
                    .stats { background: #e8f5e9; padding: 15px; border-radius: 8px; margin: 15px 0; }
                    .stat-item { margin: 10px 0; }
                    .footer { text-align: center; padding: 15px; color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>🎉 Félicitations !</h1>
                    </div>
                    <div class="content">
                        <h2>Bonjour {$parentName},</h2>
                        <p>Nous avons une excellente nouvelle à vous partager concernant <strong>{$childName}</strong> !</p>
                        <div class="stats">
                            <h3>📊 Statistiques des 3 dernières sessions :</h3>
                            <div class="stat-item">⭐ Note moyenne : <strong>{$sessionsStats['avg_note']}/5</strong></div>
                            <div class="stat-item">✅ Sessions avec note > 3 : <strong>{$sessionsStats['good_sessions']}</strong></div>
                            <div class="stat-item">🕐 Durée totale : <strong>{$sessionsStats['total_duration']} minutes</strong></div>
                        </div>
                        <p>Ces résultats montrent de magnifiques progrès dans la gestion du calme et de la concentration. Continuez à encourager {$childName} dans cette belle lancée !</p>
                        <p>Pour suivre l'évolution, connectez-vous à votre espace parent.</p>
                        <p>Cordialement,<br>L'équipe pédagogique</p>
                    </div>
                    <div class="footer">
                        <p>Cet email est un suivi automatique des sessions de calme.</p>
                    </div>
                </div>
            </body>
            </html>
            HTML;
        }

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background-color: #fff5f5; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; background: white; border-radius: 10px; }
                .header { background: #ff9800; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { padding: 20px; }
                .stats { background: #fff3e0; padding: 15px; border-radius: 8px; margin: 15px 0; }
                .stat-item { margin: 10px 0; }
                .advice { background: #e3f2fd; padding: 15px; border-radius: 8px; margin: 15px 0; }
                .footer { text-align: center; padding: 15px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>⚠️ Alerte : Difficultés détectées</h1>
                </div>
                <div class="content">
                    <h2>Bonjour {$parentName},</h2>
                    <p>Nous souhaitons attirer votre attention sur les dernières sessions de calme de <strong>{$childName}</strong>.</p>
                    <div class="stats">
                        <h3>📊 Statistiques des 3 dernières sessions :</h3>
                        <div class="stat-item">⭐ Note moyenne : <strong>{$sessionsStats['avg_note']}/5</strong></div>
                        <div class="stat-item">⚠️ Sessions avec note < 3 : <strong>{$sessionsStats['bad_sessions']}</strong></div>
                        <div class="stat-item">🕐 Durée totale : <strong>{$sessionsStats['total_duration']} minutes</strong></div>
                    </div>
                    <div class="advice">
                        <h3>💡 Suggestions pour aider {$childName} :</h3>
                        <ul>
                            <li>Proposez des sessions plus courtes mais régulières</li>
                            <li>Pratiquez les exercices de respiration avec votre enfant</li>
                            <li>Félicitez-le pour chaque effort, même petit</li>
                            <li>Créez un environnement calme et sans distraction</li>
                            <li>Utilisez les histoires relaxantes proposées sur notre plateforme</li>
                        </ul>
                    </div>
                    <p>N'hésitez pas à consulter un professionnel si ces difficultés persistent.</p>
                    <p>Cordialement,<br>L'équipe pédagogique</p>
                </div>
                <div class="footer">
                    <p>Cet email est un suivi automatique des sessions de calme.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
}