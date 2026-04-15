<?php
// src/Controller/CommentaireController.php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\CarnetEducatif;
use App\Form\CommentaireType;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/commentaire')]
final class CommentaireController extends AbstractController
{
    #[Route(name: 'app_commentaire_index', methods: ['GET'])]
    public function index(CommentaireRepository $commentaireRepository): Response
    {
        return $this->render('commentaire/index.html.twig', [
            'commentaires' => $commentaireRepository->findAll(),
        ]);
    }

    #[Route('/new/{carnetId?}', name: 'app_commentaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ?int $carnetId = null): Response
    {
        $commentaire = new Commentaire();
        $carnet = null;
        if ($carnetId) {
            $carnet = $entityManager->getRepository(CarnetEducatif::class)->find($carnetId);
            if ($carnet) {
                $commentaire->setCarnetEducatif($carnet);
            }
        }

        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer date et heure séparées
            $date = $form->get('date_seule')->getData();
            $heure = $form->get('heure_seule')->getData();

            // Validation manuelle (par sécurité)
            if (!$date || !$heure) {
                $this->addFlash('error', 'La date et l\'heure sont obligatoires.');
                return $this->render('commentaire/new.html.twig', [
                    'form' => $form->createView(),
                    'carnet' => $carnet,
                ]);
            }

            // Fusion date + heure
            $datetime = \DateTime::createFromInterface($date);
            $datetime->setTime((int)$heure->format('H'), (int)$heure->format('i'));
            $commentaire->setDateCommentaire($datetime);

            $entityManager->persist($commentaire);
            $entityManager->flush();

            $this->addFlash('success', 'Commentaire ajouté avec succès.');
            if ($carnet) {
                return $this->redirectToRoute('app_carnet_educatif_show', ['id' => $carnet->getId()]);
            }
            return $this->redirectToRoute('app_commentaire_index');
        }

        return $this->render('commentaire/new.html.twig', [
            'form' => $form->createView(),
            'carnet' => $carnet,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commentaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentaireType::class, $commentaire);
        // Pré-remplir date_seule et heure_seule à partir de date_commentaire
        if ($commentaire->getDateCommentaire()) {
            $form->get('date_seule')->setData($commentaire->getDateCommentaire());
            $form->get('heure_seule')->setData($commentaire->getDateCommentaire());
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $date = $form->get('date_seule')->getData();
            $heure = $form->get('heure_seule')->getData();

            if (!$date || !$heure) {
                $this->addFlash('error', 'La date et l\'heure sont requises.');
                return $this->render('commentaire/edit.html.twig', [
                    'commentaire' => $commentaire,
                    'form' => $form->createView(),
                ]);
            }

            $datetime = \DateTime::createFromInterface($date);
            $datetime->setTime((int)$heure->format('H'), (int)$heure->format('i'));
            $commentaire->setDateCommentaire($datetime);

            $entityManager->flush();
            $this->addFlash('success', 'Commentaire modifié.');
            return $this->redirectToRoute('app_commentaire_show', ['id' => $commentaire->getId()]);
        }

        return $this->render('commentaire/edit.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_commentaire_show', methods: ['GET'])]
    public function show(Commentaire $commentaire): Response
    {
        return $this->render('commentaire/show.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }

    #[Route('/{id}', name: 'app_commentaire_delete', methods: ['POST'])]
    public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commentaire->getId(), $request->getPayload()->getString('_token'))) {
            $carnetId = $commentaire->getCarnetEducatif()?->getId();
            $entityManager->remove($commentaire);
            $entityManager->flush();
            $this->addFlash('success', 'Commentaire supprimé.');
            if ($carnetId) {
                return $this->redirectToRoute('app_carnet_educatif_show', ['id' => $carnetId]);
            }
        }
        return $this->redirectToRoute('app_commentaire_index');
    }
}