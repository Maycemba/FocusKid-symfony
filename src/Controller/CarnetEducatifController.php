<?php
// src/Controller/CarnetEducatifController.php

namespace App\Controller;

use App\Entity\CarnetEducatif;
use App\Form\CarnetEducatifType;
use App\Repository\CarnetEducatifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('carnet_educatif')]
final class CarnetEducatifController extends AbstractController
{
    #[Route('/', name: 'app_carnet_educatif_index', methods: ['GET'])]
    public function index(CarnetEducatifRepository $carnetEducatifRepository): Response
    {
        return $this->render('carnet_educatif/index.html.twig', [
            'carnet_educatifs' => $carnetEducatifRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_carnet_educatif_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $carnetEducatif = new CarnetEducatif();
        $form = $this->createForm(CarnetEducatifType::class, $carnetEducatif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // La durée a été envoyée par le formulaire (champ readonly)
            // Mais on recalcule pour être sûr (évite la triche)
            $carnetEducatif->calculateDureeTotale();
            $entityManager->persist($carnetEducatif);
            $entityManager->flush();

            $this->addFlash('success', 'Carnet créé avec succès.');
            return $this->redirectToRoute('app_carnet_educatif_index');
        }

        return $this->render('carnet_educatif/new.html.twig', [
            'carnet_educatif' => $carnetEducatif,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_carnet_educatif_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CarnetEducatif $carnetEducatif, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CarnetEducatifType::class, $carnetEducatif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $carnetEducatif->calculateDureeTotale();
            $entityManager->flush();

            $this->addFlash('success', 'Carnet modifié.');
            return $this->redirectToRoute('app_carnet_educatif_index');
        }

        return $this->render('carnet_educatif/edit.html.twig', [
            'carnet_educatif' => $carnetEducatif,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_carnet_educatif_show', methods: ['GET'])]
    public function show(CarnetEducatif $carnetEducatif): Response
    {
        return $this->render('carnet_educatif/show.html.twig', [
            'carnet_educatif' => $carnetEducatif,
        ]);
    }

    #[Route('/{id}', name: 'app_carnet_educatif_delete', methods: ['POST'])]
    public function delete(Request $request, CarnetEducatif $carnetEducatif, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$carnetEducatif->getId(), $request->request->get('_token'))) {
            $entityManager->remove($carnetEducatif);
            $entityManager->flush();
            $this->addFlash('success', 'Carnet supprimé.');
        }
        return $this->redirectToRoute('app_carnet_educatif_index');
    }
}