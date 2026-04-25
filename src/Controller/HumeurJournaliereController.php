<?php

namespace App\Controller;

use App\Entity\HumeurJournaliere;
use App\Form\HumeurJournaliereType;
use App\Repository\HumeurJournaliereRepository;
use App\Repository\EmotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/humeur/journaliere')]
final class HumeurJournaliereController extends AbstractController
{
    #[Route(name: 'app_humeur_journaliere_index', methods: ['GET'])]
    public function index(HumeurJournaliereRepository $repo): Response
    {
        return $this->render('humeur_journaliere/index.html.twig', [
            'humeur_journalieres' => $repo->findAll(),
            'stats_by_emotion'    => $repo->countByEmotion(),
            'stats_by_day'        => $repo->countByDay(),
        ]);
    }

    #[Route('/new', name: 'app_humeur_journaliere_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $humeurJournaliere = new HumeurJournaliere();
        $form = $this->createForm(HumeurJournaliereType::class, $humeurJournaliere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($humeurJournaliere);
            $entityManager->flush();

            $this->addFlash('success', 'Humeur journalière enregistrée avec succès !');
            return $this->redirectToRoute('app_humeur_journaliere_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('humeur_journaliere/new.html.twig', [
            'humeur_journaliere' => $humeurJournaliere,
            'form'               => $form,
        ]);
    }

    /**
     * Endpoint AJAX appelé depuis le front-office quand l'enfant clique sur une émotion
     */
    #[Route('/enregistrer/{emotionId}', name: 'app_humeur_enregistrer', methods: ['POST'])]
    public function enregistrer(int $emotionId, EmotionRepository $emotionRepo, EntityManagerInterface $em): JsonResponse
    {
        $emotion = $emotionRepo->find($emotionId);

        if (!$emotion) {
            return new JsonResponse(['success' => false, 'message' => 'Émotion introuvable'], 404);
        }

        $humeur = new HumeurJournaliere();
        $humeur->setEmotion($emotion);
        $humeur->setDateHeure(new \DateTime());

        $em->persist($humeur);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Humeur enregistrée : ' . $emotion->getNom(),
        ]);
    }

    #[Route('/{id}', name: 'app_humeur_journaliere_show', methods: ['GET'])]
    public function show(HumeurJournaliere $humeurJournaliere): Response
    {
        return $this->render('humeur_journaliere/show.html.twig', [
            'humeur_journaliere' => $humeurJournaliere,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_humeur_journaliere_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, HumeurJournaliere $humeurJournaliere, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HumeurJournaliereType::class, $humeurJournaliere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Humeur journalière modifiée avec succès !');
            return $this->redirectToRoute('app_humeur_journaliere_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('humeur_journaliere/edit.html.twig', [
            'humeur_journaliere' => $humeurJournaliere,
            'form'               => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_humeur_journaliere_delete', methods: ['POST'])]
    public function delete(Request $request, HumeurJournaliere $humeurJournaliere, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $humeurJournaliere->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($humeurJournaliere);
            $entityManager->flush();
            $this->addFlash('success', 'Humeur journalière supprimée avec succès !');
        }

        return $this->redirectToRoute('app_humeur_journaliere_index', [], Response::HTTP_SEE_OTHER);
    }
}