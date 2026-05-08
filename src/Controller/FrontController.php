<?php

namespace App\Controller;

use App\Repository\CourRepository;
use App\Repository\EmotionRepository;
use App\Repository\ScenarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FrontController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_utilisateur_index');
        }

        return $this->redirectToRoute('app_index');
    }

    #[Route('/index', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('public/base-front/index.html');
    }

    #[Route('/enfant', name: 'app_enfant_cours', methods: ['GET'])]
    public function listeCours(CourRepository $courRepository): Response
    {
        return $this->render('front/cours.html.twig', [
            'cours' => $courRepository->findAll(),
        ]);
    }

    #[Route('/enfant/cours/{id_cours}', name: 'app_enfant_lecons', methods: ['GET'])]
    public function listeLecons(int $id_cours, CourRepository $courRepository): Response
    {
        $cour = $courRepository->find($id_cours);
        if (!$cour) {
            throw $this->createNotFoundException('Cours introuvable');
        }
        return $this->render('front/lecons.html.twig', [
            'cour'   => $cour,
            'lecons' => $cour->getLecons(),
        ]);
    }

    #[Route('/enfant/emotions', name: 'app_enfant_emotions', methods: ['GET'])]
    public function emotions(EmotionRepository $emotionRepository): Response
    {
        return $this->render('front/emotions.html.twig', [
            'emotions' => $emotionRepository->findAll(),
        ]);
    }

    #[Route('/enfant/emotions/{emotionId}/scenarios', name: 'app_enfant_scenarios_emotion', methods: ['GET'])]
    public function scenariosParEmotion(int $emotionId, ScenarioRepository $scenarioRepository): JsonResponse
    {
        $scenarios = $scenarioRepository->findBy(['emotion' => $emotionId]);

        $data = array_map(fn($s) => [
            'id'          => $s->getId(),
            'description' => $s->getDescription(),
            'animation'   => $s->getAnimation(),
        ], $scenarios);

        return new JsonResponse(['scenarios' => $data]);
    }
}