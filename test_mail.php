<?php
require 'vendor/autoload.php';

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

$dsn = 'smtp://naycem.lamouchi@gmail.com:bzqoziczxjwcbyhp@smtp.gmail.com:587';
$transport = Transport::fromDsn($dsn);
$mailer = new Mailer($transport);

$email = (new Email())
    ->from('naycem.lamouchi@gmail.com')
    ->to('naycem.lamouchi@gmail.com') // ← email destinataire
    ->subject('Test FocusKids')
    ->text('Email de test depuis FocusKids !');

try {
    $mailer->send($email);
    echo "✅ Email envoyé avec succès !";
} catch (\Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
}