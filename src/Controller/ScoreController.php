<?php

namespace App\Controller;

use App\Entity\Score;
use App\Form\ScoreType;
use App\Repository\ScoreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/score')]
final class ScoreController extends AbstractController
{
    #[Route(name: 'app_score_index', methods: ['GET'])]
    public function index(ScoreRepository $scoreRepository): Response
    {
        return $this->render('score/index.html.twig', [
            'scores' => $scoreRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_score_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $score = new Score();
        $form = $this->createForm(ScoreType::class, $score);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($score);
            $entityManager->flush();

            return $this->redirectToRoute('app_score_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('score/new.html.twig', [
            'score' => $score,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_score_show', methods: ['GET'])]
    public function show(Score $score): Response
    {
        return $this->render('score/show.html.twig', [
            'score' => $score,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_score_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Score $score, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ScoreType::class, $score);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_score_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('score/edit.html.twig', [
            'score' => $score,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_score_delete', methods: ['POST'])]
    public function delete(Request $request, Score $score, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$score->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($score);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_score_index', [], Response::HTTP_SEE_OTHER);
    }
}
