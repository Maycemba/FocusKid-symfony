<?php

namespace App\Service;

use App\Repository\SessionsDeCalmeRepository;
use Doctrine\ORM\EntityManagerInterface;

class SessionAnalyzerService
{
    private array $processedUsers = [];

    public function __construct(
        private SessionsDeCalmeRepository $sessionRepository,
        private MailerService $mailerService,
        private EntityManagerInterface $entityManager
    ) {}

    public function checkAndNotifyLastThreeSessions(int $userId): void
    {
        // Éviter les doublons d'envoi
        if (in_array($userId, $this->processedUsers)) {
            return;
        }
        
        // Récupérer les 3 dernières sessions de l'utilisateur
        $lastSessions = $this->sessionRepository->findBy(
            ['utilisateur' => $userId],
            ['horodatage' => 'DESC'],
            3
        );

        if (count($lastSessions) < 3) {
            return; // Pas assez de sessions
        }

        $user = $lastSessions[0]->getUtilisateur();
        
        // Vérifier si l'utilisateur a un parent associé
        if (!$user || !$user->getParent() || !$user->getParent()->getEmail()) {
            return;
        }

        $parent = $user->getParent();
        $notes = [];
        $goodSessions = 0;
        $badSessions = 0;
        $totalDuration = 0;

        foreach ($lastSessions as $session) {
            $note = $session->getFeedbackEnfant();
            if ($note !== null) {
                $notes[] = $note;
                if ($note > 3) {
                    $goodSessions++;
                } elseif ($note < 3) {
                    $badSessions++;
                }
            }
            
            $duration = $session->getDureeReelle();
            if ($duration !== null) {
                $totalDuration += $duration;
            }
        }

        if (empty($notes)) {
            return; // Pas de notes disponibles
        }

        $avgNote = array_sum($notes) / count($notes);
        
        $stats = [
            'avg_note' => round($avgNote, 1),
            'good_sessions' => $goodSessions,
            'bad_sessions' => $badSessions,
            'total_duration' => $totalDuration
        ];

        // Déterminer le verdict
        if ($avgNote > 3) {
            // Envoyer email de félicitations
            $this->mailerService->sendParentNotification(
                $parent->getEmail(),
                $parent->getPrenom() ?? $parent->getNom() ?? 'Parent',
                $user->getPrenom() ?? $user->getNom() ?? 'votre enfant',
                $stats,
                'success'
            );
        } elseif ($avgNote < 3) {
            // Envoyer email d'avertissement
            $this->mailerService->sendParentNotification(
                $parent->getEmail(),
                $parent->getPrenom() ?? $parent->getNom() ?? 'Parent',
                $user->getPrenom() ?? $user->getNom() ?? 'votre enfant',
                $stats,
                'warning'
            );
        }

        $this->processedUsers[] = $userId;
    }
}