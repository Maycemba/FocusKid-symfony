<?php

namespace App\Controller;

use App\Entity\Exercice;
use App\Entity\ExerciceEnfant;
use App\Entity\Utilisateur;
use App\Form\ExerciceType;
use App\Repository\ExerciceRepository;
use App\Service\QuestionGeneratorIA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/exercice')]
final class ExerciceController extends AbstractController
{
    #[Route(name: 'app_exercice_index', methods: ['GET'])]
    public function index(ExerciceRepository $exerciceRepository): Response
    {
        return $this->render('exercice/index.html.twig', [
            'exercices' => $exerciceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_exercice_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $exercice = new Exercice();
        $form = $this->createForm(ExerciceType::class, $exercice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($exercice);
            $entityManager->flush();
            
            // Gérer la sélection des enfants (table exercice_enfant)
            $enfantsSelectionnes = $form->get('enfantsSelectionnes')->getData();
            
            if (!$exercice->isPourTousEnfants() && $enfantsSelectionnes && count($enfantsSelectionnes) > 0) {
                foreach ($enfantsSelectionnes as $enfant) {
                    $exerciceEnfant = new ExerciceEnfant();
                    $exerciceEnfant->setExerciceId($exercice->getId());
                    $exerciceEnfant->setEnfantId($enfant->getId());
                    $exerciceEnfant->setDateAttribution(new \DateTime());
                    $entityManager->persist($exerciceEnfant);
                }
                $entityManager->flush();
            }
            
            $this->addFlash('success', 'Exercice créé avec succès !');
            return $this->redirectToRoute('app_exercice_index');
        }

        return $this->render('exercice/new.html.twig', [
            'exercice' => $exercice,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_exercice_show', methods: ['GET'])]
    public function show(Exercice $exercice): Response
    {
        return $this->render('exercice/show.html.twig', [
            'exercice' => $exercice,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_exercice_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Exercice $exercice, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ExerciceType::class, $exercice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_exercice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('exercice/edit.html.twig', [
            'exercice' => $exercice,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_exercice_delete', methods: ['POST'])]
    public function delete(Request $request, Exercice $exercice, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$exercice->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($exercice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_exercice_index', [], Response::HTTP_SEE_OTHER);
    }
   #[Route('/archive/{id}', name: 'app_exercice_archive', methods: ['POST'])]
    public function archive(Request $request, Exercice $exercice, EntityManagerInterface $entityManager): Response
    {
        // Vérifier le token CSRF
        $token = $request->request->get('_token');
        
        if ($this->isCsrfTokenValid('archive' . $exercice->getId(), $token)) {
            $exercice->setArchive(true);
            $exercice->setActif(false);
            $entityManager->flush();
            $this->addFlash('success', 'Exercice "' . $exercice->getTitre() . '" archivé avec succès!');
        } else {
            $this->addFlash('error', 'Token invalide. Veuillez réessayer.');
        }
        
        return $this->redirectToRoute('app_exercice_index');
    }

    #[Route('/restore/{id}', name: 'app_exercice_restore', methods: ['POST'])]
    public function restore(Request $request, Exercice $exercice, EntityManagerInterface $entityManager): Response
    {
        // Vérifier le token CSRF
        $token = $request->request->get('_token');
        
        if ($this->isCsrfTokenValid('restore' . $exercice->getId(), $token)) {
            $exercice->setArchive(false);
            $entityManager->flush();
            $this->addFlash('success', 'Exercice "' . $exercice->getTitre() . '" restauré avec succès!');
        } else {
            $this->addFlash('error', 'Token invalide. Veuillez réessayer.');
        }
        
        return $this->redirectToRoute('app_exercice_index');
    }
    #[Route('/api/generer-exercice-ia', name: 'api_generer_exercice_ia', methods: ['POST'])]
public function genererExerciceIA(Request $request, QuestionGeneratorIA $generatorIA): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $titre = $data['titre'] ?? '';
    $type = $data['type'] ?? 'MÉMOIRE';
    $nbQuestions = $data['nbQuestions'] ?? 5;
    
    $contenu = $generatorIA->genererExercice($titre, $type, $nbQuestions);
    
    return $this->json(['success' => true, 'contenu' => $contenu]);
}
}