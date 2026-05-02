<?php
// src/Service/QuestionGeneratorIA.php

namespace App\Service;

/**
 * Classe génératrice d'exercices pédagogiques intelligents
 * 
 * @author FocusKids Team
 * @version 2.0
 * 
 * Cette classe permet de générer automatiquement des exercices
 * de différents types (Mémoire, Attention, Logique, Chrono)
 * avec gestion de la difficulté et contenu varié.
 */
class QuestionGeneratorIA
{
    /**
     * @var array Liste complète des émojis pour les exercices visuels
     */
    private array $emojis = [
        // Animaux
        "🐶", "🐱", "🐭", "🐹", "🐰", "🦊", "🐻", "🐼", "🐨", "🐸",
        // Fruits et légumes
        "🍎", "🍏", "🍊", "🍋", "🍌", "🍉", "🍇", "🍓", "🥝", "🥥",
        // Couleurs et formes
        "🔴", "🔵", "🟢", "🟡", "🟠", "🟣", "🔶", "🔷", "⭐", "🌟",
        // Objets quotidiens
        "🚗", "🚲", "✈️", "🚀", "📚", "✏️", "📱", "💻", "🎮", "🎵",
        // Nourriture
        "🍕", "🍔", "🍟", "🍦", "🍩", "☕", "🍵", "🥤", "🍿", "🍪"
    ];

    /**
     * @var array Thèmes pour les jeux de mémoire
     */
    private array $themesMemoire = [
        "animaux", "fruits", "légumes", "formes géométriques", "couleurs", 
        "véhicules", "instruments de musique", "métiers", "sports", 
        "vêtements", "objets de la maison", "fournitures scolaires",
        "éléments de la nature", "planètes", "créatures marines",
        "oiseaux", "insectes", "pays du monde", "capitales", "inventions"
    ];

    /**
     * @var array Banque de questions de culture générale par niveau
     */
    private array $questionsCulture = [
        'niveau1' => [
            ['question' => 'Quelle est la couleur du ciel par temps clair ?', 'reponse' => 'Bleu'],
            ['question' => 'Combien de pattes possède un chien ?', 'reponse' => '4'],
            ['question' => 'Quel animal domestique fait "Miaou" ?', 'reponse' => 'Chat'],
            ['question' => 'Quel est le contraire de "grand" ?', 'reponse' => 'Petit'],
            ['question' => 'Combien de doigts a une main humaine ?', 'reponse' => '5']
        ],
        'niveau2' => [
            ['question' => 'Quelle est la capitale de la France ?', 'reponse' => 'Paris'],
            ['question' => 'Combien de jours y a-t-il dans une semaine ?', 'reponse' => '7'],
            ['question' => 'Quel est le plus grand animal terrestre ?', 'reponse' => "Éléphant"],
            ['question' => 'Quel est le premier mois de l\'année ?', 'reponse' => 'Janvier'],
            ['question' => 'Combien de minutes dans une heure ?', 'reponse' => '60']
        ],
        'niveau3' => [
            ['question' => 'Quel est le plus grand océan du monde ?', 'reponse' => 'Pacifique'],
            ['question' => 'Combien de continents existe-t-il ?', 'reponse' => '7'],
            ['question' => 'Qui a peint la célèbre Joconde ?', 'reponse' => 'Leonard de Vinci'],
            ['question' => 'Quel est le symbole chimique de l\'eau ?', 'reponse' => 'H2O'],
            ['question' => 'En quelle année a eu lieu la Révolution française ?', 'reponse' => '1789']
        ],
        'niveau4' => [
            ['question' => 'Quel est le plus long fleuve d\'Europe ?', 'reponse' => 'Volga'],
            ['question' => 'Qui a écrit le roman "Les Misérables" ?', 'reponse' => 'Victor Hugo'],
            ['question' => 'En quelle année le mur de Berlin est-il tombé ?', 'reponse' => '1989'],
            ['question' => 'Quel est le pays le plus peuplé du monde ?', 'reponse' => 'Chine'],
            ['question' => 'Quelle est la devise de l\'Union Européenne ?', 'reponse' => 'Unie dans la diversité']
        ],
        'niveau5' => [
            ['question' => 'Quel est le plus haut sommet d\'Afrique ?', 'reponse' => 'Kilimandjaro'],
            ['question' => 'Qui a découvert la pénicilline ?', 'reponse' => 'Alexander Fleming'],
            ['question' => 'En quelle année la Première Guerre mondiale a-t-elle commencé ?', 'reponse' => '1914'],
            ['question' => 'Quel prix Nobel de la paix a été décerné en 2020 ?', 'reponse' => 'Programme alimentaire mondial'],
            ['question' => 'Qui a écrit "Le Petit Prince" ?', 'reponse' => 'Antoine de Saint-Exupéry']
        ]
    ];

