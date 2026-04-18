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
    public function enfantStats(int $id, UtilisateurRepository $utilisateurRepository, ReponseExerciceRepository $reponseRepository, ExerciceRepository $exerciceRepository): Response
    {
        $enfant = $utilisateurRepository->find($id);
        $reponses = $reponseRepository->findBy(['enfant_id' => $id]);
        $exercices = $exerciceRepository->findAll();
        
        // Statistiques générales
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
        
        // Statistiques par type d'exercice
        $statsParType = [
            'MÉMOIRE' => ['total' => 0, 'faits' => 0],
            'ATTENTION' => ['total' => 0, 'faits' => 0],
            'LOGIQUE' => ['total' => 0, 'faits' => 0],
            'CHRONO' => ['total' => 0, 'faits' => 0],
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
            }
        }
        
        // Évolution des scores
        $evolution = [];
        foreach ($reponses as $r) {
            $date = $r->getDatePassage()->format('d/m');
            if (!isset($evolution[$date])) {
                $evolution[$date] = 0;
            }
            $evolution[$date] += $r->getScore();
        }
        
        // Performances par jour
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
        
        // Progression
        $progression = 0;
        if (count($reponses) >= 2) {
            $premierScore = $reponses[count($reponses) - 1]->getScore();
            $dernierScore = $reponses[0]->getScore();
            $progression = $dernierScore - $premierScore;
        }
        
        return $this->render('suivi/enfant_stats.html.twig', [
            'enfant' => $enfant,
            'totalExercices' => $totalExercices,
            'exercicesFaits' => $exercicesFaits,
            'tauxReussite' => $tauxReussite,
            'tauxReussiteReel' => $tauxReussiteReel,
            'scoreTotal' => $scoreTotal,
            'tempsMoyen' => $tempsMoyen,
            'statsParType' => $statsParType,
            'evolution' => $evolution,
            'tauxParJour' => $tauxParJour,
            'jours' => $jours,
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
}