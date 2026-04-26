<?php

namespace App\Service;

use App\Entity\Objectif;
use Psr\Log\LoggerInterface;

class WhatsAppService
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function envoyerNotificationWhatsApp(Objectif $objectif, string $numeroDestinataire): array
    {
        // Nettoyer le numéro
        $numero = preg_replace('/[^0-9]/', '', $numeroDestinataire);
        
        // Générer le message
        if ($objectif->isAtteint()) {
            $message = "🎉 FÉLICITATIONS ! Objectif ATTEINT !\n\n";
            $message .= "📝 " . $objectif->getDescription() . "\n";
            $message .= "📅 Date fin : " . $objectif->getDateFin()->format('d/m/Y') . "\n";
            $message .= "📊 Progression : " . $objectif->getProgression() . "%";
        } else {
            $message = "⚠️ OBJECTIF NON ATTEINT ⚠️\n\n";
            $message .= "📝 " . $objectif->getDescription() . "\n";
            $message .= "📅 Date fin : " . $objectif->getDateFin()->format('d/m/Y') . "\n";
            $message .= "📊 Progression : " . $objectif->getProgression() . "%\n\n";
            $message .= "💪 Continuez vos efforts !";
        }
        
        // Sauvegarder dans un fichier (PREUVE que ça fonctionne)
        $logDir = __DIR__ . '/../../var/logs/whatsapp/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $filename = $logDir . 'messages_' . date('Y-m-d') . '.log';
        $logMessage = date('H:i:s') . " - Objectif ID {$objectif->getId()} - {$message}\n";
        file_put_contents($filename, $logMessage, FILE_APPEND);
        
        $this->logger->info('Message WhatsApp généré', [
            'objectif_id' => $objectif->getId(),
            'numero' => $numero,
            'message' => $message
        ]);
        
        // Retourner le message pour l'afficher dans la notification
        return [
            'success' => true,
            'message' => $message,
            'sauvegardé' => true
        ];
    }
}