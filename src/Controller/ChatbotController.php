<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/chatbot')]
class ChatbotController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $groqApiKey
    ) {}

    #[Route('/suggest', name: 'chatbot_suggest', methods: ['POST'])]
    public function suggest(Request $request): JsonResponse
    {
        $data    = json_decode($request->getContent(), true);
        $message = trim($data['message'] ?? '');
        $context = $data['context'] ?? [];   // titre, niveau, description actuels

        if ($message === '') {
            return $this->json(['error' => 'Message vide'], 400);
        }

        // Construction du prompt système
        $systemPrompt = <<<PROMPT
Tu es un assistant pédagogique expert qui aide les enseignants à créer des cours pour enfants sur la plateforme FocusKids.
Ton rôle est de donner des idées créatives et pratiques pour enrichir les cours.

Contexte du cours en cours de création :
- Titre actuel : {$this->val($context['titre'] ?? '')}
- Niveau : {$this->val($context['niveau'] ?? '')}
- Description actuelle : {$this->val($context['description'] ?? '')}
- Formateur : {$this->val($context['formateur'] ?? '')}

Tu dois :
1. Suggérer des idées concrètes de titres, descriptions, objectifs pédagogiques, ou plans de cours
2. Adapter tes suggestions au niveau scolaire indiqué (CP, CE1, 6ème, etc.)
3. Être encourageant, clair et concis
4. Répondre en français
5. Formater tes réponses avec des listes à puces quand c'est pertinent

Ne parle que de pédagogie et de création de cours.
PROMPT;

        try {
            $response = $this->httpClient->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->groqApiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                  'model' => 'llama-3.3-70b-versatile',
                    'messages'    => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user',   'content' => $message],
                    ],
                    'temperature' => 0.7,
                    'max_tokens'  => 800,
                ],
            ]);

            $result = $response->toArray();
            $reply  = $result['choices'][0]['message']['content'] ?? 'Désolé, je n\'ai pas pu générer une réponse.';

            return $this->json(['reply' => $reply]);

        } catch (\Throwable $e) {
            return $this->json(['error' => 'Erreur lors de la communication avec l\'IA : ' . $e->getMessage()], 500);
        }
    }

    private function val(string $v): string
    {
        return $v !== '' ? $v : '(non renseigné)';
    }
}