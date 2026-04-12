<?php

namespace App\Controller;

use App\Entity\ReponseUserQuiz;
use App\Form\ReponseUserQuizType;
use App\Repository\ReponseUserQuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reponse/user/quiz')]
final class ReponseUserQuizController extends AbstractController
{
    #[Route(name: 'app_reponse_user_quiz_index', methods: ['GET'])]
    public function index(ReponseUserQuizRepository $reponseUserQuizRepository): Response
    {
        return $this->render('reponse_user_quiz/index.html.twig', [
            'reponse_user_quizzes' => $reponseUserQuizRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_reponse_user_quiz_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reponseUserQuiz = new ReponseUserQuiz();
        $form = $this->createForm(ReponseUserQuizType::class, $reponseUserQuiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reponseUserQuiz);
            $entityManager->flush();

            return $this->redirectToRoute('app_reponse_user_quiz_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reponse_user_quiz/new.html.twig', [
            'reponse_user_quiz' => $reponseUserQuiz,
            'form' => $form,
        ]);
    }

    #[Route('/{id_rep_user}', name: 'app_reponse_user_quiz_show', methods: ['GET'])]
    public function show(ReponseUserQuiz $reponseUserQuiz): Response
    {
        return $this->render('reponse_user_quiz/show.html.twig', [
            'reponse_user_quiz' => $reponseUserQuiz,
        ]);
    }

    #[Route('/{id_rep_user}/edit', name: 'app_reponse_user_quiz_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ReponseUserQuiz $reponseUserQuiz, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReponseUserQuizType::class, $reponseUserQuiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reponse_user_quiz_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reponse_user_quiz/edit.html.twig', [
            'reponse_user_quiz' => $reponseUserQuiz,
            'form' => $form,
        ]);
    }

    #[Route('/{id_rep_user}', name: 'app_reponse_user_quiz_delete', methods: ['POST'])]
    public function delete(Request $request, ReponseUserQuiz $reponseUserQuiz, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reponseUserQuiz->getId_rep_user(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reponseUserQuiz);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reponse_user_quiz_index', [], Response::HTTP_SEE_OTHER);
    }
}
