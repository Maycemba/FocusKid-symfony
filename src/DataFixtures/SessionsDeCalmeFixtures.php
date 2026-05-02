<?php

namespace App\DataFixtures;

use App\Entity\SessionsDeCalme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SessionsDeCalmeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $types = ['musique', 'coloriage', 'respiration', 'histoire'];
        $declencheurs = ['enfant', 'parent'];
        
        for ($i = 1; $i <= 25; $i++) {
            $session = new SessionsDeCalme();
            $session->setTypeActivite($types[array_rand($types)]);
            $session->setDeclencheur($declencheurs[array_rand($declencheurs)]);
            $session->setDureePrevue(rand(5, 30));
            $session->setDureeReelle(rand(3, 35));
            $session->setFeedbackEnfant(rand(1, 5));
            $session->setNoteParent('Commentaire de test pour la session ' . $i);
            $session->setHorodatage(new \DateTime('-' . rand(0, 30) . ' days'));
            
            $manager->persist($session);
        }
        
        $manager->flush();
    }
}