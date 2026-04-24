<?php
// src/Controller/SuiviController.php

namespace App\Controller;

use App\Repository\ExerciceRepository;
use App\Repository\ReponseExerciceRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\ExerciceEnfantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/suivi')]
class SuiviController extends AbstractController
{
    #[Route('/', name: 'app_suivi_index')]
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
        // Récupérer tous les enfants (role = 'enfant')
        $enfants = $utilisateurRepository->findBy(['role' => 'enfant']);
        
        return $this->render('suivi/index.html.twig', [
            'enfants' => $enfants,
        ]);
    }
    
    #[Route('/enfant/{id}', name: 'app_suivi_enfant_menu')]
    public function enfantMenu(int $id, UtilisateurRepository $utilisateurRepository): Response
    {
        $enfant = $utilisateurRepository->find($id);
        
        if (!$enfant) {
            throw $this->createNotFoundException('Enfant non trouvé');
        }
        
        return $this->render('suivi/enfant_menu.html.twig', [
            'enfant' => $enfant,
        ]);
    }
    
    #[Route('/enfant/{id}/exercices', name: 'app_suivi_enfant_exercices')]
    public function enfantExercices(int $id, UtilisateurRepository $utilisateurRepository, ReponseExerciceRepository $reponseRepository): Response
    {
        $enfant = $utilisateurRepository->find($id);
        
        // Récupérer les exercices avec leurs réponses
        $reponses = $reponseRepository->findBy(['enfant_id' => $id]);
        
        return $this->render('suivi/enfant_exercices.html.twig', [
            'enfant' => $enfant,
            'reponses' => $reponses,
        ]);
    }
    
    #[Route('/enfant/{id}/heatmap', name: 'app_suivi_enfant_heatmap')]
    public function enfantHeatmap(int $id, UtilisateurRepository $utilisateurRepository, ReponseExerciceRepository $reponseRepository): Response
    {
        $enfant = $utilisateurRepository->find($id);
        $reponses = $reponseRepository->findBy(['enfant_id' => $id]);
        
        // Préparer les données pour la heatmap (par heure et jour)
        $heatmapData = [];
        for ($h = 0; $h < 24; $h++) {
            for ($d = 1; $d <= 7; $d++) {
                $heatmapData[$d][$h] = ['count' => 0, 'success' => 0];
            }
        }
        
        foreach ($reponses as $r) {
            $heure = (int)$r->getDatePassage()->format('H');
            $jour = (int)$r->getDatePassage()->format('N');
            $heatmapData[$jour][$heure]['count']++;
            if ($r->isReussite()) {
                $heatmapData[$jour][$heure]['success']++;
            }
        }
        
        return $this->render('suivi/enfant_heatmap.html.twig', [
            'enfant' => $enfant,
            'heatmapData' => $heatmapData,
        ]);
    }
    
   #[Route('/enfant/{id}/stats', name: 'app_suivi_enfant_stats')]
    public function enfantStats(int $id, ChartBuilderInterface $chartBuilder, UtilisateurRepository $utilisateurRepository, ReponseExerciceRepository $reponseRepository, ExerciceRepository $exerciceRepository): Response
    {
        $enfant = $utilisateurRepository->find($id);
        
        if (!$enfant) {
            throw $this->createNotFoundException('Enfant non trouvé');
        }
        
        $reponses = $reponseRepository->findBy(['enfant_id' => $id]);
        $exercices = $exerciceRepository->findAll();
        
        // ========== STATISTIQUES GÉNÉRALES ==========
        $totalExercices = count($exercices);
        $exercicesFaits = count($reponses);
        $tauxReussite = $totalExercices > 0 ? round(($exercicesFaits / $totalExercices) * 100, 1) : 0;
        
        $scoreTotal = 0;
        $tempsTotal = 0;
        $reussites = 0;
        
        foreach ($reponses as $r) {
            $scoreTotal += $r->getScore();
            $tempsTotal += $r->getTempsPasse();
            if ($r->isReussite()) $reussites++;
        }
        
        $tauxReussiteReel = count($reponses) > 0 ? round(($reussites / count($reponses)) * 100, 1) : 0;
        $tempsMoyen = count($reponses) > 0 ? round($tempsTotal / count($reponses), 1) : 0;
        $scoreMoyen = count($reponses) > 0 ? round($scoreTotal / count($reponses), 1) : 0;
        
        // ========== STATISTIQUES PAR TYPE ==========
        $statsParType = [
            'MÉMOIRE' => ['total' => 0, 'faits' => 0, 'scores' => []],
            'ATTENTION' => ['total' => 0, 'faits' => 0, 'scores' => []],
            'LOGIQUE' => ['total' => 0, 'faits' => 0, 'scores' => []],
            'CHRONO' => ['total' => 0, 'faits' => 0, 'scores' => []],
        ];
        
        foreach ($exercices as $exo) {
            if (isset($statsParType[$exo->getType()])) {
                $statsParType[$exo->getType()]['total']++;
            }
        }
        
        foreach ($reponses as $r) {
            $exercice = $exerciceRepository->find($r->getExerciceId());
            if ($exercice && isset($statsParType[$exercice->getType()])) {
                $statsParType[$exercice->getType()]['faits']++;
                $statsParType[$exercice->getType()]['scores'][] = $r->getScore();
            }
        }
        
        // Calcul des moyennes par type
        $moyennesParType = [];
        foreach ($statsParType as $type => $data) {
            $moyennesParType[$type] = !empty($data['scores']) ? round(array_sum($data['scores']) / count($data['scores']), 1) : 0;
        }
        
        // ========== ÉVOLUTION DES SCORES ==========
        $evolutionLabels = [];
        $evolutionData = [];
        foreach (array_reverse($reponses) as $r) {
            $evolutionLabels[] = $r->getDatePassage()->format('d/m');
            $evolutionData[] = $r->getScore();
        }
        
        // ========== PERFORMANCES PAR JOUR ==========
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $perfParJour = array_fill(0, 7, 0);
        $countParJour = array_fill(0, 7, 0);
        
        foreach ($reponses as $r) {
            $jour = (int)$r->getDatePassage()->format('N') - 1;
            $countParJour[$jour]++;
            if ($r->isReussite()) {
                $perfParJour[$jour]++;
            }
        }
        
        $tauxParJour = [];
        for ($i = 0; $i < 7; $i++) {
            $tauxParJour[$i] = $countParJour[$i] > 0 ? round(($perfParJour[$i] / $countParJour[$i]) * 100, 1) : 0;
        }
        
        // ========== PROGRESSION ==========
        $progression = 0;
        if (count($reponses) >= 2) {
            $premierScore = $reponses[count($reponses) - 1]->getScore();
            $dernierScore = $reponses[0]->getScore();
            $progression = $dernierScore - $premierScore;
        }
        
        // ========== GRAPHIQUE ÉVOLUTION ==========
        $evolutionChart = $chartBuilder->createChart(Chart::TYPE_LINE)
            ->setData([
                'labels' => $evolutionLabels,
                'datasets' => [
                    [
                        'label' => 'Score',
                        'backgroundColor' => 'rgba(255, 152, 0, 0.2)',
                        'borderColor' => '#FF9800',
                        'data' => $evolutionData,
                        'tension' => 0.3,
                        'fill' => true,
                        'pointBackgroundColor' => '#FF9800',
                        'pointBorderColor' => '#fff',
                        'pointRadius' => 5,
                        'pointHoverRadius' => 7,
                    ],
                ],
            ])
            ->setOptions([
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Évolution des scores dans le temps',
                        'font' => ['size' => 16],
                    ],
                    'tooltip' => [
                        'callbacks' => [
                            'label' => 'function(context) { return "Score: " + context.raw + " points"; }',
                        ],
                    ],
                ],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Score (points)',
                        ],
                    ],
                    'x' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Date',
                        ],
                    ],
                ],
            ]);
        
        // ========== GRAPHIQUE PAR TYPE (CAMEMBERT) ==========
        $typeLabels = [];
        $typeData = [];
        $typeColors = ['#9C27B0', '#2196F3', '#FF9800', '#F44336'];
        foreach ($statsParType as $type => $data) {
            if ($data['total'] > 0) {
                $typeLabels[] = $type;
                $typeData[] = $data['faits'];
            }
        }
        
        $typeChart = $chartBuilder->createChart(Chart::TYPE_PIE)
            ->setData([
                'labels' => $typeLabels,
                'datasets' => [
                    [
                        'data' => $typeData,
                        'backgroundColor' => $typeColors,
                        'borderWidth' => 0,
                    ],
                ],
            ])
            ->setOptions([
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Répartition des exercices complétés par type',
                        'font' => ['size' => 16],
                    ],
                    'legend' => [
                        'position' => 'bottom',
                    ],
                ],
            ]);
        
        // ========== GRAPHIQUE PERFORMANCE PAR JOUR (BARRES) ==========
        $weekdayChart = $chartBuilder->createChart(Chart::TYPE_BAR)
            ->setData([
                'labels' => $jours,
                'datasets' => [
                    [
                        'label' => 'Taux de réussite (%)',
                        'data' => $tauxParJour,
                        'backgroundColor' => '#FF9800',
                        'borderRadius' => 10,
                        'barPercentage' => 0.7,
                    ],
                ],
            ])
            ->setOptions([
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Performance par jour de la semaine',
                        'font' => ['size' => 16],
                    ],
                ],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'max' => 100,
                        'title' => [
                            'display' => true,
                            'text' => 'Taux de réussite (%)',
                        ],
                    ],
                ],
            ]);
        
        return $this->render('suivi/enfant_stats.html.twig', [
            'enfant' => $enfant,
            'evolutionChart' => $evolutionChart,
            'typeChart' => $typeChart,
            'weekdayChart' => $weekdayChart,
            'totalExercices' => $totalExercices,
            'exercicesFaits' => $exercicesFaits,
            'tauxReussite' => $tauxReussite,
            'tauxReussiteReel' => $tauxReussiteReel,
            'scoreTotal' => $scoreTotal,
            'scoreMoyen' => $scoreMoyen,
            'tempsMoyen' => $tempsMoyen,
            'statsParType' => $statsParType,
            'moyennesParType' => $moyennesParType,
            'progression' => $progression,
        ]);
    }

    
    // Dans src/Controller/SuiviController.php

