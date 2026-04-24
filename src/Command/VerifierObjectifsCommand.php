<?php

namespace App\Command;

use App\Repository\ObjectifRepository;
use App\Service\MailtrapApiService;  // Changement ici
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:verifier-objectifs', description: 'Vérifie les objectifs et envoie des notifications')]
class VerifierObjectifsCommand extends Command
{
    private ObjectifRepository $objectifRepository;
    private MailtrapApiService $mailtrapApiService;  // Changement ici
    private EntityManagerInterface $entityManager;

    public function __construct(
        ObjectifRepository $objectifRepository,
        MailtrapApiService $mailtrapApiService,  // Changement ici
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->objectifRepository = $objectifRepository;
        $this->mailtrapApiService = $mailtrapApiService;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Vérification des objectifs...');
        
        // Récupérer les objectifs à vérifier (ex: ceux qui se terminent aujourd'hui)
        $objectifs = $this->objectifRepository->findBy(['statut' => 'EN_COURS']);
        
        foreach ($objectifs as $objectif) {
            $dateFin = $objectif->getDateFin();
            $aujourdhui = new \DateTime();
            
            if ($dateFin < $aujourdhui) {
                // Objectif expiré, marquer comme non atteint
                $objectif->setStatut('NON_ATTEINT');
                $this->entityManager->flush();
                
                // Envoyer notification
                $result = $this->mailtrapApiService->envoyerNotification($objectif);
                
                $output->writeln(sprintf(
                    'Objectif "%s" marqué comme non atteint. Email envoyé: %s',
                    $objectif->getDescription(),
                    $result['success'] ? 'Oui' : 'Non'
                ));
            }
        }
        
        $output->writeln('Vérification terminée.');
        return Command::SUCCESS;
    }
}