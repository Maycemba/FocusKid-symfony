<?php

namespace App\Controller;

use App\Entity\Jeu;
use App\Entity\Score;
use App\Repository\JeuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/front/jeu')]
final class FrontJeuController extends AbstractController
{
    /**
     * Liste de tous les jeux disponibles (lecture seule, front)
     */
    #[Route('', name: 'app_front_jeu_list', methods: ['GET'])]
    public function list(Request $request, JeuRepository $jeuRepository, \Knp\Component\Pager\PaginatorInterface $paginator): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'asc');

        $queryBuilder = $jeuRepository->createQueryBuilder('j');

        if ($search) {
            $queryBuilder->andWhere('j.titre LIKE :search')
                         ->setParameter('search', '%' . $search . '%');
        }

        if ($sort === 'asc') {
            $queryBuilder->orderBy('j.titre', 'ASC');
        } elseif ($sort === 'desc') {
            $queryBuilder->orderBy('j.titre', 'DESC');
        } elseif ($sort === 'niveau') {
            $queryBuilder->orderBy('j.niveau', 'ASC');
        }

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            6 // Nombre de jeux par page
        );

        return $this->render('front/jeu/list.html.twig', [
            'jeus' => $pagination,
            'current_search' => $search,
            'current_sort' => $sort,
        ]);
    }

    /**
     * Jouer à un jeu : GET = afficher les questions, POST = corriger + sauvegarder le score
     */
    #[Route('/{id}/play', name: 'app_front_jeu_play', methods: ['GET', 'POST'])]
    public function play(Request $request, Jeu $jeu, EntityManagerInterface $em, SessionInterface $session): Response
    {
        $questions = $jeu->getQuestions();

        if ($request->isMethod('POST')) {
            $answers = $request->request->all('answers'); // tableau [question_id => reponse_choisie]
            $score = 0;
            $total = count($questions);
            $details = [];

            foreach ($questions as $question) {
                $id = $question->getId();
                $chosen = $answers[$id] ?? null;
                $bonneReponseLettre = strtoupper(trim((string)$question->getBonneReponse()));
                
                $texteBonneReponse = '';
                if ($bonneReponseLettre === 'A') {
                    $texteBonneReponse = $question->getOptionA() ?? $question->getOption_a();
                } elseif ($bonneReponseLettre === 'B') {
                    $texteBonneReponse = $question->getOptionB() ?? $question->getOption_b();
                } elseif ($bonneReponseLettre === 'C') {
                    $texteBonneReponse = $question->getOptionC() ?? $question->getOption_c();
                } else {
                    $texteBonneReponse = $question->getBonneReponse();
                }

                $isCorrect = ($chosen !== null && strtolower(trim($chosen)) === strtolower(trim((string)$texteBonneReponse)));

                if ($isCorrect) {
                    $score++;
                }

                $details[] = [
                    'question' => $question->getQuestionText(),
                    'chosen' => $chosen,
                    'bonne_reponse' => $texteBonneReponse,
                    'is_correct' => $isCorrect,
                ];
            }

            // Sauvegarder le score en BDD
            $scoreEntity = new Score();
            $scoreEntity->setJeu($jeu);
            $scoreEntity->setPoints($score);
            $scoreEntity->setDatePartie(new \DateTime());
            // utilisateur = null (pas d'auth Symfony pour l'instant)
            $em->persist($scoreEntity);
            $em->flush();

            // Stocker en session pour l'afficher sur la page résultat
            $session->set('jeu_result', [
                'score' => $score,
                'total' => $total,
                'details' => $details,
                'jeu_id' => $jeu->getId(),
                'jeu_titre' => $jeu->getTitre(),
            ]);

            return $this->redirectToRoute('app_front_jeu_result', ['id' => $jeu->getId()]);
        }

        return $this->render('front/jeu/play.html.twig', [
            'jeu' => $jeu,
            'questions' => $questions,
        ]);
    }

    /**
     * Page de résultats après avoir joué
     */
    #[Route('/{id}/result', name: 'app_front_jeu_result', methods: ['GET'])]
    public function result(Jeu $jeu, SessionInterface $session): Response
    {
        $result = $session->get('jeu_result');

        // Sécurité : si on arrive ici sans avoir joué, rediriger
        if (!$result || $result['jeu_id'] !== $jeu->getId()) {
            return $this->redirectToRoute('app_front_jeu_play', ['id' => $jeu->getId()]);
        }

        return $this->render('front/jeu/result.html.twig', [
            'jeu' => $jeu,
            'score' => $result['score'],
            'total' => $result['total'],
            'details' => $result['details'],
        ]);
    }

    /**
     * Exporter les résultats en PDF
     */
    #[Route('/{id}/pdf', name: 'app_front_jeu_pdf', methods: ['GET'])]
    public function downloadPdf(Jeu $jeu, SessionInterface $session): Response
    {
        $result = $session->get('jeu_result');

        if (!$result || $result['jeu_id'] !== $jeu->getId()) {
            return $this->redirectToRoute('app_front_jeu_list');
        }

        // Configuration de Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'DejaVu Sans');
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($pdfOptions);

        // Générer le HTML
        $html = $this->renderView('front/jeu/pdf.html.twig', [
            'jeu' => $jeu,
            'score' => $result['score'],
            'total' => $result['total'],
            'details' => $result['details'],
            'date' => new \DateTime(),
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Envoyer le PDF au navigateur
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Resultats_FocusKid_' . $jeu->getTitre() . '.pdf"'
        ]);
    }
}
