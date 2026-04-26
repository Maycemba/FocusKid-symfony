<?php

namespace App\Controller;

use App\Entity\Emotion;
use App\Form\EmotionType;
use App\Repository\EmotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/emotion')]
final class EmotionController extends AbstractController
{
    #[Route(name: 'app_emotion_index', methods: ['GET'])]
    public function index(Request $request, EmotionRepository $emotionRepository, PaginatorInterface $paginator): Response
    {
        $search = $request->query->get('search', '');
        $sort   = $request->query->get('sort', 'id');
        $order  = $request->query->get('order', 'ASC');

        $query = $emotionRepository->findWithFiltersQuery($search ?: null, $sort, $order);

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('emotion/index.html.twig', [
            'emotions' => $pagination,
            'search'   => $search,
            'sort'     => $sort,
            'order'    => $order,
        ]);
    }

    #[Route('/new', name: 'app_emotion_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $emotion = new Emotion();
        $form = $this->createForm(EmotionType::class, $emotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('photo')->getData();
            if ($file) {
                $mimeType = $file->getMimeType();
                $base64   = base64_encode(file_get_contents($file->getPathname()));
                $emotion->setPhoto('data:' . $mimeType . ';base64,' . $base64);
            }
            $entityManager->persist($emotion);
            $entityManager->flush();

            $this->addFlash('success', 'Émotion créée avec succès !');
            return $this->redirectToRoute('app_emotion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('emotion/new.html.twig', [
            'emotion' => $emotion,
            'form'    => $form,
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
        $form = $this->createForm(EmotionType::class, $emotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('photo')->getData();
            if ($file) {
                $mimeType = $file->getMimeType();
                $base64   = base64_encode(file_get_contents($file->getPathname()));
                $emotion->setPhoto('data:' . $mimeType . ';base64,' . $base64);
            }
            $entityManager->flush();

            $this->addFlash('success', 'Émotion modifiée avec succès !');
            return $this->redirectToRoute('app_emotion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('emotion/edit.html.twig', [
            'emotion' => $emotion,
            'form'    => $form,
        ]);
    }

#[Route('/{id}/delete', name: 'app_emotion_delete', methods: ['POST'])]
public function delete(Request $request, Emotion $emotion, EntityManagerInterface $entityManager): Response
{
    if (!$this->isCsrfTokenValid('delete' . $emotion->getId(), $request->request->get('_token'))) {
        $this->addFlash('error', 'Token CSRF invalide.');
        return $this->redirectToRoute('app_emotion_index');
    }

    $entityManager->remove($emotion);
    $entityManager->flush();

    $this->addFlash('success', 'Émotion supprimée avec succès !');
    return $this->redirectToRoute('app_emotion_index', [], Response::HTTP_SEE_OTHER);
}
}