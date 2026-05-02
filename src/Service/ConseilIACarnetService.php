<?php

namespace App\Service;

use App\Entity\CarnetEducatif;

class ConseilIACarnetService
{
    public function analyserCarnetEtGenererConseil(CarnetEducatif $carnet): string
    {
        // Extraire les données du carnet avec vérifications
        $donnees = $this->extraireDonnees($carnet);
        
        // Générer le conseil
        $conseil = $this->genererConseilLocal($donnees);
        
        return $conseil;
    }

    private function extraireDonnees(CarnetEducatif $carnet): array
    {
        $donnees = [
            'description' => '',
            'emotions' => [],
            'objectifs' => [],
            'difficultes' => '',
            'points_positifs' => '',
            'matiere' => '',
            'niveau_concentration' => 0
        ];
        
        // Récupérer la description (méthode existante)
        if (method_exists($carnet, 'getDescription')) {
            $donnees['description'] = $carnet->getDescription() ?? '';
        }
        
        // Récupérer les difficultés
        if (method_exists($carnet, 'getDifficultes')) {
            $donnees['difficultes'] = $carnet->getDifficultes() ?? '';
        }
        
        // Récupérer les points positifs
        if (method_exists($carnet, 'getPointsPositifs')) {
            $donnees['points_positifs'] = $carnet->getPointsPositifs() ?? '';
        }
        
        // Récupérer la matière
        if (method_exists($carnet, 'getMatiere')) {
            $donnees['matiere'] = $carnet->getMatiere() ?? '';
        }
        
        // Récupérer le niveau de concentration
        if (method_exists($carnet, 'getNiveauConcentration')) {
            $donnees['niveau_concentration'] = $carnet->getNiveauConcentration() ?? 0;
        }
        
        // Récupérer les émotions si la méthode existe
        if (method_exists($carnet, 'getEmotions')) {
            foreach ($carnet->getEmotions() as $emotion) {
                if (method_exists($emotion, 'getType')) {
                    $donnees['emotions'][] = [
                        'type' => $emotion->getType(),
                        'note' => method_exists($emotion, 'getNote') ? $emotion->getNote() : 3,
                        'commentaire' => method_exists($emotion, 'getCommentaire') ? $emotion->getCommentaire() : ''
                    ];
                }
            }
        }
        
        // Récupérer les objectifs si la méthode existe
        if (method_exists($carnet, 'getObjectifs')) {
            foreach ($carnet->getObjectifs() as $objectif) {
                if (method_exists($objectif, 'getDescription')) {
                    $donnees['objectifs'][] = [
                        'description' => $objectif->getDescription(),
                        'statut' => method_exists($objectif, 'getStatut') ? $objectif->getStatut() : 'EN_COURS',
                        'progression' => method_exists($objectif, 'getProgression') ? $objectif->getProgression() : 0
                    ];
                }
            }
        }
        
        return $donnees;
    }

