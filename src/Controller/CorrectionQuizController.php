<?php

namespace App\Controller;

use App\Entity\CorrectionQuiz;
use App\Form\CorrectionQuizType;
use App\Repository\CorrectionQuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/correction/quiz')]
final class CorrectionQuizController extends AbstractController
{
    #[Route(name: 'app_correction_quiz_index', methods: ['GET'])]
    public function index(CorrectionQuizRepository $correctionQuizRepository): Response
    {
        return $this->render('correction_quiz/index.html.twig', [
            'correction_quizzes' => $correctionQuizRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_correction_quiz_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $correctionQuiz = new CorrectionQuiz();
        $form = $this->createForm(CorrectionQuizType::class, $correctionQuiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($correctionQuiz);
            $entityManager->flush();

            return $this->redirectToRoute('app_correction_quiz_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('correction_quiz/new.html.twig', [
            'correction_quiz' => $correctionQuiz,
            'form' => $form,
        ]);
    }

    #[Route('/{id_correction}', name: 'app_correction_quiz_show', methods: ['GET'])]
    public function show(CorrectionQuiz $correctionQuiz): Response
    {
        return $this->render('correction_quiz/show.html.twig', [
            'correction_quiz' => $correctionQuiz,
        ]);
    }

    #[Route('/{id_correction}/edit', name: 'app_correction_quiz_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CorrectionQuiz $correctionQuiz, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CorrectionQuizType::class, $correctionQuiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_correction_quiz_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('correction_quiz/edit.html.twig', [
            'correction_quiz' => $correctionQuiz,
            'form' => $form,
        ]);
    }

    #[Route('/{id_correction}', name: 'app_correction_quiz_delete', methods: ['POST'])]
    public function delete(Request $request, CorrectionQuiz $correctionQuiz, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$correctionQuiz->getId_correction(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($correctionQuiz);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_correction_quiz_index', [], Response::HTTP_SEE_OTHER);
    }
}
