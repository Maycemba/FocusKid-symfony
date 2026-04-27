<?php
// src/Service/AdvancedIaRecommendationService.php

namespace App\Service;

use App\Repository\ReponseExerciceRepository;
use App\Repository\ExerciceRepository;
use App\Repository\UtilisateurRepository;
use App\Service\QrCodeService;

class AdvancedIaRecommendationService
{
    private $reponseRepo;
    private $exerciceRepo;
    private $userRepo;
    private $geminiService;

    private $qrCodeService; // Nouveau

    public function __construct(
        ReponseExerciceRepository $reponseRepo,
        ExerciceRepository $exerciceRepo,
        UtilisateurRepository $userRepo,
        GeminiService $geminiService,
        QrCodeService $qrCodeService // Ajouté

    ) {
        $this->reponseRepo = $reponseRepo;
        $this->exerciceRepo = $exerciceRepo;
        $this->userRepo = $userRepo;
        $this->geminiService = $geminiService;
        $this->qrCodeService = $qrCodeService; // Ajouté

    }

    public function generateFullReport(int $enfantId): array
    {
        $enfant = $this->userRepo->find($enfantId);
        $reponses = $this->reponseRepo->findBy(['enfant_id' => $enfantId]);
        $allExercices = $this->exerciceRepo->findAll();
        
        // 1. ANALYSE STATISTIQUE AVANCEE
        $stats = $this->calculateAdvancedStats($reponses, $allExercices);
        
        // 2. ANALYSE DES TENDANCES
        $trends = $this->analyzeTrends($reponses);
        
        // 3. DETECTION DES PATTERNS
        $patterns = $this->detectPatterns($reponses);
        
        // 4. PROFIL D'APPRENTISSAGE
        $learningProfile = $this->createLearningProfile($stats, $trends);
        
        // 5. PREDICTIONS
        $predictions = $this->makePredictions($stats, $trends);
        
        // 6. RECOMMANDATIONS PERSONNALISEES
        $recommendations = $this->generateDetailedRecommendations($stats, $patterns, $learningProfile);
        
        // 7. PLAN D'ACTION
        $actionPlan = $this->createActionPlan($recommendations, $learningProfile);
        
        // 8. CONSEILS GEMINI (AUTOMATIQUE)
        $geminiAdvice = $this->generateGeminiAdvice($enfant, $stats, $trends);
        
         // ========== GÉNÉRATION DU QR CODE ==========
            $qrCode = $this->qrCodeService->generateRapportQrCode($enfantId);

        return [
            'enfant' => [
                'nom' => $enfant->getUsername(),
                'age' => $this->estimateAge($enfant),
                'niveau' => $this->assessLevel($stats),
                'date_inscription' => method_exists($enfant, 'getDateCreation') && $enfant->getDateCreation() 
                    ? $enfant->getDateCreation()->format('d/m/Y') 
                    : 'Non disponible'
            ],
            'statistiques' => $stats,
            'tendances' => $trends,
            'patterns' => $patterns,
            'profil_apprentissage' => $learningProfile,
            'predictions' => $predictions,
            'recommandations' => $recommendations,
            'plan_action' => $actionPlan,
            'gemini_advice' => $geminiAdvice,
            'qr_code' => $qrCode, // QR code généré automatiquement
            'date_rapport' => (new \DateTime())->format('d/m/Y H:i:s')
        ];
    }
    
    private function generateGeminiAdvice($enfant, array $stats, array $trends): array
    {
        $statsForGemini = [
            'total' => $stats['total_exercices'],
            'taux_reussite' => $stats['taux_reussite_global'],
            'score_moyen' => $stats['score_moyen'],
            'temps_moyen' => $stats['temps_moyen'],
            'meilleur_score' => $stats['score_max'],
            'progression' => $stats['progression'],
            'tendance' => $trends['tendance'] ?? 'stable'
        ];
        
        $advice = $this->geminiService->generatePedagogicalAdvice($enfant->getUsername(), $statsForGemini);
        
        if (!$advice || $advice === 'null' || trim($advice) === '') {
            return [
                'success' => false,
                'content' => $this->getFallbackGeminiAdvice($stats, $enfant->getUsername()),
                'error' => true
            ];
        }
        
        return [
            'success' => true,
            'content' => $advice,
            'error' => false
        ];
    }
    
