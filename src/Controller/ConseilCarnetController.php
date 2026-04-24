<?php

namespace App\Controller;

use App\Repository\CarnetEducatifRepository;
use App\Service\ConseilIACarnetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/carnet/conseil')]
class ConseilCarnetController extends AbstractController
{
    #[Route('/generer/{id}', name: 'conseil_carnet_generer', methods: ['POST'])]
    public function genererConseil(
        int $id,
        CarnetEducatifRepository $carnetRepo,
        ConseilIACarnetService $conseilService
    ): JsonResponse {
        try {
            $carnet = $carnetRepo->find($id);
            
            if (!$carnet) {
                return $this->json(['error' => 'Carnet non trouvé'], 404);
            }
            
            // Vérifier l'utilisateur (si connecté)
            $user = $this->getUser();
            if ($user && method_exists($carnet, 'getUtilisateur') && $carnet->getUtilisateur() !== $user) {
                return $this->json(['error' => 'Accès non autorisé'], 403);
            }
            
            $conseil = $conseilService->analyserCarnetEtGenererConseil($carnet);
            
            return $this->json([
                'success' => true,
                'conseil' => nl2br($conseil),
                'conseil_texte' => $conseil,
                'date' => (new \DateTime())->format('d/m/Y H:i')
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }
}