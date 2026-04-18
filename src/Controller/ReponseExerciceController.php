<?php

namespace App\Controller;

use App\Entity\ReponseExercice;
use App\Form\ReponseExerciceType;
use App\Repository\ReponseExerciceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reponse/exercice')]
final class ReponseExerciceController extends AbstractController
{
    #[Route(name: 'app_reponse_exercice_index', methods: ['GET'])]
    public function index(ReponseExerciceRepository $reponseExerciceRepository): Response
    {
        return $this->render('reponse_exercice/index.html.twig', [
            'reponse_exercices' => $reponseExerciceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_reponse_exercice_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reponseExercice = new ReponseExercice();
        $form = $this->createForm(ReponseExerciceType::class, $reponseExercice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reponseExercice);
            $entityManager->flush();

            return $this->redirectToRoute('app_reponse_exercice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reponse_exercice/new.html.twig', [
            'reponse_exercice' => $reponseExercice,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reponse_exercice_show', methods: ['GET'])]
    public function show(ReponseExercice $reponseExercice): Response
    {
        return $this->render('reponse_exercice/show.html.twig', [
            'reponse_exercice' => $reponseExercice,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reponse_exercice_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ReponseExercice $reponseExercice, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReponseExerciceType::class, $reponseExercice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reponse_exercice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reponse_exercice/edit.html.twig', [
            'reponse_exercice' => $reponseExercice,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reponse_exercice_delete', methods: ['POST'])]
    public function delete(Request $request, ReponseExercice $reponseExercice, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reponseExercice->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reponseExercice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reponse_exercice_index', [], Response::HTTP_SEE_OTHER);
    }
    
}