    private function genererConseilLocal(array $donnees): string
    {
        $conseil = [];
        
        // Texte complet à analyser
        $texteComplet = strtolower(
            $donnees['description'] . ' ' . 
            $donnees['difficultes'] . ' ' . 
            $donnees['points_positifs']
        );
        
        // --- SECTION 1 : Analyse des points positifs ---
        if (!empty($donnees['points_positifs']) && $donnees['points_positifs'] != '—') {
            $conseil[] = "✨ **Ce qui fonctionne bien :**";
            $conseil[] = "• " . $donnees['points_positifs'];
            $conseil[] = "• Continuez à valoriser ces réussites !";
            $conseil[] = "";
        }
        
        // --- SECTION 2 : Détection des problèmes ---
        $problemesTrouves = false;
        
        // Détection des problèmes de fatigue
        if (strpos($texteComplet, 'fatigue') !== false || strpos($texteComplet, 'fatigué') !== false || strpos($texteComplet, 'dort') !== false) {
            if (!$problemesTrouves) {
                $conseil[] = "💡 **Points d'attention détectés :**";
                $problemesTrouves = true;
            }
            $conseil[] = "😴 **Fatigue :** Établissez un rituel du coucher régulier et limitez les écrans 1h avant le dodo.";
        }
        
        // Détection des problèmes de concentration
        if ($donnees['niveau_concentration'] < 3 || strpos($texteComplet, 'concentr') !== false) {
            if (!$problemesTrouves) {
                $conseil[] = "💡 **Points d'attention détectés :**";
                $problemesTrouves = true;
            }
            $conseil[] = "🎯 **Concentration :** Alternez 15min de travail et 5min de pause. Utilisez un minuteur visuel.";
        }
        
        // Détection des problèmes de colère/comportement
        if (strpos($texteComplet, 'colère') !== false || strpos($texteComplet, 'crise') !== false || strpos($texteComplet, 'énerv') !== false) {
            if (!$problemesTrouves) {
                $conseil[] = "💡 **Points d'attention détectés :**";
                $problemesTrouves = true;
            }
            $conseil[] = "🌊 **Gestion des émotions :** Validez l'émotion avant de chercher la solution. Proposez un 'coin calme'.";
        }
        
        // Détection des difficultés scolaires
        if (strpos($texteComplet, 'école') !== false || strpos($texteComplet, 'devoirs') !== false || strpos($texteComplet, 'difficile') !== false) {
            if (!$problemesTrouves) {
                $conseil[] = "💡 **Points d'attention détectés :**";
                $problemesTrouves = true;
            }
            $conseil[] = "📚 **Scolarité :** Divisez les devoirs en petites étapes. Félicitez l'effort, pas seulement le résultat.";
        }
        
        if ($problemesTrouves) {
            $conseil[] = "";
        }
        
        // --- SECTION 3 : Analyse des difficultés spécifiques ---
        if (!empty($donnees['difficultes']) && $donnees['difficultes'] != '—') {
            $conseil[] = "⚠️ **Difficultés rencontrées :**";
            $conseil[] = "• " . $donnees['difficultes'];
            $conseil[] = "";
        }
        
        // --- SECTION 4 : Analyse des objectifs (si disponibles) ---
        $objectifsReussis = 0;
        $objectifsEnCours = 0;
        $progressionTotale = 0;
        
        foreach ($donnees['objectifs'] as $obj) {
            if ($obj['statut'] === 'ATTEINT') {
                $objectifsReussis++;
            } elseif ($obj['statut'] === 'EN_COURS') {
                $objectifsEnCours++;
                $progressionTotale += $obj['progression'];
            }
        }
        
        if ($objectifsReussis > 0) {
            $conseil[] = "🏆 **Objectifs atteints :** $objectifsReussis objectif(s) réussi(s) récemment ! Continuez sur cette lancée !";
            $conseil[] = "";
        }
        
        if ($objectifsEnCours > 0) {
            $moyenne = round($progressionTotale / $objectifsEnCours);
            if ($moyenne < 30) {
                $conseil[] = "📊 **Objectifs en cours :** Progression moyenne de {$moyenne}%. Divisez les objectifs en sous-objectifs plus petits.";
                $conseil[] = "";
            } elseif ($moyenne > 70) {
                $conseil[] = "🎯 **Objectifs en cours :** Progression de {$moyenne}% - vous y êtes presque ! Un dernier effort !";
                $conseil[] = "";
            }
        }
        
        // --- SECTION 5 : Action du jour (basée sur les données) ---
        $actions = $this->getActionsPersonnalisees($donnees);
        $index = abs(crc32($donnees['description'] . date('Y-m-d'))) % count($actions);
        
        $conseil[] = "---";
        $conseil[] = "✨ **Action du jour :**";
        $conseil[] = $actions[$index];
        $conseil[] = "";
        
        // --- SECTION 6 : Message de soutien personnalisé ---
        $soutiens = [
            "👨‍👩‍👦 Vous faites un travail formidable, chaque petit pas compte !",
            "💖 Votre présence aimante est ce dont votre enfant a le plus besoin.",
            "🌟 Chaque jour est une nouvelle opportunité d'apprendre et de grandir.",
            "🌱 La patience et la bienveillance sont vos meilleurs alliés.",
            "⭐ Célébrez les petites victoires, elles mènent aux grandes réussites !"
        ];
        
        $index2 = abs(crc32($donnees['description'] . 'soutien')) % count($soutiens);
        $conseil[] = $soutiens[$index2];
        
        return implode("\n", $conseil);
    }
    
    private function getActionsPersonnalisees(array $donnees): array
    {
        $actions = [
            "📝 Notez une chose positive que votre enfant a bien faite aujourd'hui.",
            "🎨 Faites un dessin des émotions ensemble pour mieux les exprimer.",
            "💬 Demandez à votre enfant : 'Qu'est-ce qui t'a rendu fier aujourd'hui ?'",
            "🏃 Proposez 10 minutes d'activité physique avant les devoirs.",
            "📖 Lisez une histoire calme ensemble le soir.",
            "🎵 Écoutez une musique apaisante et respirez profondément.",
            "🫂 Accordez un moment de câlin et de parole en fin de journée.",
            "🌟 Félicitez votre enfant pour 3 choses qu'il a bien faites.",
            "⏰ Mettez en place un minuteur pour structurer le temps de travail.",
            "😴 Vérifiez et notez le temps de sommeil de votre enfant cette nuit."
        ];
        
        // Personnaliser selon la matière
        if (!empty($donnees['matiere'])) {
            if ($donnees['matiere'] == 'Français') {
                $actions[] = "📚 Lisez un petit texte avec votre enfant et posez-lui 3 questions de compréhension.";
            } elseif ($donnees['matiere'] == 'Mathématiques') {
                $actions[] = "🧮 Faites une petite série de calculs ludiques (courses, jeux de société).";
            }
        }
        
        // Personnaliser selon la concentration
        if ($donnees['niveau_concentration'] < 3) {
            $actions[] = "🍅 Testez la méthode Pomodoro : 15min travail, 5min pause.";
        }
        
        return $actions;
    }
}