#[Route('/enfant/{id}/planning', name: 'app_suivi_enfant_planning')]
public function enfantPlanning(int $id, UtilisateurRepository $utilisateurRepository, ExerciceRepository $exerciceRepository, ExerciceEnfantRepository $exerciceEnfantRepository): Response
{
    $enfant = $utilisateurRepository->find($id);
    
    if (!$enfant) {
        throw $this->createNotFoundException('Enfant non trouvé');
    }
    
    // Récupérer les exercices assignés à cet enfant
    $exercicesEnfant = $exerciceEnfantRepository->findBy(['enfant_id' => $id]);
    $exercicesIds = [];
    foreach ($exercicesEnfant as $ee) {
        $exercicesIds[] = $ee->getExerciceId();
    }
    
    // Récupérer les exercices actifs
    $exercices = $exerciceRepository->findBy(['id' => $exercicesIds, 'actif' => true]);
    
    // Organiser les exercices par jour
    $planningParJour = [
        'Lundi' => [],
        'Mardi' => [],
        'Mercredi' => [],
        'Jeudi' => [],
        'Vendredi' => [],
        'Samedi' => [],
        'Dimanche' => []
    ];
    
    foreach ($exercices as $exercice) {
        $contenu = json_decode($exercice->getContenu(), true);
        $jours = isset($contenu['jours']) ? $contenu['jours'] : [];
        
        // Si aucun jour spécifié, assigner à tous les jours
        if (empty($jours)) {
            $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        }
        
        foreach ($jours as $jour) {
            if (isset($planningParJour[$jour])) {
                $planningParJour[$jour][] = $exercice;
            }
        }
    }
    
    return $this->render('suivi/enfant_planning.html.twig', [
        'enfant' => $enfant,
        'planning' => $planningParJour,
    ]);
}
#[Route('/enfant/{id}/reponses', name: 'app_suivi_enfant_reponses')]
public function enfantReponses(int $id, EntityManagerInterface $em): Response
{
    $enfant = $em->getConnection()->fetchAssociative(
        "SELECT * FROM utilisateur WHERE id = :id AND role = 'enfant'",
        ['id' => $id]
    );
    
    $reponses = $em->getConnection()->fetchAllAssociative(
        "SELECT r.*, e.titre as exercice_titre, e.type as exercice_type 
         FROM reponse_exercice r
         JOIN exercice e ON e.id = r.exercice_id
         WHERE r.enfant_id = :id
         ORDER BY r.date_passage DESC",
        ['id' => $id]
    );
    
    return $this->render('suivi/enfant_reponses.html.twig', [
        'enfant' => $enfant,
        'reponses' => $reponses,
    ]);
}
}