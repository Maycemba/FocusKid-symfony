<?php
// src/Controller/StressController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

#[Route('/stress')]
class StressController extends AbstractController
{
    private readonly string $pythonApiUrl;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface     $logger
    ) {
        $this->pythonApiUrl = $_ENV['PYTHON_API_URL'] ?? $_SERVER['PYTHON_API_URL'] ?? 'http://127.0.0.1:5001';
    }

    // ──────────────────────────────────────────────
    // Page principale (webcam + dashboard)
    // ──────────────────────────────────────────────
    #[Route('/', name: 'stress_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('stress/index.html.twig');
    }

    // ──────────────────────────────────────────────
    // Endpoint AJAX : reçoit une frame base64
    // Retourne : { emotion, stress, probabilities, face_detected }
    // ──────────────────────────────────────────────
    #[Route('/analyze', name: 'stress_analyze', methods: ['POST'])]
    public function analyze(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $frame = $data['frame'] ?? null;

        if (empty($frame)) {
            return $this->json(['error' => 'Aucune frame reçue'], Response::HTTP_BAD_REQUEST);
        }

        // Vérification basique : doit commencer par data:image/
        if (!str_starts_with($frame, 'data:image/')) {
            return $this->json(['error' => 'Format image invalide'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $response = $this->httpClient->request(
                'POST',
                $this->pythonApiUrl . '/predict',
                [
                    'json'    => ['image' => $frame],
                    'timeout' => 5.0,
                    'headers' => ['Accept' => 'application/json'],
                ]
            );

            $result = $response->toArray();

            // Enregistrement en session pour historique
            $session = $request->getSession();
            $history = $session->get('stress_history', []);
            $history[] = [
                'time'    => date('H:i:s'),
                'emotion' => $result['emotion']  ?? 'unknown',
                'stress'  => $result['stress']   ?? 0,
            ];
            // Garder les 20 dernières mesures
            if (count($history) > 20) {
                $history = array_slice($history, -20);
            }
            $session->set('stress_history', $history);

            return $this->json($result);

        } catch (\Exception $e) {
            $this->logger->error('Erreur API Python: ' . $e->getMessage());
            return $this->json([
                'error'   => 'Service IA indisponible',
                'details' => $e->getMessage()
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    // ──────────────────────────────────────────────
    // Statut de l'API Python
    // ──────────────────────────────────────────────
    #[Route('/api-status', name: 'stress_api_status', methods: ['GET'])]
    public function apiStatus(): JsonResponse
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                $this->pythonApiUrl . '/health',
                ['timeout' => 2.0]
            );
            return $this->json(['online' => true] + $response->toArray());
        } catch (\Exception $e) {
            return $this->json(['online' => false, 'error' => $e->getMessage()]);
        }
    }

    // ──────────────────────────────────────────────
    // Historique de session
    // ──────────────────────────────────────────────
    #[Route('/history', name: 'stress_history', methods: ['GET'])]
    public function history(Request $request): JsonResponse
    {
        $history = $request->getSession()->get('stress_history', []);
        return $this->json(['history' => $history]);
    }

    // ──────────────────────────────────────────────
    // Vider l'historique
    // ──────────────────────────────────────────────
    #[Route('/history/clear', name: 'stress_history_clear', methods: ['POST'])]
    public function clearHistory(Request $request): JsonResponse
    {
        $request->getSession()->remove('stress_history');
        return $this->json(['ok' => true]);
    }
 
 
}
