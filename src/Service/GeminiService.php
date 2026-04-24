<?php
// src/Service/GeminiService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeminiService
{
    private $httpClient;
    private $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $apiKey = null)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey ?? $_ENV['GEMINI_API_KEY'] ?? '';
    }

    public function generateContent(string $prompt): ?string
    {
        if (empty($this->apiKey)) {
            return null;
        }

        try {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $this->apiKey;
            
            $response = $this->httpClient->request('POST', $url, [
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 1000,
                    ]
                ],
                'timeout' => 30,
            ]);

            $data = json_decode($response->getContent(), true);
            
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return $data['candidates'][0]['content']['parts'][0]['text'];
            }
            
            return null;
            
        } catch (\Exception $e) {
            error_log('Gemini API Error: ' . $e->getMessage());
            return null;
        }
    }

    public function generatePedagogicalAdvice(string $enfantNom, array $stats): string
    {
        $prompt = "Tu es un expert en pédagogie pour enfants. Analyse les performances de l'enfant '{$enfantNom}' et donne des conseils personnalisés.

STATISTIQUES:
- Exercices effectués: {$stats['total']}
- Taux de réussite: {$stats['taux_reussite']}%
- Score moyen: {$stats['score_moyen']}/100
- Temps moyen: {$stats['temps_moyen']} secondes
- Meilleur score: {$stats['meilleur_score']}
- Progression: {$stats['progression']} points
- Tendance: {$stats['tendance']}

RÉPONDS EXACTEMENT AVEC CETTE STRUCTURE (sans ajouter de texte avant ou après):

### 📊 RÉSUMÉ
[Rédige 2-3 phrases d'analyse objective des performances de l'enfant]

### 💪 POINTS FORTS
- [Point fort spécifique 1 avec explication concise]
- [Point fort spécifique 2 avec explication concise]

### 🎯 AXES D'AMÉLIORATION
- [Axe d'amélioration 1 avec conseil concret et actionnable]
- [Axe d'amélioration 2 avec conseil concret et actionnable]

### 📝 RECOMMANDATIONS
1. [Recommandation actionnable et précise]
2. [Recommandation actionnable et précise]
3. [Recommandation actionnable et précise]

### 🎉 MOTIVATION
[Une phrase d'encouragement personnalisée, chaleureuse et bienveillante]

IMPORTANT: 
- Utilise du texte clair, pas de markdown complexe
- Sois bienveillant et concret
- Adapte ton langage à des parents ou enseignants
- Ne mets pas de texte avant la première section ###";

        $advice = $this->generateContent($prompt);
        
        if (!$advice || trim($advice) === '') {
            return $this->getFallbackAdvice($stats, $enfantNom);
        }
        
        return $advice;
    }

    private function getFallbackAdvice(array $stats, string $enfantNom): string
    {
        $advice = "### 📊 RÉSUMÉ\n\nL'enfant {$enfantNom} a effectué **{$stats['total']}** exercices avec un taux de réussite de **{$stats['taux_reussite']}%** et un score moyen de **{$stats['score_moyen']}/100**.\n\n";
        
        if ($stats['taux_reussite'] >= 70) {
            $advice .= "### 💪 POINTS FORTS\n- Excellente progression et maîtrise des exercices\n- Bonne autonomie dans l'apprentissage\n\n";
            $advice .= "### 🎯 AXES D'AMÉLIORATION\n- Continuer à diversifier les types d'exercices\n- Introduire plus de défis chronométrés\n\n";
            $advice .= "### 📝 RECOMMANDATIONS\n1. Augmenter progressivement la difficulté des exercices\n2. Proposer des exercices plus complexes en logique\n3. Féliciter chaque réussite pour maintenir la motivation\n\n";
            $advice .= "### 🎉 MOTIVATION\nFélicitations ! Continue sur cette belle lancée, tu fais de super progrès ! 🌟";
        } elseif ($stats['taux_reussite'] >= 40) {
            $advice .= "### 💪 POINTS FORTS\n- Progression régulière malgré quelques difficultés\n- Bonne implication et persévérance\n\n";
            $advice .= "### 🎯 AXES D'AMÉLIORATION\n- Travailler la concentration sur des exercices plus longs\n- Revoir les bases sur certains types d'exercices\n\n";
            $advice .= "### 📝 RECOMMANDATIONS\n1. Faire des pauses de 5 minutes entre les exercices\n2. Varier les types d'exercices pour maintenir l'intérêt\n3. Récompenser chaque effort, pas seulement les réussites\n\n";
            $advice .= "### 🎉 MOTIVATION\nBravo ! Chaque exercice te fait progresser, continue comme ça ! 💪";
        } else {
            $advice .= "### 💪 POINTS FORTS\n- Persévérance et courage malgré les difficultés\n- L'enfant continue à s'entraîner régulièrement\n\n";
            $advice .= "### 🎯 AXES D'AMÉLIORATION\n- Revoir les notions de base en douceur\n- Travailler sur des exercices plus simples pour reconstruire la confiance\n\n";
            $advice .= "### 📝 RECOMMANDATIONS\n1. Proposer des exercices plus courts (5-10 minutes max)\n2. Multiplier les encouragements positifs\n3. Utiliser des récompenses visuelles (stickers, étoiles)\n\n";
            $advice .= "### 🎉 MOTIVATION\nCourage ! Chaque petit pas compte et tu vas y arriver petit à petit ! 🌈";
        }
        
        return $advice;
    }
}