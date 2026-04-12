<?php

namespace App\Controller;

use App\Entity\Emotion;
use App\Form\EmotionType;
use App\Repository\EmotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/emotion')]
final class EmotionController extends AbstractController
{
    #[Route(name: 'app_emotion_index', methods: ['GET'])]
    public function index(EmotionRepository $emotionRepository): Response
    {
        return $this->render('emotion/index.html.twig', [
            'emotions' => $emotionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_emotion_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $emotion = new Emotion();
        $form = $this->createForm(EmotionType::class, $emotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($emotion);
            $entityManager->flush();

            return $this->redirectToRoute('app_emotion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('emotion/new.html.twig', [
            'emotion' => $emotion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_emotion_show', methods: ['GET'])]
    public function show(Emotion $emotion): Response
    {
        return $this->render('emotion/show.html.twig', [
            'emotion' => $emotion,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_emotion_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Emotion $emotion, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EmotionType::class, $emotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_emotion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('emotion/edit.html.twig', [
            'emotion' => $emotion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_emotion_delete', methods: ['POST'])]
    public function delete(Request $request, Emotion $emotion, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$emotion->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($emotion);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_emotion_index', [], Response::HTTP_SEE_OTHER);
    }
}
