<?php

namespace App\Controller;

use App\Entity\ReponseUserExercice;
use App\Form\ReponseUserExerciceType;
use App\Repository\ReponseUserExerciceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reponse/user/exercice')]
final class ReponseUserExerciceController extends AbstractController
{
    #[Route(name: 'app_reponse_user_exercice_index', methods: ['GET'])]
    public function index(ReponseUserExerciceRepository $reponseUserExerciceRepository): Response
    {
        return $this->render('reponse_user_exercice/index.html.twig', [
            'reponse_user_exercices' => $reponseUserExerciceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_reponse_user_exercice_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reponseUserExercice = new ReponseUserExercice();
        $form = $this->createForm(ReponseUserExerciceType::class, $reponseUserExercice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reponseUserExercice);
            $entityManager->flush();

            return $this->redirectToRoute('app_reponse_user_exercice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reponse_user_exercice/new.html.twig', [
            'reponse_user_exercice' => $reponseUserExercice,
            'form' => $form,
        ]);
    }

    #[Route('/{id_rep_user}', name: 'app_reponse_user_exercice_show', methods: ['GET'])]
    public function show(ReponseUserExercice $reponseUserExercice): Response
    {
        return $this->render('reponse_user_exercice/show.html.twig', [
            'reponse_user_exercice' => $reponseUserExercice,
        ]);
    }

    #[Route('/{id_rep_user}/edit', name: 'app_reponse_user_exercice_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ReponseUserExercice $reponseUserExercice, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReponseUserExerciceType::class, $reponseUserExercice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reponse_user_exercice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reponse_user_exercice/edit.html.twig', [
            'reponse_user_exercice' => $reponseUserExercice,
            'form' => $form,
        ]);
    }

    #[Route('/{id_rep_user}', name: 'app_reponse_user_exercice_delete', methods: ['POST'])]
    public function delete(Request $request, ReponseUserExercice $reponseUserExercice, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reponseUserExercice->getId_rep_user(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reponseUserExercice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reponse_user_exercice_index', [], Response::HTTP_SEE_OTHER);
    }
}