    private function getFallbackGeminiAdvice(array $stats, string $enfantNom): string
    {
        $advice = "### 📊 RÉSUMÉ\n\nL'enfant {$enfantNom} a effectué **{$stats['total_exercices']}** exercices avec un taux de réussite de **{$stats['taux_reussite_global']}%**.\n\n";
        
        if ($stats['taux_reussite_global'] >= 70) {
            $advice .= "### 💪 POINTS FORTS\n- Excellente progression\n- Bonne maîtrise des exercices\n\n";
            $advice .= "### 🎯 AXES D'AMÉLIORATION\n- Continuer à diversifier les exercices\n- Introduire plus de défis chronométrés\n\n";
            $advice .= "### 📝 RECOMMANDATIONS\n1. Augmenter progressivement la difficulté\n2. Proposer des exercices plus complexes\n3. Féliciter chaque réussite\n\n";
            $advice .= "### 🎉 MOTIVATION\nFélicitations ! Continue sur cette belle lancée !";
        } elseif ($stats['taux_reussite_global'] >= 40) {
            $advice .= "### 💪 POINTS FORTS\n- Progression régulière\n- Bonne implication\n\n";
            $advice .= "### 🎯 AXES D'AMÉLIORATION\n- Travailler la concentration\n- Revoir les bases sur certains types\n\n";
            $advice .= "### 📝 RECOMMANDATIONS\n1. Faire des pauses entre les exercices\n2. Varier les types d'exercices\n3. Récompenser chaque effort\n\n";
            $advice .= "### 🎉 MOTIVATION\nBravo ! Chaque exercice te fait progresser !";
        } else {
            $advice .= "### 💪 POINTS FORTS\n- Persévérance malgré les difficultés\n- L'enfant continue à s'entraîner\n\n";
            $advice .= "### 🎯 AXES D'AMÉLIORATION\n- Revoir les notions de base\n- Travailler sur des exercices plus simples\n\n";
            $advice .= "### 📝 RECOMMANDATIONS\n1. Proposer des exercices plus courts\n2. Multiplier les encouragements\n3. Utiliser des récompenses visuelles\n\n";
            $advice .= "### 🎉 MOTIVATION\nCourage ! Chaque petit pas compte !";
        }
        
        return $advice;
    }
    
