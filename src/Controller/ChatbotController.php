<?php

namespace App\Controller;

use App\Service\CoursIAService;
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
        private string $groqApiKey,
        private CoursIAService $ia
    ) {}

    #[Route('/suggest', name: 'chatbot_suggest', methods: ['POST'])]
    public function suggest(Request $request): JsonResponse
    {
        $data    = json_decode($request->getContent(), true);
        $message = trim($data['message'] ?? '');
        $context = $data['context'] ?? [];

        if ($message === '') {
            return $this->json(['error' => 'Message vide'], 400);
        }

        // 1. Essayer Groq API (LLaMA) en priorité
        if ($this->groqApiKey !== '') {
            try {
                // ── System prompt optimisé TDAH ──────────────────────────────
                $systemPrompt =
                    "Tu es un expert en pédagogie spécialisée pour les enfants TDAH (Trouble Déficit de l'Attention avec ou sans Hyperactivité), "
                    . "intégré dans la plateforme FocusKids.\n\n"
                    . "CONTEXTE DU COURS EN COURS DE CRÉATION :\n"
                    . "- Titre : " . $this->val($context['titre'] ?? '') . "\n"
                    . "- Niveau : " . $this->val($context['niveau'] ?? '') . "\n"
                    . "- Description : " . $this->val($context['description'] ?? '') . "\n"
                    . "- Formateur : " . $this->val($context['formateur'] ?? '') . "\n\n"
                    . "RÈGLES ABSOLUES — applique-les à CHAQUE réponse :\n"
                    . "1. TITRES : max 7 mots, commence par un verbe d'action ou une question, crée un défi/mystère/aventure. "
                    . "   Exemples : 'Deviens un super calculateur !', 'Le mystère des formes cachées', 'Mission : décode les mots !'\n"
                    . "2. DESCRIPTIONS : 2-3 phrases MAXIMUM. Parle DIRECTEMENT à l'enfant avec 'tu'. "
                    . "   Mots simples. Mentionne une activité fun ou un défi concret. Donne envie IMMÉDIATEMENT.\n"
                    . "3. ACTIVITÉS : courtes (max 15 min), avec mouvement ou défi, jamais passives.\n"
                    . "4. TON : énergique, enthousiaste, jamais scolaire ou ennuyeux.\n"
                    . "5. STRUCTURE : utilise des listes à puces avec un emoji par item.\n"
                    . "6. LANGUE : français uniquement.\n\n"
                    . "Avant de répondre, demande-toi toujours : 'Un enfant TDAH aurait-il envie de cliquer sur ce cours ?'";

                $response = $this->httpClient->request('POST', 'https://api.groq.com/openai/v1/chat/completions', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->groqApiKey,
                        'Content-Type'  => 'application/json',
                    ],
                    'json' => [
                        'model'       => 'llama-3.3-70b-versatile',
                        'messages'    => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user',   'content' => $message],
                        ],
                        'temperature' => 0.85,
                        'max_tokens'  => 900,
                    ],
                ]);

                $result = $response->toArray();
                $reply  = $result['choices'][0]['message']['content'] ?? null;

                if ($reply) {
                    return $this->json(['reply' => $reply]);
                }
            } catch (\Throwable) {
                // Groq indisponible → fallback TF-IDF
            }
        }

        // 2. Fallback : modèle TF-IDF local entraîné
        try {
            $reply = $this->ia->repondre($message, $context);
            return $this->json(['reply' => $reply . "\n\n_(réponse locale - vérifiez votre clé Groq)_"]);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Service indisponible : ' . $e->getMessage()], 500);
        }
    }

    private function val(string $v): string
    {
        return $v !== '' ? $v : '(non renseigné)';
    }
}
