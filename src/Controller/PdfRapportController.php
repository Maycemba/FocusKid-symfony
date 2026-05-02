<?php
// src/Controller/PdfRapportController.php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Repository\UtilisateurRepository;
use App\Service\AdvancedIaRecommendationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PdfRapportController extends AbstractController
{
    #[Route('/pdf/rapport/{id}', name: 'app_pdf_rapport')]
public function generatePdf(int $id, UtilisateurRepository $userRepo, AdvancedIaRecommendationService $iaService): Response
{
    $enfant = $userRepo->find($id);
    
    if (!$enfant) {
        throw $this->createNotFoundException('Enfant non trouvé');
    }
    
    $rapport = $iaService->generateFullReport($id);
    
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    
    $dompdf = new Dompdf($options);
    
    $html = $this->renderView('pdf/rapport_pdf.html.twig', [
        'rapport' => $rapport
    ]);
    
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    // Téléchargement direct (pas d'affichage inline)
    return new Response($dompdf->output(), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="rapport_' . $rapport['enfant']['nom'] . '_' . date('Y-m-d') . '.pdf"'
    ]);
}
}