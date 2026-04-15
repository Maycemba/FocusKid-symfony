<?php

namespace App\Controller;

use App\Entity\Score;
use App\Repository\JeuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GamesController extends AbstractController
{
    #[Route('/games', name: 'app_games_index')]
    public function index(JeuRepository $jeuRepository): Response
    {
        $jeux = $jeuRepository->findAll();

        return $this->render('games/index.html.twig', [
            'jeux' => $jeux,
        ]);
    }

    #[Route('/games/{id}/submit', name: 'app_games_submit', methods: ['POST'])]
    public function submit(Request $request, $id, JeuRepository $jeuRepository, EntityManagerInterface $entityManager): Response
    {
        $jeu = $jeuRepository->find($id);
        if (!$jeu) {
            throw $this->createNotFoundException('Jeu not found');
        }

        $answers = $request->request->all();
        $score = 0;
        $total = count($jeu->getQuestions());

        foreach ($jeu->getQuestions() as $question) {
            $userAnswer = $answers['q' . $question->getId()] ?? null;
            if ($userAnswer === $question->getBonneReponse()) {
                $score++;
            }
        }

        // Assuming user is logged in
        $user = $this->getUser();
        if ($user) {
            $scoreEntity = new Score();
            $scoreEntity->setJeu($jeu);
            $scoreEntity->setUtilisateur($user);
            $scoreEntity->setPoints($score);
            $scoreEntity->setDatePartie(new \DateTime());

            $entityManager->persist($scoreEntity);
            $entityManager->flush();
        }

        return $this->render('games/result.html.twig', [
            'jeu' => $jeu,
            'score' => $score,
            'total' => $total,
        ]);
    }