    /**
     * Point d'entrée principal pour la génération d'exercice
     *
     * @param string $titre Titre de l'exercice
     * @param string $type Type d'exercice (MÉMOIRE, ATTENTION, LOGIQUE, CHRONO)
     * @param int $difficulte Niveau de difficulté (1: très facile à 5: très difficile)
     * @return array Configuration complète de l'exercice
     */
    public function genererExercice(string $titre, string $type, int $difficulte = 3): array
    {
        // Validation des paramètres
        $difficulte = max(1, min(5, $difficulte));
        
        // Configuration de base selon la difficulté
        $config = $this->getBaseConfig($difficulte);
        
        switch ($type) {
            case 'MÉMOIRE':
                return $this->genererMemoire($difficulte, $config);
            case 'ATTENTION':
                return $this->genererAttention($difficulte, $config);
            case 'LOGIQUE':
                return $this->genererLogique($difficulte, $config);
            case 'CHRONO':
                return $this->genererChrono($difficulte, $config);
            default:
                return [
                    'error' => 'Type d\'exercice non reconnu',
                    'types_disponibles' => ['MÉMOIRE', 'ATTENTION', 'LOGIQUE', 'CHRONO']
                ];
        }
    }

    /**
     * Configuration de base selon le niveau de difficulté
     *
     * @param int $difficulte Niveau 1-5
     * @return array Configuration
     */
    private function getBaseConfig(int $difficulte): array
    {
        // Nombre de questions par niveau
        $nbQuestions = match($difficulte) {
            1 => 5,   // Très facile
            2 => 7,   // Facile
            3 => 10,  // Moyen
            4 => 12,  // Difficile
            5 => 15   // Très difficile
        };
        
        // Temps par question (en secondes)
        $tempsParQuestion = match($difficulte) {
            1 => 60,
            2 => 50,
            3 => 40,
            4 => 30,
            5 => 20
        };
        
        // Points par question
        $pointsBase = match($difficulte) {
            1 => 10,
            2 => 15,
            3 => 20,
            4 => 30,
            5 => 50
        };
        
        // Seuil maximal des nombres
        $maxNombre = match($difficulte) {
            1 => 10,
            2 => 30,
            3 => 100,
            4 => 200,
            5 => 500
        };
        
        return [
            'nb_questions' => $nbQuestions,
            'temps_question' => $tempsParQuestion,
            'points_base' => $pointsBase,
            'max_nombre' => $maxNombre,
            'difficulte' => $difficulte
        ];
    }

    /**
     * Génère un exercice de mémoire (jeu de paires)
     *
     * @param int $difficulte Niveau de difficulté
     * @param array $config Configuration de base
     * @return array Configuration du jeu de mémoire
     */
    private function genererMemoire(int $difficulte, array $config): array
    {
        // Taille de la grille selon difficulté
        $tailles = [
            1 => '4x4',   // 8 paires
            2 => '4x4',   // 8 paires
            3 => '4x4',   // 8 paires
            4 => '6x6',   // 18 paires
            5 => '6x6'    // 18 paires
        ];
        
        // Temps d'affichage des cartes
        $tempsAffichage = [
            1 => 5, 2 => 4, 3 => 3, 4 => 2, 5 => 2
        ];
        
        // Points par paire
        $pointsParPaire = [
            1 => 10, 2 => 15, 3 => 20, 4 => 35, 5 => 50
        ];
        
        return [
            'type' => 'MEMOIRE',
            'theme' => $this->themesMemoire[array_rand($this->themesMemoire)],
            'taille_grille' => $tailles[$difficulte],
            'nombre_paires' => $tailles[$difficulte] === '4x4' ? 8 : 18,
            'temps_affichage' => $tempsAffichage[$difficulte],
            'points_par_paire' => $pointsParPaire[$difficulte],
            'difficulte_niveau' => $difficulte,
            'difficulte_libelle' => $this->getLibelleDifficulte($difficulte)
        ];
    }

