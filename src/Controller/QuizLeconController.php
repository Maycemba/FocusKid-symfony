<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class QuizLeconController extends AbstractController
{
    #[Route('/api/generer-quiz-lecon', name: 'api_generer_quiz_lecon', methods: ['POST'])]
    public function genererQuizLecon(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $contenu = $data['contenu'] ?? '';

        if (empty($contenu)) {
            return new JsonResponse(['error' => 'Contenu vide'], 400);
        }

        $apiKey = $_ENV['GROQ_API_KEY'] ?? $_SERVER['GROQ_API_KEY'] ?? '';
        if ($apiKey === '') {
            return new JsonResponse(['error' => 'Cle API Groq manquante'], 500);
        }

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => 'llama-3.3-70b-versatile',
                'max_tokens' => 1000,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "Tu es un assistant pedagogique pour enfants.
Genere exactement 3 questions QCM basees sur ce contenu de lecons pour tester la concentration d'un enfant.

CONTENU:
{$contenu}

Reponds UNIQUEMENT en JSON valide, sans texte avant ou apres, avec ce format exact:
{
  \"questions\": [
    {
      \"question\": \"...\",
      \"options\": [\"A. ...\", \"B. ...\", \"C. ...\", \"D. ...\"],
      \"correct\": 0
    }
  ]
}

\"correct\" est l'index (0-3) de la bonne reponse. Questions simples et adaptees aux enfants.",
                    ],
                ],
            ]),
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return new JsonResponse(['error' => 'cURL echoue', 'detail' => $curlError], 500);
        }

        if ($httpCode !== 200) {
            return new JsonResponse(['error' => 'Erreur API Groq', 'detail' => $response], 500);
        }

        $result = json_decode($response, true);
        $text = $result['choices'][0]['message']['content'] ?? '';
        $clean = preg_replace('/```json|```/', '', $text);
        $quiz = json_decode(trim($clean), true);

        if (!$quiz || !isset($quiz['questions'])) {
            return new JsonResponse(['error' => 'Reponse invalide de Groq', 'raw' => $text], 500);
        }

        return new JsonResponse($quiz);
    }
}
