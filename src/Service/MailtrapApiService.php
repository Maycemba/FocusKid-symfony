<?php

namespace App\Service;

use App\Entity\Objectif;
use Psr\Log\LoggerInterface;

class MailtrapApiService
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function envoyerNotification(Objectif $objectif): array
    {
        try {
            $emailDir = __DIR__ . '/../../var/emails';

            if (!is_dir($emailDir)) {
                mkdir($emailDir, 0777, true);
            }

            $filename = sprintf(
                '%s/email_%s_objectif_%d.html',
                $emailDir,
                date('Y-m-d_H-i-s'),
                $objectif->getId()
            );

            $contenuHtml = $this->getEmailContent($objectif);

            file_put_contents($filename, $contenuHtml);

            $this->logger->info('Email sauvegardé', [
                'file' => $filename
            ]);

            return [
                'success' => true,
                'message' => '✅ Notification enregistrée avec succès !',
                'html' => $contenuHtml,
                'file' => $filename
            ];
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la génération de l\'email', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ];
        }
    }

    private function getEmailContent(Objectif $objectif): string
    {
        $estAtteint = $objectif->isAtteint();

        $couleur = $estAtteint ? '#27ae60' : '#e74c3c';
        $titre = $estAtteint
            ? '🎉 Félicitations ! Objectif ATTEINT'
            : '⚠️ Objectif NON ATTEINT';

        $message = $estAtteint
            ? 'Bravo ! Vous avez atteint cet objectif avec succès.'
            : 'Cet objectif n\'a pas été atteint. Continuez vos efforts !';

        $dateDebut = $objectif->getDateDebut()?->format('d/m/Y') ?? 'Non définie';
        $dateFin = $objectif->getDateFin()?->format('d/m/Y') ?? 'Non définie';
        $dateGeneration = date('d/m/Y à H:i:s');

        $badgeClass = $estAtteint ? 'badge-success' : 'badge-danger';

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>FocusKid - Notification Objectif</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 650px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .header {
            background: {$couleur};
            color: white;
            padding: 30px;
            text-align: center;
        }

        .content {
            padding: 30px;
        }

        .info-box {
            background: #f9fafb;
            padding: 20px;
            border-left: 5px solid {$couleur};
            border-radius: 8px;
            margin: 20px 0;
        }

        .badge-success {
            background: #d4f7ec;
            color: #0a7a52;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: bold;
        }

        .badge-danger {
            background: #fdecea;
            color: #c0392b;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            padding: 20px;
            background: #f4f4f4;
            color: #777;
            font-size: 13px;
        }

        p {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 FocusKid</h1>
            <p>Suivi éducatif personnalisé</p>
        </div>

        <div class="content">
            <h2 style="color: {$couleur}; margin-top: 0;">
                {$titre}
            </h2>

            <div class="info-box">
                <p><strong>📝 Objectif :</strong> {$objectif->getDescription()}</p>
                <p><strong>📅 Période :</strong> {$dateDebut} → {$dateFin}</p>
                <p><strong>📊 Progression :</strong> {$objectif->getProgression()}%</p>
                <p>
                    <strong>🏷️ Statut :</strong>
                    <span class="{$badgeClass}">
                        {$objectif->getStatutLabel()}
                    </span>
                </p>
            </div>

            <p>{$message}</p>
        </div>

        <div class="footer">
            <p>FocusKid - Notification automatique</p>
            <p>Généré le : {$dateGeneration}</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}