    /**
     * Génère un exercice d'attention (trouver l'intrus)
     *
     * @param int $difficulte Niveau de difficulté
     * @param array $config Configuration de base
     * @return array Configuration de l'exercice d'attention
     */
    private function genererAttention(int $difficulte, array $config): array
    {
        $questions = [];
        $nbQuestions = $config['nb_questions'];
        
        // Types d'exercices d'attention
        $sousTypes = [
            "Trouver l'intrus", "Cherchez l'élément différent", 
            "Lequel est différent ?", "Trouvez l'intrus dans la liste",
            "Identifiez l'élément qui ne correspond pas"
        ];
        
        for ($i = 0; $i < $nbQuestions; $i++) {
            // Sélection aléatoire de l'intrus
            $intrus = $this->emojis[array_rand($this->emojis)];
            
            do {
                $normal = $this->emojis[array_rand($this->emojis)];
            } while ($normal === $intrus);
            
            // Taille de la liste selon difficulté
            $tailleMin = 4 + ($difficulte - 1);
            $tailleMax = 8 + ($difficulte - 1) * 2;
            $taille = rand($tailleMin, min($tailleMax, 15));
            
            // Nombre d'intrus (1 pour facile, 2 pour difficile)
            $nbIntrus = $difficulte >= 4 ? rand(1, 2) : 1;
            
            $images = [];
            $positionsIntrus = [];
            
            // Sélectionner les positions des intrus
            while (count($positionsIntrus) < $nbIntrus) {
                $pos = rand(0, $taille - 1);
                if (!in_array($pos, $positionsIntrus)) {
                    $positionsIntrus[] = $pos;
                }
            }
            
            // Construire la liste
            for ($j = 0; $j < $taille; $j++) {
                if (in_array($j, $positionsIntrus)) {
                    $images[] = $intrus;
                } else {
                    $images[] = $normal;
                }
            }
            
            // Mélanger pour plus de difficulté
            if ($difficulte >= 3) {
                shuffle($images);
            }
            
            $questions[] = [
                'images' => implode(',', $images),
                'intrus' => $intrus,
                'position' => $positionsIntrus,
                'nb_intrus' => $nbIntrus
            ];
        }
        
        return [
            'type' => 'ATTENTION',
            'sous_type' => $sousTypes[array_rand($sousTypes)],
            'questions' => $questions,
            'temps_par_question' => $config['temps_question'],
            'difficulte_niveau' => $difficulte,
            'difficulte_libelle' => $this->getLibelleDifficulte($difficulte)
        ];
    }

    /**
     * Génère un exercice de logique (suites numériques)
     *
     * @param int $difficulte Niveau de difficulté
     * @param array $config Configuration de base
     * @return array Configuration de l'exercice logique
     */
    private function genererLogique(int $difficulte, array $config): array
    {
        $sequences = [];
        $nbQuestions = $config['nb_questions'];
        
        // Types d'opérations disponibles par niveau
        $operationsDisponibles = match($difficulte) {
            1 => ['addition'],
            2 => ['addition', 'soustraction'],
            3 => ['addition', 'soustraction', 'multiplication'],
            4 => ['addition', 'soustraction', 'multiplication', 'division'],
            5 => ['addition', 'soustraction', 'multiplication', 'division', 'alternance', 'fibonacci']
        };
        
        for ($i = 0; $i < $nbQuestions; $i++) {
            $operation = $operationsDisponibles[array_rand($operationsDisponibles)];
            $longueur = 4 + ($difficulte - 1);
            $serie = $this->genererSuite($operation, $config['max_nombre'], $longueur);
            
            $sequences[] = [
                'serie' => $serie['texte'],
                'reponse' => (string)$serie['suivant'],
                'regle' => $this->getRegleOperation($operation, $serie['parametres']),
                'operation' => $operation
            ];
        }
        
        return [
            'type' => 'LOGIQUE',
            'sous_type' => 'Suite numérique',
            'sequences' => $sequences,
            'difficulte_niveau' => $difficulte,
            'difficulte_libelle' => $this->getLibelleDifficulte($difficulte)
        ];
    }

