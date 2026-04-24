<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/chatbot')]
class ChatbotLeconController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $groqApiKey
    ) {}

    /**
     * Endpoint AJAX : génère résumé + objectif + durée pour l'aperçu IA
     */
    #[Route('/lecon-preview', name: 'chatbot_lecon_preview', methods: ['POST'])]
    public function leconPreview(Request $request): JsonResponse
    {
        $data    = json_decode($request->getContent(), true);
        $titre   = trim($data['titre']   ?? '');
        $contenu = trim($data['contenu'] ?? '');

        if ($titre === '' && $contenu === '') {
            return $this->json(['error' => 'Titre et contenu vides.'], 400);
        }

        $prompt = <<<PROMPT
Tu es un assistant pédagogique. À partir de cette leçon, génère UNIQUEMENT un objet JSON valide (sans texte autour, sans backticks) :
{
  "resume": "résumé simple en 1 phrase pour un parent non-spécialiste",
  "objectif": "objectif pédagogique en 1 phrase claire",
  "duree": 15
}

La durée est un nombre entier de minutes.

Titre de la leçon : $titre
Contenu : $contenu
PROMPT;

        try {
            $response = $this->httpClient->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->groqApiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model'       => 'llama-3.3-70b-versatile',
                    'messages'    => [['role' => 'user', 'content' => $prompt]],
                    'temperature' => 0.3,
                    'max_tokens'  => 200,
                ],
            ]);

            $result = $response->toArray();
            $text   = $result['choices'][0]['message']['content'] ?? '{}';
            $text   = preg_replace('/```json|```/', '', $text);
            $parsed = json_decode(trim($text), true);

            if (!$parsed) {
                throw new \RuntimeException('Réponse IA invalide.');
            }

            return $this->json([
                'resume'   => $parsed['resume']   ?? 'Votre enfant va apprendre ' . $titre,
                'objectif' => $parsed['objectif'] ?? 'Comprendre les bases de ' . $titre,
                'duree'    => $parsed['duree']     ?? 15,
            ]);

        } catch (\Throwable $e) {
            return $this->json(['error' => 'Erreur IA : ' . $e->getMessage()], 500);
        }
    }
}