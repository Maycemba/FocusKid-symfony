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
            
            $photoFile = $form->get('photoFile')->getData();
            
            if ($photoFile) {
                $photoContent = file_get_contents($photoFile->getPathname());
                $photoBase64 = base64_encode($photoContent);
                $emotion->setPhoto($photoBase64);
            }
            
            $entityManager->persist($emotion);
            $entityManager->flush();

            $this->addFlash('success', 'Émotion créée avec succès !');
            return $this->redirectToRoute('app_emotion_index');
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
        // Créer une copie de l'ancienne photo
        $oldPhoto = $emotion->getPhoto();
        
        $form = $this->createForm(EmotionType::class, $emotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $photoFile = $form->get('photoFile')->getData();
            
            if ($photoFile) {
                // Nouvelle image uploadée
                $photoContent = file_get_contents($photoFile->getPathname());
                $photoBase64 = base64_encode($photoContent);
                $emotion->setPhoto($photoBase64);
            } else {
                // Garder l'ancienne photo si aucune nouvelle n'est uploadée
                $emotion->setPhoto($oldPhoto);
            }
            
            $entityManager->flush();

            $this->addFlash('success', 'Émotion modifiée avec succès !');
            return $this->redirectToRoute('app_emotion_index');
        }

        return $this->render('emotion/edit.html.twig', [
            'emotion' => $emotion,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete-photo', name: 'app_emotion_delete_photo', methods: ['POST'])]
    public function deletePhoto(Request $request, Emotion $emotion, EntityManagerInterface $entityManager): Response
    {
        $token = $request->request->get('_token');
        
        if ($this->isCsrfTokenValid('delete-photo' . $emotion->getId(), $token)) {
            $emotion->setPhoto(null);
            $entityManager->flush();
            $this->addFlash('success', 'Image supprimée avec succès !');
        }
        
        return $this->redirectToRoute('app_emotion_edit', ['id' => $emotion->getId()]);
    }

    #[Route('/{id}/delete', name: 'app_emotion_delete', methods: ['POST'])]
    public function delete(Request $request, Emotion $emotion, EntityManagerInterface $entityManager): Response
    {
        $token = $request->request->get('_token');
        
        if ($this->isCsrfTokenValid('delete' . $emotion->getId(), $token)) {
            $entityManager->remove($emotion);
            $entityManager->flush();
            $this->addFlash('success', 'Émotion supprimée avec succès !');
        }

        return $this->redirectToRoute('app_emotion_index');
    }
}