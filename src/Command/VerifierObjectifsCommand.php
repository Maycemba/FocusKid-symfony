<?php

namespace App\Command;

use App\Service\NotificationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:verifier-objectifs',
    description: 'Vérifie les objectifs terminés aujourd\'hui et envoie les notifications'
)]
class VerifierObjectifsCommand extends Command
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Vérification des objectifs terminés');
        
        $resultats = $this->notificationService->verifierEtNotifierObjectifsTermines();
        
        if (empty($resultats)) {
            $io->success('Aucun objectif terminé aujourd\'hui.');
            return Command::SUCCESS;
        }
        
        $io->table(
            ['ID', 'Description', 'Statut', 'Email envoyé'],
            array_map(function($r) {
                return [$r['objectif_id'], $r['description'], $r['statut'], $r['email_envoye'] ? '✅ Oui' : '❌ Non'];
            }, $resultats)
        );
        
        $io->success(count($resultats) . ' notification(s) envoyée(s)');
        
        return Command::SUCCESS;
    }
}