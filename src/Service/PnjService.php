<?php

namespace App\Service;

class PnjService
{
    /**
     * Le "Cerveau" de notre PNJ. 
     * Il contient des règles associant des mots-clés (regex) à des réponses aléatoires.
     */
    private array $rules = [
        'salutations' => [
            'pattern' => '/\b(bonjour|salut|coucou|hey)\b/i',
            'responses' => [
                'Salut ! Prêt à jouer ?',
                'Coucou ! On va bien s\'amuser aujourd\'hui !',
                'Bonjour ! Montre-moi ce que tu sais faire !'
            ]
        ],
        'besoin_aide' => [
            'pattern' => '/\b(aide|bloqué|dur|difficile|sais pas|help)\b/i',
            'responses' => [
                'Ne t\'inquiète pas, prends ton temps ! Lis bien la question.',
                'Si tu es bloqué, essaie de procéder par élimination.',
                'C\'est un peu dur ? Courage, tu vas y arriver !'
            ]
        ],
        'frustration' => [
            'pattern' => '/\b(nul|marre|chiant|bête|triste)\b/i',
            'responses' => [
                'Hé, reste positif ! L\'important c\'est d\'apprendre.',
                'Fais une petite pause si tu en as besoin.',
                'Chaque erreur te rend plus fort !'
            ]
        ],
        'victoire' => [
            'pattern' => '/\b(facile|gagné|super|cool|bravo|oui)\b/i',
            'responses' => [
                'Génial ! Tu es un vrai champion !',
                'C\'est trop facile pour toi on dirait !',
                'Continue comme ça, impressionnant !'
            ]
        ]
    ];

    /**
     * Réponses si le PNJ ne comprend pas la phrase.
     */
    private array $defaultResponses = [
        'Je suis là pour t\'accompagner !',
        'Concentre-toi bien sur le jeu !',
        'Hmm, intéressant...',
        'Tu t\'en sors très bien !'
    ];

    /**
     * Connaissances géographiques basiques du PNJ (Pays => Continent)
     */
    private array $geographyKnowledge = [
        'tunisie' => 'en Afrique',
        'france' => 'en Europe',
        'canada' => 'en Amérique',
        'japon' => 'en Asie',
        'bresil' => 'en Amérique',
        'brésil' => 'en Amérique',
        'australie' => 'en Océanie',
        'maroc' => 'en Afrique',
        'algerie' => 'en Afrique',
        'algérie' => 'en Afrique',
        'senegal' => 'en Afrique',
        'sénégal' => 'en Afrique',
        'belgique' => 'en Europe',
        'suisse' => 'en Europe',
        'chine' => 'en Asie',
        'inde' => 'en Asie',
        'etats-unis' => 'en Amérique',
        'etats unis' => 'en Amérique',
        'égypte' => 'en Afrique',
        'egypte' => 'en Afrique',
        'mali' => 'en Afrique',
        'cameroun' => 'en Afrique'
    ];

    /**
     * Analyse un message utilisateur ou un contexte et retourne une phrase du PNJ.
     * 
     * @param string|null $message Le texte tapé par l'enfant (optionnel)
     * @param string|null $context Un événement du jeu (ex: 'idle', 'mauvaise_reponse') (optionnel)
     * @param string|null $timezone Le fuseau horaire de l'utilisateur (optionnel)
     * @return string La réponse de la mascotte
     */
    public function getReaction(?string $message = null, ?string $context = null, ?string $timezone = null): string
    {
        // 1. Salutation culturelle au lancement
        if ($context === 'greeting') {
            return $this->getCulturalGreeting($timezone);
        }

        // 2. Réaction en fonction d'un événement du jeu (prioritaire)
        if ($context === 'idle') {
            return 'Ouh ouh, tu es toujours là ? Clique sur une réponse !';
        }
        if ($context === 'mauvaise_reponse') {
            return 'Oups, ce n\'est pas ça. Mais essaie encore !';
        }
        if ($context === 'bonne_reponse') {
            return 'Bien joué ! C\'était la bonne réponse.';
        }

        // 2. Réaction en fonction des mots-clés dans le chat/message
        if ($message) {
            foreach ($this->rules as $rule) {
                if (preg_match($rule['pattern'], $message)) {
                    // Choisir une réponse au hasard parmi la catégorie trouvée
                    $randomIndex = array_rand($rule['responses']);
                    return $rule['responses'][$randomIndex];
                }
            }
        }

        // 3. Questions de géographie (ex: "Où se trouve la Tunisie ?")
        if ($message && preg_match('/o[ùu]\s+(est|se\s+trouve)\s+(?:la\s+|le\s+|les\s+|l\')?([a-zA-Z\é\è\ê\-\s\']+)/i', $message, $matches)) {
            $pays = trim(strtolower($matches[2]));
            $pays = preg_replace('/[\?\!\.]/', '', $pays); // On enlève la ponctuation
            $pays = trim($pays);

            if (isset($this->geographyKnowledge[$pays])) {
                $continent = $this->geographyKnowledge[$pays];
                return "C'est $continent ! 🌍";
            } else {
                return "Hmm, je ne connais pas ce pays. Demande à ton professeur de géographie ! 🗺️";
            }
        }

        // 4. Réponse par défaut
        $randomIndex = array_rand($this->defaultResponses);
        return $this->defaultResponses[$randomIndex];
    }

    /**
     * Retourne une salutation personnalisée selon le pays/fuseau horaire.
     */
    private function getCulturalGreeting(?string $timezone): string
    {
        if (!$timezone) {
            return 'Salut ! Prêt à jouer ?';
        }

        if (str_contains($timezone, 'Africa/Dakar')) {
            return 'Nanga def ! Prêt pour une petite partie ?';
        }
        if (str_contains($timezone, 'America/Toronto') || str_contains($timezone, 'America/Montreal') || str_contains($timezone, 'America/Vancouver')) {
            return 'Allo ! Prêt à t\'amuser un peu ?';
        }
        if (str_contains($timezone, 'Europe/Brussels')) {
            return 'Salut ! Une fois qu\'on commence, on ne s\'arrête plus !';
        }
        if (str_contains($timezone, 'Africa/Tunis') || str_contains($timezone, 'Africa/Algiers') || str_contains($timezone, 'Africa/Casablanca')) {
            return 'Ahlan ! Montre-moi ce que tu sais faire !';
        }
        if (str_contains($timezone, 'Europe/Paris')) {
            return 'Coucou ! Prêt à jouer avec moi ?';
        }

        return 'Salut ! Prêt à jouer ?'; // Par défaut
    }
}