    private function calculateAdvancedStats(array $reponses, array $allExercices): array
    {
        $total = count($reponses);
        $reussites = 0;
        $echecs = 0;
        $sumScores = 0;
        $sumTimes = 0;
        $detailsParType = [];
        $performanceParHeure = array_fill(0, 24, ['count' => 0, 'success' => 0]);
        $performanceParJour = array_fill(1, 7, ['count' => 0, 'success' => 0]);
        $evolutionHebdomadaire = [];
        $meilleurScore = 0;
        $pireScore = 100;
        
        foreach ($reponses as $r) {
            $sumScores += $r->getScore();
            $sumTimes += $r->getTempsPasse();
            if ($r->isReussite()) {
                $reussites++;
            } else {
                $echecs++;
            }
            
            if ($r->getScore() > $meilleurScore) $meilleurScore = $r->getScore();
            if ($r->getScore() < $pireScore) $pireScore = $r->getScore();
            
            $exercice = $this->exerciceRepo->find($r->getExerciceId());
            if ($exercice) {
                $type = $exercice->getType();
                if (!isset($detailsParType[$type])) {
                    $detailsParType[$type] = [
                        'total' => 0, 'reussites' => 0, 'scores' => [], 'temps' => []
                    ];
                }
                $detailsParType[$type]['total']++;
                $detailsParType[$type]['scores'][] = $r->getScore();
                $detailsParType[$type]['temps'][] = $r->getTempsPasse();
                if ($r->isReussite()) {
                    $detailsParType[$type]['reussites']++;
                }
            }
            
            $heure = (int)$r->getDatePassage()->format('H');
            $performanceParHeure[$heure]['count']++;
            if ($r->isReussite()) $performanceParHeure[$heure]['success']++;
            
            $jour = (int)$r->getDatePassage()->format('N');
            $performanceParJour[$jour]['count']++;
            if ($r->isReussite()) $performanceParJour[$jour]['success']++;
            
            $semaine = $r->getDatePassage()->format('Y-\WW');
            if (!isset($evolutionHebdomadaire[$semaine])) {
                $evolutionHebdomadaire[$semaine] = ['scores' => [], 'reussites' => 0, 'total' => 0];
            }
            $evolutionHebdomadaire[$semaine]['scores'][] = $r->getScore();
            $evolutionHebdomadaire[$semaine]['total']++;
            if ($r->isReussite()) $evolutionHebdomadaire[$semaine]['reussites']++;
        }
        
        foreach ($detailsParType as $type => $data) {
            $detailsParType[$type]['score_moyen'] = round(array_sum($data['scores']) / max(1, $data['total']), 1);
            $detailsParType[$type]['temps_moyen'] = round(array_sum($data['temps']) / max(1, $data['total']), 1);
            $detailsParType[$type]['taux_reussite'] = round(($data['reussites'] / max(1, $data['total'])) * 100, 1);
        }
        
        $meilleursHoraires = [];
        foreach ($performanceParHeure as $heure => $data) {
            if ($data['count'] > 0) {
                $taux = round(($data['success'] / $data['count']) * 100, 1);
                $meilleursHoraires[] = ['heure' => $heure, 'taux' => $taux, 'nb' => $data['count']];
            }
        }
        usort($meilleursHoraires, function($a, $b) { return $b['taux'] <=> $a['taux']; });
        
        $progression = 0;
        $progressionPourcentage = 0;
        if (count($reponses) >= 2) {
            $premiersScores = array_slice(array_reverse($reponses), 0, min(5, count($reponses)));
            $derniersScores = array_slice($reponses, 0, min(5, count($reponses)));
            $moyennePremier = array_sum(array_map(function($r) { return $r->getScore(); }, $premiersScores)) / count($premiersScores);
            $moyenneDernier = array_sum(array_map(function($r) { return $r->getScore(); }, $derniersScores)) / count($derniersScores);
            $progression = round($moyenneDernier - $moyennePremier, 1);
            $progressionPourcentage = $moyennePremier > 0 ? round(($progression / $moyennePremier) * 100, 1) : 0;
        }
        
        return [
            'total_exercices' => $total,
            'reussites' => $reussites,
            'echecs' => $echecs,
            'taux_reussite_global' => $total > 0 ? round(($reussites / $total) * 100, 1) : 0,
            'score_moyen' => $total > 0 ? round($sumScores / $total, 1) : 0,
            'temps_moyen' => $total > 0 ? round($sumTimes / $total, 1) : 0,
            'score_max' => $meilleurScore,
            'score_min' => $pireScore,
            'details_par_type' => $detailsParType,
            'meilleurs_horaires' => array_slice($meilleursHoraires, 0, 3),
            'meilleurs_jours' => $this->getBestDays($performanceParJour),
            'evolution_hebdomadaire' => $evolutionHebdomadaire,
            'progression' => $progression,
            'progression_pourcentage' => $progressionPourcentage
        ];
    }
    
