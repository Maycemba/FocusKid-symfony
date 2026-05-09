<?php

namespace App\Controller;

use App\Entity\Scenario;
use App\Form\ScenarioType;
use App\Repository\ScenarioRepository;
use App\Repository\EmotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/scenario')]
final class ScenarioController extends AbstractController
{
    #[Route(name: 'app_scenario_index', methods: ['GET'])]
    public function index(Request $request, ScenarioRepository $scenarioRepository, EmotionRepository $emotionRepository, PaginatorInterface $paginator): Response
    {
        $search    = $request->query->get('search', '');
        $emotionId = $request->query->get('emotion_id') ? (int) $request->query->get('emotion_id') : null;

        // ✅ findWithFiltersQuery (pas findWithFilters)
        $query    = $scenarioRepository->findWithFiltersQuery($search ?: null, $emotionId);
        $emotions = $emotionRepository->findAll();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('scenario/index.html.twig', [
            'scenarios'        => $pagination,
            'emotions'         => $emotions,
            'search'           => $search,
            'selected_emotion' => $emotionId,
        ]);
    }

    #[Route('/new', name: 'app_scenario_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $scenario = new Scenario();
        $form = $this->createForm(ScenarioType::class, $scenario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('animation')->getData();
            if ($file) {
                // ✅ Stockage avec data URI complet
                $mimeType = $file->getMimeType();
                $base64   = base64_encode(file_get_contents($file->getPathname()));
                $scenario->setAnimation('data:' . $mimeType . ';base64,' . $base64);
            }
            $entityManager->persist($scenario);
            $entityManager->flush();

            $this->addFlash('success', 'Scénario créé avec succès !');
            return $this->redirectToRoute('app_scenario_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('scenario/new.html.twig', [
            'scenario' => $scenario,
            'form'     => $form,
        ]);
    }



    #[Route('/{id}', name: 'app_scenario_show', methods: ['GET'])]
    public function show(Scenario $scenario): Response
    {
        return $this->render('scenario/show.html.twig', [
            'scenario' => $scenario,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_scenario_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Scenario $scenario, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ScenarioType::class, $scenario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('animation')->getData();
            if ($file) {
                // ✅ Même correction que new() — data URI complet
                $mimeType = $file->getMimeType();
                $base64   = base64_encode(file_get_contents($file->getPathname()));
                $scenario->setAnimation('data:' . $mimeType . ';base64,' . $base64);
            }
            $entityManager->flush();

            $this->addFlash('success', 'Scénario modifié avec succès !');
            return $this->redirectToRoute('app_scenario_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('scenario/edit.html.twig', [
            'scenario' => $scenario,
            'form'     => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_scenario_delete', methods: ['POST'])]
    public function delete(Request $request, Scenario $scenario, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $scenario->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($scenario);
            $entityManager->flush();
            $this->addFlash('success', 'Scénario supprimé avec succès !');
        }

        return $this->redirectToRoute('app_scenario_index', [], Response::HTTP_SEE_OTHER);
    }

}