    /**
     * Génère une suite numérique selon l'opération
     *
     * @param string $operation Type d'opération
     * @param int $maxNombre Valeur maximale
     * @param int $longueur Longueur de la suite
     * @return array Suite générée
     */
    private function genererSuite(string $operation, int $maxNombre, int $longueur): array
    {
        $serie = [];
        $parametres = [];
        
        switch ($operation) {
            case 'addition':
                $start = rand(1, $maxNombre / 2);
                $step = rand(1, min(10, $maxNombre / 5));
                $parametres = ['start' => $start, 'step' => $step];
                for ($j = 0; $j < $longueur; $j++) {
                    $serie[] = $start + ($j * $step);
                }
                $suivant = $start + ($longueur * $step);
                break;
                
            case 'soustraction':
                $start = rand($maxNombre / 2, $maxNombre);
                $step = rand(1, min(10, $maxNombre / 5));
                $parametres = ['start' => $start, 'step' => $step];
                for ($j = 0; $j < $longueur; $j++) {
                    $serie[] = $start - ($j * $step);
                }
                $suivant = $start - ($longueur * $step);
                break;
                
            case 'multiplication':
                $start = rand(1, 10);
                $step = rand(2, 5);
                $parametres = ['start' => $start, 'step' => $step];
                for ($j = 0; $j < $longueur; $j++) {
                    $serie[] = $start * pow($step, $j);
                }
                $suivant = $start * pow($step, $longueur);
                break;
                
            case 'division':
                $step = rand(2, 4);
                $start = $step * pow(2, $longueur);
                $parametres = ['start' => $start, 'step' => $step];
                for ($j = 0; $j < $longueur; $j++) {
                    $serie[] = $start / pow($step, $j);
                }
                $suivant = $start / pow($step, $longueur);
                break;
                
            case 'alternance':
                $step1 = rand(1, 5);
                $step2 = rand(1, 5);
                $start = rand(1, 20);
                $parametres = ['start' => $start, 'step1' => $step1, 'step2' => $step2];
                for ($j = 0; $j < $longueur; $j++) {
                    if ($j % 2 == 0) {
                        $serie[] = $start + ($j/2) * $step1;
                    } else {
                        $serie[] = $start + (($j-1)/2) * $step2;
                    }
                }
                $suivant = ($longueur % 2 == 0) 
                    ? $start + (($longueur/2) - 1) * $step2
                    : $start + (($longueur-1)/2) * $step1;
                break;
                
            case 'fibonacci':
                $a = rand(1, 5);
                $b = rand(2, 10);
                $parametres = ['a' => $a, 'b' => $b];
                $serie = [$a, $b];
                for ($j = 2; $j < $longueur; $j++) {
                    $serie[] = $serie[$j-1] + $serie[$j-2];
                }
                $suivant = $serie[$longueur-1] + $serie[$longueur-2];
                break;
                
            default:
                $start = rand(1, 20);
                $step = rand(1, 5);
                for ($j = 0; $j < $longueur; $j++) {
                    $serie[] = $start + ($j * $step);
                }
                $suivant = $start + ($longueur * $step);
        }
        
        return [
            'texte' => implode(', ', $serie) . ', ?',
            'suivant' => $suivant,
            'parametres' => $parametres
        ];
    }

