<?php
<<<<<<< HEAD
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
=======
$smtp = fsockopen('smtp.gmail.com', 587, $errno, $errstr, 10);
if ($smtp) {
    echo "✅ Port 587 is OPEN - connection works!";
    fclose($smtp);
} else {
    echo "❌ Port 587 is BLOCKED - error: $errstr ($errno)";
}

$smtp2 = fsockopen('smtp.gmail.com', 465, $errno2, $errstr2, 10);
if ($smtp2) {
    echo "<br>✅ Port 465 is OPEN - connection works!";
    fclose($smtp2);
} else {
    echo "<br>❌ Port 465 is BLOCKED - error: $errstr2 ($errno2)";
}
?>
--dgeg mqcx vtvd vvjl
>>>>>>> origin/User
