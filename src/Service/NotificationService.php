<?php

namespace App\Service;

use App\Entity\Objectif;
use App\Repository\ObjectifRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class NotificationService
{
    private MailerInterface $mailer;
    private ObjectifRepository $objectifRepository;
    private string $adminEmail;

    public function __construct(
        MailerInterface $mailer,
        ObjectifRepository $objectifRepository,
        string $adminEmail
    ) {
        $this->mailer = $mailer;
        $this->objectifRepository = $objectifRepository;
        $this->adminEmail = $adminEmail;
    }

    // Vérifier et notifier les objectifs terminés
    public function verifierEtNotifierObjectifsTermines(): array
    {
        $objectifsTermines = $this->objectifRepository->findObjectifsTerminesAujourdhui();
        $notificationsEnvoyees = [];
        
        foreach ($objectifsTermines as $objectif) {
            $resultat = $this->envoyerNotification($objectif);
            $notificationsEnvoyees[] = [
                'objectif_id' => $objectif->getId(),
                'description' => $objectif->getDescription(),
                'statut' => $objectif->getStatut(),
                'email_envoye' => $resultat
            ];
        }
        
        return $notificationsEnvoyees;
    }

    // Envoyer une notification pour un objectif spécifique
    public function envoyerNotification(Objectif $objectif): bool
    {
        try {
            $email = (new TemplatedEmail())
                ->from(new Address($this->adminEmail, 'FocusKid'))
                ->to($this->adminEmail) // À remplacer par l'email réel du parent
                ->subject($this->getSujetEmail($objectif))
                ->htmlTemplate('carnet_educatif/notification_objectif.html.twig')
                ->context([
                    'objectif' => $objectif,
                    'est_atteint' => $objectif->isAtteint(),
                    'est_non_atteint' => $objectif->isNonAtteint()
                ]);
            
            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getSujetEmail(Objectif $objectif): string
    {
        if ($objectif->isAtteint()) {
            return '🎉 Félicitations ! Objectif atteint';
        }
        return '⚠️ Objectif non atteint - Continuez vos efforts';
    }
}