    private function analyzeTrends(array $reponses): array
    {
        if (count($reponses) < 3) {
            return [
                'pente' => 0,
                'tendance' => 'stable',
                'message' => 'Pas assez de données pour analyser les tendances',
                'variance' => 0,
                'ecart_type' => 0,
                'consistance' => 'moyenne',
                'stabilité' => 'Performances variables'
            ];
        }
        
        $scores = array_map(function($r) { return $r->getScore(); }, $reponses);
        $n = count($scores);
        $x = range(1, $n);
        
        $sumX = array_sum($x);
        $sumY = array_sum($scores);
        $sumXY = array_sum(array_map(function($xi, $yi) { return $xi * $yi; }, $x, $scores));
        $sumX2 = array_sum(array_map(function($xi) { return $xi * $xi; }, $x));
        
        $pente = ($n * $sumXY - $sumX * $sumY) / (($n * $sumX2 - $sumX * $sumX) ?: 1);
        
        $moyenne = $sumY / $n;
        $variance = array_sum(array_map(function($y) use ($moyenne) { return pow($y - $moyenne, 2); }, $scores)) / $n;
        $ecartType = sqrt($variance);
        
        if ($pente > 5) {
            $tendance = 'forte_progression';
            $message = 'L\'enfant montre une progression très significative !';
        } elseif ($pente > 1) {
            $tendance = 'progression';
            $message = 'L\'enfant progresse régulièrement.';
        } elseif ($pente > -1) {
            $tendance = 'stable';
            $message = 'Les performances sont stables.';
        } elseif ($pente > -5) {
            $tendance = 'regression';
            $message = 'Attention, on observe une légère baisse des performances.';
        } else {
            $tendance = 'forte_regression';
            $message = 'Alerte : baisse significative des performances !';
        }
        
        $consistance = $ecartType < 15 ? 'elevée' : ($ecartType < 30 ? 'moyenne' : 'faible');
        
        return [
            'pente' => round($pente, 2),
            'tendance' => $tendance,
            'message' => $message,
            'variance' => round($variance, 1),
            'ecart_type' => round($ecartType, 1),
            'consistance' => $consistance,
            'stabilité' => $ecartType < 15 ? 'Performances très stables' : ($ecartType < 30 ? 'Performances variables' : 'Performances très irrégulières')
        ];
    }
    
    private function detectPatterns(array $reponses): array
    {
        $patterns = [];
        
        $maxReussiteSuite = 0;
        $maxEchecSuite = 0;
        $currentReussiteSuite = 0;
        $currentEchecSuite = 0;
        
        foreach ($reponses as $r) {
            if ($r->isReussite()) {
                $currentReussiteSuite++;
                $currentEchecSuite = 0;
            } else {
                $currentEchecSuite++;
                $currentReussiteSuite = 0;
            }
            $maxReussiteSuite = max($maxReussiteSuite, $currentReussiteSuite);
            $maxEchecSuite = max($maxEchecSuite, $currentEchecSuite);
        }
        
        if ($maxReussiteSuite >= 3) {
            $patterns[] = "✨ Séquences de réussite : $maxReussiteSuite exercices réussis à la suite !";
        }
        if ($maxEchecSuite >= 3) {
            $patterns[] = "⚠️ Séquences d'échec : $maxEchecSuite exercices échoués à la suite. Une pause serait bénéfique.";
        }
        
        $ameliorationParType = [];
        foreach ($this->exerciceRepo->findAll() as $exo) {
            $type = $exo->getType();
            $reponsesType = array_filter($reponses, function($r) use ($exo) {
                return $r->getExerciceId() == $exo->getId();
            });
            if (count($reponsesType) >= 2) {
                $premier = reset($reponsesType);
                $dernier = end($reponsesType);
                $amelioration = $dernier->getScore() - $premier->getScore();
                if ($amelioration > 20) {
                    $ameliorationParType[] = "📈 $type : Progression de $amelioration points !";
                } elseif ($amelioration < -20) {
                    $ameliorationParType[] = "📉 $type : Baisse de " . abs($amelioration) . " points à surveiller.";
                }
            }
        }
        $patterns = array_merge($patterns, $ameliorationParType);
        
        if (count($reponses) >= 5) {
            $premieresReponses = array_slice($reponses, 0, 3);
            $dernieresReponses = array_slice($reponses, -3);
            $scoreDebut = array_sum(array_map(function($r) { return $r->getScore(); }, $premieresReponses)) / 3;
            $scoreFin = array_sum(array_map(function($r) { return $r->getScore(); }, $dernieresReponses)) / 3;
            if ($scoreFin < $scoreDebut - 20) {
                $patterns[] = "😴 Signes de fatigue détectés : les performances baissent en fin de session. Pensez à faire des pauses.";
            }
        }
        
        return $patterns;
    }
    
