<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:test-mailer',
    description: 'Test l\'envoi d\'email avec la configuration actuelle'
)]
class TestMailerCommand extends Command
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        parent::__construct();
        $this->mailer = $mailer;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Test de configuration Mailer');
        
        // Afficher la configuration actuelle
        $io->text('Configuration actuelle:');
        $io->text('- DSN: ' . ($_ENV['MAILER_DSN'] ?? 'Non défini'));
        
        $io->newLine();
        $io->text('Tentative d\'envoi d\'email...');
        
        try {
            $email = (new Email())
                ->from('noreply@focuskid.com')
                ->to('ayamosbeh91@gmail.com')
                ->subject('Test de configuration Mailer - FocusKid')
                ->html('<h1>Test réussi !</h1><p>Votre configuration email fonctionne parfaitement.</p>');
            
            $this->mailer->send($email);
            
            $io->success('✅ Email envoyé avec succès !');
            $io->text('Vérifiez votre boîte de réception (ou Mailtrap)');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('❌ Erreur lors de l\'envoi: ' . $e->getMessage());
            $io->text('Vérifiez votre configuration MAILER_DSN dans le fichier .env');
            
            return Command::FAILURE;
        }
    }
}