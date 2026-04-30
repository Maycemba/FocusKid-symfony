<?php

namespace App\Controller\Api;

use App\Service\TranslationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/translate', name: 'api_translate_')]
class TranslationController extends AbstractController
{
    private $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    #[Route('', name: 'index', methods: ['POST'])]
    public function translate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $text = $data['text'] ?? '';
        $targetLang = $data['targetLang'] ?? 'en';
        $sourceLang = $data['sourceLang'] ?? 'fr';

        if (empty($text)) {
            return new JsonResponse(['error' => 'Texte vide'], 400);
        }

        $translated = $this->translationService->translate($text, $sourceLang, $targetLang);

        return new JsonResponse([
            'original' => $text,
            'translated' => $translated,
            'source' => $sourceLang,
            'target' => $targetLang
        ]);
    }
}
