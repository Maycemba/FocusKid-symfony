<?php

namespace App\Controller;

use App\Entity\ExerciceEnfant;
use App\Form\ExerciceEnfantType;
use App\Repository\ExerciceEnfantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/exercice/enfant')]
final class ExerciceEnfantController extends AbstractController
{
    #[Route(name: 'app_exercice_enfant_index', methods: ['GET'])]
    public function index(ExerciceEnfantRepository $exerciceEnfantRepository): Response
    {
        return $this->render('exercice_enfant/index.html.twig', [
            'exercice_enfants' => $exerciceEnfantRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_exercice_enfant_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $exerciceEnfant = new ExerciceEnfant();
        $form = $this->createForm(ExerciceEnfantType::class, $exerciceEnfant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($exerciceEnfant);
            $entityManager->flush();

            return $this->redirectToRoute('app_exercice_enfant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('exercice_enfant/new.html.twig', [
            'exercice_enfant' => $exerciceEnfant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_exercice_enfant_show', methods: ['GET'])]
    public function show(ExerciceEnfant $exerciceEnfant): Response
    {
        return $this->render('exercice_enfant/show.html.twig', [
            'exercice_enfant' => $exerciceEnfant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_exercice_enfant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ExerciceEnfant $exerciceEnfant, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ExerciceEnfantType::class, $exerciceEnfant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_exercice_enfant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('exercice_enfant/edit.html.twig', [
            'exercice_enfant' => $exerciceEnfant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_exercice_enfant_delete', methods: ['POST'])]
    public function delete(Request $request, ExerciceEnfant $exerciceEnfant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$exerciceEnfant->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($exerciceEnfant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_exercice_enfant_index', [], Response::HTTP_SEE_OTHER);
    }
}