    private function createLearningProfile(array $stats, array $trends): array
    {
        $forcePrincipale = '';
        $faiblessePrincipale = '';
        
        $meilleurType = null;
        $meilleurTaux = 0;
        $pireType = null;
        $pireTaux = 100;
        
        foreach ($stats['details_par_type'] as $type => $data) {
            if ($data['taux_reussite'] > $meilleurTaux) {
                $meilleurTaux = $data['taux_reussite'];
                $meilleurType = $type;
            }
            if ($data['taux_reussite'] < $pireTaux) {
                $pireTaux = $data['taux_reussite'];
                $pireType = $type;
            }
        }
        
        $forcePrincipale = $meilleurType ? match($meilleurType) {
            'MEMOIRE' => 'Mémoire visuelle',
            'ATTENTION' => 'Concentration et observation',
            'LOGIQUE' => 'Raisonnement logique',
            'CHRONO' => 'Rapidité d\'exécution',
            default => $meilleurType
        } : 'Non déterminée';
        
        $faiblessePrincipale = $pireType ? match($pireType) {
            'MEMOIRE' => 'Mémoire à court terme',
            'ATTENTION' => 'Maintien de l\'attention',
            'LOGIQUE' => 'Raisonnement abstrait',
            'CHRONO' => 'Gestion du temps',
            default => $pireType
        } : 'Non déterminée';
        
        if ($stats['temps_moyen'] < 15) {
            $styleApprentissage = 'Réflexif - Répond rapidement';
        } elseif ($stats['temps_moyen'] > 40) {
            $styleApprentissage = 'Analytique - Prend le temps de réfléchir';
        } else {
            $styleApprentissage = 'Équilibré - Bon rythme de travail';
        }
        
        return [
            'force_principale' => $forcePrincipale,
            'faiblesse_principale' => $faiblessePrincipale,
            'style_apprentissage' => $styleApprentissage,
            'niveau_concentration' => $trends['consistance'] == 'elevée' ? 'Excellent' : ($trends['consistance'] == 'moyenne' ? 'Bon' : 'À améliorer'),
            'profil_score' => $this->getProfileDescription($stats, $trends)
        ];
    }
    
    private function getProfileDescription(array $stats, array $trends): string
    {
        if ($stats['taux_reussite_global'] >= 80 && $trends['pente'] > 0) {
            return "Élève brillant - Excellentes performances avec progression constante";
        } elseif ($stats['taux_reussite_global'] >= 70) {
            return "Bon élève - Très bonnes performances";
        } elseif ($stats['taux_reussite_global'] >= 50) {
            return "Élève moyen - Performances correctes, peut encore progresser";
        } else {
            return "Élève en difficulté - Nécessite un accompagnement renforcé";
        }
    }
    
    private function makePredictions(array $stats, array $trends): array
    {
        $scorePrediction = min(100, max(0, $stats['score_moyen'] + $trends['pente'] * 2));
        $reussitePrediction = min(100, max(0, $stats['taux_reussite_global'] + $trends['pente'] * 3));
        
        return [
            'score_projete' => round($scorePrediction, 1),
            'taux_reussite_projete' => round($reussitePrediction, 1),
            'objectif_court_terme' => $stats['taux_reussite_global'] < 70 
                ? "Atteindre 70% de taux de réussite dans les 2 à 4 prochaines semaines" 
                : "Maintenir un taux de réussite supérieur à 70% et diversifier les types d'exercices",
            'objectif_long_terme' => $stats['score_moyen'] < 60 
                ? "Atteindre une moyenne de 60 points sur l'ensemble des exercices d'ici 2 à 3 mois" 
                : "Atteindre 85 points de moyenne et développer l'autonomie"
        ];
    }
    
