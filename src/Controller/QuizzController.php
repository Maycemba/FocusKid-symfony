<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Service\EmotionApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/quizz')]
class QuizzController extends AbstractController
{
    public function __construct(private EmotionApiService $emotionApi) {}

    #[Route('/start', name: 'quizz_start')]
    public function start(Request $request): Response
    {
        // Récupérer l'utilisateur connecté ou utiliser "Enfant" par défaut
        $user = $this->getUser();
        $childName = 'Enfant';
        $childId = uniqid('child_');

        if ($user instanceof Utilisateur) {
            $fullName = $user->getFullName();
            $childName = $fullName !== '' ? $fullName : ($user->getUsername() ?? 'Enfant');
            $childId = (string) $user->getId();
        }
        
        try {
            $questions = $this->emotionApi->generateQuiz();
            
            if (empty($questions)) {
                throw new \Exception('Aucune question reçue de l\'API');
            }
            
            return $this->render('quizz/quizz.html.twig', [
                'child_name' => htmlspecialchars($childName),
                'child_id' => htmlspecialchars($childId),
                'questions' => $questions
            ]);
        } catch (\Exception $e) {
            return $this->render('quizz/error.html.twig', [
                'error' => 'Impossible de charger le quizz: ' . $e->getMessage()
            ]);
        }
    }

    // Le reste du contrôleur reste identique...
    
    #[Route('/api/generate', name: 'quizz_api_generate', methods: ['GET'])]
    public function apiGenerate(): JsonResponse
    {
        try {
            $questions = $this->emotionApi->generateQuiz();
            return $this->json([
                'success' => true,
                'questions' => $questions
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/analyze', name: 'quizz_api_analyze', methods: ['POST'])]
    public function apiAnalyze(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['answers']) || !is_array($data['answers'])) {
            return $this->json([
                'success' => false,
                'error' => 'Réponses manquantes ou invalides'
            ], 400);
        }
        
        if (count($data['answers']) !== 5) {
            return $this->json([
                'success' => false,
                'error' => '5 réponses sont requises'
            ], 400);
        }
        
        try {
            $result = $this->emotionApi->analyzeAnswers(
                $data['child_id'] ?? 'anonymous',
                $data['answers']
            );
            
            $session = $request->getSession();
            $session->set('last_result', $result);
            $session->set('child_name', $data['child_name'] ?? 'Enfant');
            
            return $this->json([
                'success' => true,
                'result' => $result,
                'redirect_url' => $this->generateUrl('quizz_bilan')
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/bilan', name: 'quizz_bilan')]
    public function bilan(Request $request): Response
    {
        $session = $request->getSession();
        $result = $session->get('last_result');
        $childName = $session->get('child_name', 'Enfant');
        
        if (!$result || !isset($result['success']) || $result['success'] === false) {
            return $this->redirectToRoute('quizz_start');
        }
        
        return $this->render('bilan/enfant.html.twig', [
            'result' => $result,
            'child_name' => $childName
        ]);
    }

    #[Route('/superviseur/{childId}', name: 'quizz_superviseur')]
    public function superviseur(string $childId, Request $request): Response
    {
        $session = $request->getSession();
        $result = $session->get('last_result');
        $childName = $session->get('child_name', 'Enfant');
        
        if (!$result || !isset($result['success']) || $result['success'] === false) {
            return $this->redirectToRoute('quizz_start');
        }
        
        return $this->render('bilan/superviseur.html.twig', [
            'result' => $result,
            'child_id' => $childId,
            'child_name' => $childName
        ]);
    }

    #[Route('/health', name: 'quizz_health')]
    public function health(): JsonResponse
    {
        $isHealthy = $this->emotionApi->checkHealth();
        
        return $this->json([
            'status' => $isHealthy ? 'ok' : 'error',
            'api_python' => $isHealthy ? 'connected' : 'disconnected',
            'message' => $isHealthy ? 'API Python fonctionnelle' : 'API Python inaccessible. Lancez python app.py dans emotion_ai/api/'
        ]);
    }

    #[Route('/test', name: 'quizz_test')]
    public function test(): Response
    {
        return $this->render('quizz/test.html.twig');
    }
}
