<?php
// src/Service/QuestionGeneratorIA.php

namespace App\Service;

class QuestionGeneratorIA
{
    private array $emojis = [
        "🍎", "🍏", "🐶", "🐱", "🔴", "🔵", "⭐", "🌟", "🐭", "🐹",
        "🍕", "🍔", "🚗", "🚲", "📚", "✏️", "🌞", "🌙", "❤️", "💙",
        "🐧", "🐦", "🎈", "🎯", "⚽", "🏀", "🎵", "🎶", "📱", "💻",
        "☕", "🍵", "🐸", "🦋", "🌸", "🌺", "🍦", "🍩", "🎨", "🎭"
    ];

    private array $themesMemoire = [
        "animaux", "fruits", "légumes", "formes", "couleurs", "véhicules",
        "instruments", "métiers", "sports", "vêtements", "maison", "école",
        "nature", "espace", "océan", "forêt", "désert", "montagne", "ville", "campagne"
    ];

    public function genererExercice(string $titre, string $type, int $nbQuestions): array
    {
        switch ($type) {
            case 'MÉMOIRE':
                return $this->genererMemoire();
            case 'ATTENTION':
                return $this->genererAttention($nbQuestions);
            case 'LOGIQUE':
                return $this->genererLogique($nbQuestions);
            case 'CHRONO':
                return $this->genererChrono($nbQuestions);
            default:
                return [];
        }
    }

    private function genererMemoire(): array
    {
        return [
            'theme' => $this->themesMemoire[array_rand($this->themesMemoire)],
            'taille' => rand(0, 1) ? '4x4' : '6x6',
            'tempsAffichage' => rand(2, 9),
            'pointsParPaire' => rand(5, 50)
        ];
    }

    private function genererAttention(int $nbQuestions): array
    {
        $sousTypes = ["Trouver l'intrus", "Trouver les différences", "L'élément différent"];
        $questions = [];

        for ($i = 0; $i < $nbQuestions; $i++) {
            $intrus = $this->emojis[array_rand($this->emojis)];
            
            do {
                $normal = $this->emojis[array_rand($this->emojis)];
            } while ($normal === $intrus);
            
            $taille = rand(5, 20);
            $positionIntrus = rand(0, $taille - 1);
            
            $images = [];
            for ($j = 0; $j < $taille; $j++) {
                $images[] = ($j == $positionIntrus) ? $intrus : $normal;
            }
            
            $questions[] = [
                'images' => implode(',', $images),
                'intrus' => $intrus
            ];
        }

        return [
            'sousType' => $sousTypes[array_rand($sousTypes)],
            'questions' => $questions,
            'tempsParQuestion' => rand(5, 24)
        ];
    }

    private function genererLogique(int $nbQuestions): array
    {
        $sousTypes = ["Suite numérique", "Suite logique", "Suite arithmétique"];
        $sequences = [];

        for ($i = 0; $i < $nbQuestions; $i++) {
            $start = rand(1, 50);
            $step = rand(1, 10);
            $length = rand(4, 8);
            
            $serie = [];
            for ($j = 0; $j < $length; $j++) {
                $serie[] = $start + ($j * $step);
            }
            
            $sequences[] = [
                'debut' => implode(',', $serie) . ',?',
                'reponse' => (string)($start + ($length * $step)),
                'regle' => "Ajouter $step à chaque fois"
            ];
        }

        return [
            'sousType' => $sousTypes[array_rand($sousTypes)],
            'sequences' => $sequences
        ];
    }

    private function genererChrono(int $nbQuestions): array
    {
        $defis = [];

        for ($i = 0; $i < $nbQuestions; $i++) {
            $typeDefi = rand(0, 4);
            
            switch ($typeDefi) {
                case 0:
                    $a = rand(1, 50);
                    $b = rand(1, 50);
                    $defis[] = [
                        'question' => "$a + $b = ?",
                        'reponse' => (string)($a + $b),
                        'points' => rand(5, 20)
                    ];
                    break;
                case 1:
                    $a = rand(10, 100);
                    $b = rand(1, $a);
                    $defis[] = [
                        'question' => "$a - $b = ?",
                        'reponse' => (string)($a - $b),
                        'points' => rand(5, 20)
                    ];
                    break;
                case 2:
                    $a = rand(1, 12);
                    $b = rand(1, 12);
                    $defis[] = [
                        'question' => "$a × $b = ?",
                        'reponse' => (string)($a * $b),
                        'points' => rand(8, 25)
                    ];
                    break;
                case 3:
                    $questionsCG = [
                        ['question' => 'Quelle est la capitale de la France ?', 'reponse' => 'Paris'],
                        ['question' => 'Quel est le plus grand océan ?', 'reponse' => 'Pacifique'],
                        ['question' => 'Combien de continents y a-t-il ?', 'reponse' => '7'],
                        ['question' => 'Quel animal est le roi de la jungle ?', 'reponse' => 'Lion'],
                    ];
                    $cg = $questionsCG[array_rand($questionsCG)];
                    $defis[] = [
                        'question' => $cg['question'],
                        'reponse' => $cg['reponse'],
                        'points' => rand(10, 20)
                    ];
                    break;
                case 4:
                    $start = rand(1, 20);
                    $step = rand(1, 5);
                    $defis[] = [
                        'question' => "Complète: $start, " . ($start + $step) . ", " . ($start + 2*$step) . ", ?",
                        'reponse' => (string)($start + 3*$step),
                        'points' => rand(10, 20)
                    ];
                    break;
            }
        }

        return [
            'dureeTotale' => rand(30, 240),
            'defis' => $defis
        ];
    }
}