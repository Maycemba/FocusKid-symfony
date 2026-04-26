<?php

namespace App\Controller;

use App\Entity\HumeurJournaliere;
use App\Form\HumeurJournaliereType;
use App\Repository\HumeurJournaliereRepository;
use App\Repository\EmotionRepository;
use App\Service\HumeurExportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\DetectionEmotion;
use App\Service\FacePlusPlusService;
use App\Entity\Emotion;

#[Route('/humeur/journaliere')]
final class HumeurJournaliereController extends AbstractController
{
    #[Route(name: 'app_humeur_journaliere_index', methods: ['GET'])]
    public function index(HumeurJournaliereRepository $repo, EntityManagerInterface $entityManager): Response
    {
        $humeurs = $entityManager->getRepository(HumeurJournaliere::class)
            ->findBy([], ['dateHeure' => 'DESC']);

        $detections = [];
        foreach ($humeurs as $humeur) {
            $detection = $entityManager
                ->getRepository(DetectionEmotion::class)
                ->findByHumeur($humeur->getId());
            $detections[$humeur->getId()] = $detection;
        }

        return $this->render('humeur_journaliere/index.html.twig', [
            'humeur_journalieres' => $repo->findAll(),
            'stats_by_emotion'    => $repo->countByEmotion(),
            'stats_by_day'        => $repo->countByDay(),
            'detections'          => $detections,
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

    #[Route('/export/excel', name: 'app_humeur_export_excel', methods: ['GET', 'POST'])]
    public function exportExcel(Request $request, HumeurExportService $exportService): StreamedResponse
    {
        $dateDebut = $request->get('date_debut') ?: null;
        $dateFin   = $request->get('date_fin')   ?: null;
        $emotion   = $request->get('emotion')    ?: null;

        return $exportService->exportExcel($dateDebut, $dateFin, $emotion);
    }

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
            'success'  => true,
            'message'  => 'Humeur enregistrée : ' . $emotion->getNom(),
            'humeurId' => $humeur->getId(),
        ]);
    }

    // IMPORTANT : webcam et detecter AVANT /{id} pour éviter les conflits de routing
    #[Route('/webcam', name: 'humeur_journaliere_webcam', methods: ['GET'])]
    public function webcam(EntityManagerInterface $entityManager): Response
    {
        $emotions = $entityManager->getRepository(Emotion::class)->findAll();

        return $this->render('humeur_journaliere/webcam.html.twig', [
            'emotions' => $emotions,
        ]);
    }

    #[Route('/detecter/{humeurId}', name: 'humeur_journaliere_detecter', methods: ['POST'])]
    public function detecterEtEnregistrer(
        int $humeurId,
        Request $request,
        FacePlusPlusService $facePlusPlusService,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $humeur = $entityManager->getRepository(HumeurJournaliere::class)->find($humeurId);

        if (!$humeur) {
            return $this->json(['error' => 'Humeur introuvable'], 404);
        }

        $body           = json_decode($request->getContent(), true);
        $photoBase64    = $body['photo']          ?? null;
        $emotionChoisie = $body['emotionChoisie'] ?? null;

        if (!$photoBase64 || !$emotionChoisie) {
            return $this->json(['error' => 'Données manquantes'], 400);
        }

        $resultat = $facePlusPlusService->analyserImage($photoBase64);

        $correspondance = $facePlusPlusService->verifierCorrespondance(
            $emotionChoisie,
            $resultat['emotionDominanteNormalisee']
        );

        $detection = new DetectionEmotion();
        $detection->setHumeurJournaliere($humeur);
        $detection->setEmotionChoisie($emotionChoisie);
        $detection->setEmotionDetectee($resultat['emotionDominanteNormalisee']);
        $detection->setScoresDetection($resultat['scores']);
        $detection->setPhotoCapture($photoBase64);
        $detection->setCorrespondance($correspondance);
        $detection->setScoreConfiance($resultat['confiance']);

        $entityManager->persist($detection);
        $entityManager->flush();

        return $this->json([
            'success'         => true,
            'correspondance'  => $correspondance,
            'emotionDetectee' => $resultat['emotionDominanteNormalisee'],
            'confiance'       => $resultat['confiance'],
            'detectionId'     => $detection->getId(),
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