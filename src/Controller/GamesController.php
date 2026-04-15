<?php

namespace App\Controller;

use App\Repository\JeuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    #[Route('/games/{id}', name: 'app_games_show')]
    public function show($id, JeuRepository $jeuRepository): Response
    {
        $jeu = $jeuRepository->find($id);

        if (!$jeu) {
            throw $this->createNotFoundException('Jeu not found');
        }

        return $this->render('games/show.html.twig', [
            'jeu' => $jeu,
        ]);
    }
}