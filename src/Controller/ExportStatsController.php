<?php
// src/Controller/ExportStatsController.php

namespace App\Controller;

use App\Repository\ReponseExerciceRepository;
use App\Repository\UtilisateurRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExportStatsController extends AbstractController
{
    #[Route('/suivi/export/pdf/{id}', name: 'app_export_stats_pdf')]
    public function exportPdf(int $id, UtilisateurRepository $utilisateurRepository, ReponseExerciceRepository $reponseRepository): Response
    {
        $enfant = $utilisateurRepository->find($id);
        $reponses = $reponseRepository->findBy(['enfant_id' => $id]);
        
        // Calcul des statistiques
        $totalExercices = count($reponses);
        $scoreTotal = 0;
        $tempsTotal = 0;
        $reussis = 0;
        $maxScore = 0;
        
        foreach ($reponses as $r) {
            $scoreTotal += $r->getScore();
            $tempsTotal += $r->getTempsPasse();
            if ($r->isReussite()) {
                $reussis++;
            }
            if ($r->getScore() > $maxScore) {
                $maxScore = $r->getScore();
            }
        }
        
        $tauxReussite = $totalExercices > 0 ? round(($reussis / $totalExercices) * 100, 1) : 0;
        $tempsMoyen = $totalExercices > 0 ? round($tempsTotal / $totalExercices, 1) : 0;
        $scoreMoyen = $totalExercices > 0 ? round($scoreTotal / $totalExercices, 1) : 0;
        
        // Calcul de la progression
        $progression = 0;
        $premierScore = 0;
        $dernierScore = 0;
        if (count($reponses) >= 2) {
            $premierScore = $reponses[count($reponses) - 1]->getScore();
            $dernierScore = $reponses[0]->getScore();
            $progression = $dernierScore - $premierScore;
        }
        
        // Générer le HTML
        $html = $this->renderView('export/stats_pdf.html.twig', [
            'enfant' => $enfant,
            'reponses' => $reponses,
            'totalExercices' => $totalExercices,
            'scoreTotal' => $scoreTotal,
            'scoreMoyen' => $scoreMoyen,
            'tempsMoyen' => $tempsMoyen,
            'tauxReussite' => $tauxReussite,
            'maxScore' => $maxScore,
            'reussis' => $reussis,
            'progression' => $progression,
            'premierScore' => $premierScore,
            'dernierScore' => $dernierScore,
            'date' => new \DateTime(),
        ]);
        
        // Configurer Dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="statistiques_' . $enfant->getUsername() . '_' . date('Y-m-d') . '.pdf"'
        ]);
    }
    
    #[Route('/suivi/export/csv/{id}', name: 'app_export_stats_csv')]
    public function exportCsv(int $id, UtilisateurRepository $utilisateurRepository, ReponseExerciceRepository $reponseRepository): Response
    {
        $enfant = $utilisateurRepository->find($id);
        $reponses = $reponseRepository->findBy(['enfant_id' => $id]);
        
        $csvContent = "Date;Score;Temps (s);Réussite;Détails\n";
        
        foreach ($reponses as $r) {
            $csvContent .= sprintf(
                "%s;%d;%d;%s;%s\n",
                $r->getDatePassage()->format('d/m/Y H:i'),
                $r->getScore(),
                $r->getTempsPasse(),
                $r->isReussite() ? 'Oui' : 'Non',
                str_replace([";", "\n"], [" ", " "], $r->getReponses() ?? '')
            );
        }
        
        return new Response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="statistiques_' . $enfant->getUsername() . '_' . date('Y-m-d') . '.csv"'
        ]);
    }
}