    private function generateDetailedRecommendations(array $stats, array $patterns, array $profile): array
    {
        $recommendations = [
            'immediates' => [],
            'hebdomadaires' => [],
            'specifiques_par_type' => []
        ];
        
        if ($stats['echecs'] > $stats['reussites']) {
            $recommendations['immediates'][] = "🔴 Revoir les bases : Les exercices échoués sont plus nombreux que les réussis. Proposez des exercices plus simples pour reconstruire la confiance.";
        }
        
        if ($stats['taux_reussite_global'] < 50) {
            $recommendations['immediates'][] = "🟠 Intensifier la pratique : Objectif de 3 à 5 exercices par jour pendant une semaine.";
        }
        
        if ($profile['niveau_concentration'] == 'À améliorer') {
            $recommendations['immediates'][] = "🎯 Travailler la concentration : Exercices courts (5-10 min) avec des pauses de 2-3 minutes entre chaque.";
        }
        
        $recommendations['hebdomadaires'][] = "📅 Planifier " . ($stats['taux_reussite_global'] < 60 ? "4-5" : "2-3") . " séances par semaine";
        $recommendations['hebdomadaires'][] = "⏰ " . ($stats['meilleurs_horaires'][0]['heure'] ?? '10') . "h semble être le meilleur moment pour l'apprentissage";
        $recommendations['hebdomadaires'][] = "🎮 Varier les types d'exercices pour maintenir l'intérêt";
        
        foreach ($stats['details_par_type'] as $type => $data) {
            if ($data['taux_reussite'] < 60) {
                $rec = match($type) {
                    'MEMOIRE' => "🧠 Exercices de mémoire : Commencer par 4 paires, augmenter progressivement.",
                    'ATTENTION' => "👁️ Exercices d'attention : Utiliser des thèmes qui intéressent l'enfant.",
                    'LOGIQUE' => "🧩 Exercices logiques : Commencer par des suites simples (+2, +3).",
                    'CHRONO' => "⏱️ Exercices chrono : Ne pas limiter le temps au début, travailler d'abord la précision.",
                    default => "Exercices $type : Renforcer la pratique régulière."
                };
                $recommendations['specifiques_par_type'][$type] = $rec;
            }
        }
        
        return $recommendations;
    }
    
    private function createActionPlan(array $recommendations, array $profile): array
    {
        return [
            'semaine_1' => [
                'objectif' => 'Reconstruire les bases',
                'actions' => array_slice($recommendations['immediates'], 0, 2)
            ],
            'semaine_2' => [
                'objectif' => 'Consolider les acquis',
                'actions' => ['Continuer la pratique quotidienne', 'Introduire des exercices de difficulté moyenne']
            ],
            'semaine_3' => [
                'objectif' => 'Diversifier et renforcer',
                'actions' => ['Ajouter des exercices sur les points faibles', 'Introduire des défis chronométrés']
            ],
            'mois_1' => [
                'objectif' => 'Atteindre les objectifs fixés',
                'actions' => ['Évaluation des progrès', 'Ajustement du programme']
            ]
        ];
    }
    
    private function getBestDays(array $performanceParJour): array
    {
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $best = [];
        foreach ($performanceParJour as $jour => $data) {
            if ($data['count'] > 0) {
                $best[] = [
                    'jour' => $jours[$jour - 1],
                    'taux' => round(($data['success'] / $data['count']) * 100, 1),
                    'nb' => $data['count']
                ];
            }
        }
        usort($best, function($a, $b) { return $b['taux'] <=> $a['taux']; });
        return array_slice($best, 0, 3);
    }
    
    private function assessLevel(array $stats): string
    {
        $score = $stats['score_moyen'];
        if ($score >= 80) return 'Avancé';
        if ($score >= 60) return 'Intermédiaire';
        if ($score >= 40) return 'Débutant';
        return 'Initiation';
    }
    
    private function estimateAge($enfant): string
    {
        if (method_exists($enfant, 'getDateNaissance') && $enfant->getDateNaissance()) {
            $aujourdhui = new \DateTime();
            $age = $aujourdhui->diff($enfant->getDateNaissance())->y;
            return $age . ' ans';
        }
        return 'Non renseigné';
    }
    private function generateQrCode(int $enfantId, string $nomEnfant): string
{
    try {
        $token = $this->qrCodeService->generateSecureToken($enfantId, $nomEnfant);
        return $this->qrCodeService->generateRapportQrCode($enfantId, $token);
    } catch (\Exception $e) {
        // Fallback: QR code texte si erreur
        return '';
    }
}
}