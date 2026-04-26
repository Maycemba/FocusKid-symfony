<?php

namespace App\Controller;

use App\Entity\Jeu;
use App\Repository\JeuRepository;
use App\Repository\QuestionRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    #[Route('/game/play/{id}', name: 'app_game_play')]
    public function play(
        int $id, 
        Request $request, 
        JeuRepository $jeuRepository, 
        QuestionRepository $questionRepository,
        PaginatorInterface $paginator,
        SessionInterface $session
    ): Response {
        $jeu = $jeuRepository->find($id);
        if (!$jeu) {
            throw $this->createNotFoundException('Jeu non trouvé');
        }

        $page = $request->query->getInt('page', 1);

        // Initialiser le score au début du jeu
        if ($page === 1 && !$request->isMethod('POST')) {
            $session->set('game_score', 0);
            $session->set('answered_questions', []);
        }

        // Récupérer la question actuelle via pagination
        $queryBuilder = $questionRepository->createQueryBuilder('q')
            ->where('q.jeu = :jeu')
            ->setParameter('jeu', $jeu)
            ->orderBy('q.id', 'ASC');

        $pagination = $paginator->paginate(
            $queryBuilder,
            $page,
            1 // Une seule question par page
        );

        // Si la page est vide (fin du jeu), rediriger vers les résultats
        if (count($pagination) === 0 && $page > 1) {
            return $this->redirectToRoute('app_game_results', ['id' => $id]);
        }

        // Gérer la soumission de la réponse
        if ($request->isMethod('POST')) {
            $questionId = $request->request->get('question_id');
            $userAnswer = $request->request->get('answer');
            
            $question = $questionRepository->find($questionId);
            $answered = $session->get('answered_questions', []);

            // Eviter de compter plusieurs fois la même question si on revient en arrière
            if (!in_array($questionId, $answered)) {
                if ($question && $userAnswer === $question->getBonneReponse()) {
                    $currentScore = $session->get('game_score', 0);
                    $session->set('game_score', $currentScore + 1);
                }
                $answered[] = $questionId;
                $session->set('answered_questions', $answered);
            }

            // Aller à la page suivante
            if ($page < $pagination->getTotalItemCount()) {
                return $this->redirectToRoute('app_game_play', ['id' => $id, 'page' => $page + 1]);
            } else {
                return $this->redirectToRoute('app_game_results', ['id' => $id]);
            }
        }

        return $this->render('game/play.html.twig', [
            'jeu' => $jeu,
            'pagination' => $pagination,
            'current_question' => $pagination->count() > 0 ? $pagination[0] : null,
        ]);
    }

    #[Route('/game/results/{id}', name: 'app_game_results')]
    public function results(int $id, JeuRepository $jeuRepository, SessionInterface $session): Response
    {
        $jeu = $jeuRepository->find($id);
        $score = $session->get('game_score', 0);
        $total = $jeu ? count($jeu->getQuestions()) : 0;

        return $this->render('game/results.html.twig', [
            'jeu' => $jeu,
            'score' => $score,
            'total' => $total,
        ]);
    }
}
