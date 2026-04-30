<?php

namespace App\Controller;

use App\Service\PnjService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PnjController extends AbstractController
{
    #[Route('/jeu/pnj/parler', name: 'app_jeu_pnj_parler', methods: ['POST'])]
    public function parler(Request $request, PnjService $pnjService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $message = $data['message'] ?? null;
        $context = $data['context'] ?? null;
        $timezone = $data['timezone'] ?? null;

        $reponseMascotte = $pnjService->getReaction($message, $context, $timezone);

        return new JsonResponse(['reponse' => $reponseMascotte]);
    }
}
