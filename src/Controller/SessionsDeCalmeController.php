<?php

namespace App\Controller;

use App\Entity\SessionsDeCalme;
use App\Form\SessionsDeCalmeType;
use App\Repository\SessionsDeCalmeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sessions/de/calme')]
final class SessionsDeCalmeController extends AbstractController  // Changé: extends AbstractController au lieu de BaseController
{


   #[Route(name: 'app_sessions_de_calme_index', methods: ['GET'])]
    public function index(SessionsDeCalmeRepository $sessionsDeCalmeRepository): Response
    {
        return $this->render('sessions_de_calme/index.html.twig', [
            'sessions_de_calmes' => $sessionsDeCalmeRepository->findAll(),
            // AJOUT OBLIGATOIRE - Données du carousel
            'carousel_slides' => [
                [
                    'image' => 'base-front/img/carousel-1.jpg',
                    'title' => 'The Best Kindergarten School For Your Child',
                    'description' => 'Vero elitr justo clita lorem. Ipsum dolor at sed stet sit diam no. Kasd rebum ipsum et diam justo clita et kasd rebum sea elitr.',
                    'learn_more_link' => '#',
                    'classes_link' => '#'
                ],
                [
                    'image' => 'base-front/img/carousel-2.jpg',
                    'title' => 'Make A Brighter Future For Your Child',
                    'description' => 'Vero elitr justo clita lorem. Ipsum dolor at sed stet sit diam no. Kasd rebum ipsum et diam justo clita et kasd rebum sea elitr.',
                    'learn_more_link' => '#',
                    'classes_link' => '#'
                ]
            ]
        ]);
    }
    #[Route('/new', name: 'app_sessions_de_calme_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sessionsDeCalme = new SessionsDeCalme();
        $form = $this->createForm(SessionsDeCalmeType::class, $sessionsDeCalme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sessionsDeCalme);
            $entityManager->flush();

            return $this->redirectToRoute('app_sessions_de_calme_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sessions_de_calme/new.html.twig', [
            'sessions_de_calme' => $sessionsDeCalme,
            'form' => $form,
            // AJOUTEZ AUSSI ICI
            'carousel_slides' => [
                [
                    'image' => 'base-front/img/carousel-1.jpg',
                    'title' => 'The Best Kindergarten School For Your Child',
                    'description' => 'Vero elitr justo clita lorem.',
                    'learn_more_link' => '#',
                    'classes_link' => '#'
                ]
            ]
        ]);
    }

    #[Route('/{id}', name: 'app_sessions_de_calme_show', methods: ['GET'])]
    public function show(SessionsDeCalme $sessionsDeCalme): Response
    {
        return $this->render('sessions_de_calme/show.html.twig', [
            'sessions_de_calme' => $sessionsDeCalme,
            // AJOUTEZ AUSSI ICI
            'carousel_slides' => [
                [
                    'image' => 'base-front/img/carousel-1.jpg',
                    'title' => 'The Best Kindergarten School For Your Child',
                    'description' => 'Vero elitr justo clita lorem.',
                    'learn_more_link' => '#',
                    'classes_link' => '#'
                ]
            ]
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sessions_de_calme_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SessionsDeCalme $sessionsDeCalme, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SessionsDeCalmeType::class, $sessionsDeCalme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_sessions_de_calme_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sessions_de_calme/edit.html.twig', [
            'sessions_de_calme' => $sessionsDeCalme,
            'form' => $form,
            // AJOUTEZ AUSSI ICI
            'carousel_slides' => [
                [
                    'image' => 'base-front/img/carousel-1.jpg',
                    'title' => 'The Best Kindergarten School For Your Child',
                    'description' => 'Vero elitr justo clita lorem.',
                    'learn_more_link' => '#',
                    'classes_link' => '#'
                ]
            ]
        ]);
    }
 #[Route('/{id}/delete', name: 'app_sessions_de_calme_delete', methods: ['POST'])]
    public function delete(Request $request, SessionsDeCalme $sessionsDeCalme, EntityManagerInterface $entityManager): Response
    {
        // Vérifier le token CSRF
        if ($this->isCsrfTokenValid('delete' . $sessionsDeCalme->getId(), $request->request->get('_token'))) {
            
            try {
                // Supprimer l'entité
                $entityManager->remove($sessionsDeCalme);
                $entityManager->flush();
                
                // Ajouter un message de succès
                $this->addFlash('success', 'La session a été supprimée avec succès !');
                
            } catch (\Exception $e) {
                // En cas d'erreur
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression : ' . $e->getMessage());
            }
        } else {
            // Token CSRF invalide
            $this->addFlash('error', 'Token CSRF invalide. Veuillez réessayer.');
        }
        
        // Rediriger vers la liste
        return $this->redirectToRoute('app_sessions_de_calme_index', [], Response::HTTP_SEE_OTHER);
    }
}