    /**
     * Génère les questions pour le jeu Chrono
     *
     * @param int $difficulte Niveau de difficulté
     * @param array $config Configuration de base
     * @return array Configuration du jeu Chrono
     */
    private function genererChrono(int $difficulte, array $config): array
    {
        $defis = [];
        $nbQuestions = $config['nb_questions'];
        $maxNombre = $config['max_nombre'];
        $points = $config['points_base'];
        
        // Types de questions disponibles
        $typesQuestions = match($difficulte) {
            1 => ['addition'],
            2 => ['addition', 'soustraction'],
            3 => ['addition', 'soustraction', 'multiplication'],
            4 => ['addition', 'soustraction', 'multiplication', 'division'],
            5 => ['addition', 'soustraction', 'multiplication', 'division', 'expression', 'culture']
        };
        
        for ($i = 0; $i < $nbQuestions; $i++) {
            $type = $typesQuestions[array_rand($typesQuestions)];
            $pointsBonus = $difficulte >= 3 ? ($difficulte - 2) * 5 : 0;
            
            switch ($type) {
                case 'addition':
                    $a = rand(1, $maxNombre);
                    $b = rand(1, $maxNombre);
                    $defis[] = [
                        'question' => "$a + $b = ?",
                        'reponse' => (string)($a + $b),
                        'points' => $points,
                        'type' => 'addition'
                    ];
                    break;
                    
                case 'soustraction':
                    $a = rand($maxNombre / 2, $maxNombre);
                    $b = rand(1, $a);
                    $defis[] = [
                        'question' => "$a - $b = ?",
                        'reponse' => (string)($a - $b),
                        'points' => $points,
                        'type' => 'soustraction'
                    ];
                    break;
                    
                case 'multiplication':
                    $a = rand(2, min(12, $maxNombre / 5));
                    $b = rand(2, min(12, $maxNombre / 5));
                    $defis[] = [
                        'question' => "$a × $b = ?",
                        'reponse' => (string)($a * $b),
                        'points' => $points + 5,
                        'type' => 'multiplication'
                    ];
                    break;
                    
                case 'division':
                    $b = rand(2, min(12, $maxNombre / 10));
                    $resultat = rand(2, 12);
                    $a = $b * $resultat;
                    $defis[] = [
                        'question' => "$a ÷ $b = ?",
                        'reponse' => (string)$resultat,
                        'points' => $points + 10,
                        'type' => 'division'
                    ];
                    break;
                    
                case 'expression':
                    $a = rand(2, 15);
                    $b = rand(2, 10);
                    $c = rand(2, 8);
                    $defis[] = [
                        'question' => "$a + $b × $c = ?",
                        'reponse' => (string)($a + ($b * $c)),
                        'points' => $points + 15,
                        'type' => 'expression'
                    ];
                    break;
                    
                case 'culture':
                    $niveau = min($difficulte, 5);
                    $questions = $this->questionsCulture["niveau$niveau"];
                    $cg = $questions[array_rand($questions)];
                    $defis[] = [
                        'question' => $cg['question'],
                        'reponse' => $cg['reponse'],
                        'points' => $points + 10,
                        'type' => 'culture'
                    ];
                    break;
            }
        }
        
        // Mélanger les défis pour plus de variété
        shuffle($defis);
        
        // Calcul de la durée totale
        $dureeTotale = $nbQuestions * $config['temps_question'];
        
        return [
            'type' => 'CHRONO',
            'duree_totale' => $dureeTotale,
            'defis' => $defis,
            'nombre_questions' => $nbQuestions,
            'temps_par_question' => $config['temps_question'],
            'difficulte_niveau' => $difficulte,
            'difficulte_libelle' => $this->getLibelleDifficulte($difficulte)
        ];
    }

    /**
     * Retourne le libellé textuel de la difficulté
     *
     * @param int $difficulte Niveau 1-5
     * @return string Libellé
     */
    private function getLibelleDifficulte(int $difficulte): string
    {
        return match($difficulte) {
            1 => 'Très facile',
            2 => 'Facile',
            3 => 'Moyen',
            4 => 'Difficile',
            5 => 'Très difficile',
            default => 'Inconnu'
        };
    }

    /**
     * Retourne la règle textuelle d'une opération
     *
     * @param string $operation Type d'opération
     * @param array $params Paramètres de l'opération
     * @return string Règle en français
     */
    private function getRegleOperation(string $operation, array $params): string
    {
        return match($operation) {
            'addition' => "Ajouter {$params['step']} à chaque terme",
            'soustraction' => "Soustraire {$params['step']} à chaque terme",
            'multiplication' => "Multiplier par {$params['step']} à chaque fois",
            'division' => "Diviser par {$params['step']} à chaque fois",
            'alternance' => "Alterner entre +{$params['step1']} et +{$params['step2']}",
            'fibonacci' => "Chaque terme est la somme des deux précédents",
            default => "Suite logique"
        };
    }
}