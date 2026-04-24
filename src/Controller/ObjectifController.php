<?php

namespace App\Controller;

use App\Entity\Objectif;
use App\Repository\ObjectifRepository;
use App\Service\MailtrapApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/objectifs', name: 'objectif_')]
class ObjectifController extends AbstractController
{
    // Afficher la page principale
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('carnet_educatif/indexObjectif.html.twig');
    }

   #[Route('/api', name: 'create', methods: ['POST'])]
public function create(
    Request $request,
    EntityManagerInterface $em,
    ValidatorInterface $validator
): JsonResponse {
    try {
        $content = $request->getContent();

        if (empty($content)) {
            return $this->json([
                'error' => 'Le corps de la requête est vide'
            ], 400);
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json([
                'error' => 'JSON invalide : ' . json_last_error_msg(),
                'received' => $content
            ], 400);
        }

        $description = trim($data['description'] ?? '');
        $dateDebut = $data['date_debut'] ?? null;
        $dateFin = $data['date_fin'] ?? null;

        if ($description === '') {
            return $this->json([
                'error' => 'La description est obligatoire'
            ], 400);
        }

        if (!$dateDebut || !$dateFin) {
            return $this->json([
                'error' => 'Les dates sont obligatoires'
            ], 400);
        }

        $objectif = new Objectif();
        $objectif->setDescription($description);
        $objectif->setDateDebut(new \DateTime($dateDebut));
        $objectif->setDateFin(new \DateTime($dateFin));
        $objectif->setStatut(Objectif::STATUT_EN_COURS);

        $errors = $validator->validate($objectif);

        if (count($errors) > 0) {
            $messages = [];

            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }

            return $this->json([
                'errors' => $messages
            ], 400);
        }

        $em->persist($objectif);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Objectif créé avec succès',
            'id' => $objectif->getId()
        ], 201);

    } catch (\Throwable $e) {
        return $this->json([
            'error' => $e->getMessage(),
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ], 500);
    }
}

    // API: Lister tous les objectifs
    #[Route('/api', name: 'list', methods: ['GET'])]
    public function list(ObjectifRepository $repository): JsonResponse
    {
        try {
            $objectifs = $repository->findBy([], ['created_at' => 'DESC']);
            
            $data = array_map(function(Objectif $o) {
                return [
                    'id' => $o->getId(),
                    'description' => $o->getDescription(),
                    'date_debut' => $o->getDateDebut()->format('Y-m-d'),
                    'date_fin' => $o->getDateFin()->format('Y-m-d'),
                    'statut' => $o->getStatut(),
                    'statut_label' => $o->getStatutLabel(),
                    'statut_class' => $o->getStatutBadgeClass(),
                    'est_termine' => $o->estTermine(),
                    'jours_restants' => $o->getJoursRestants(),
                    'progression' => $o->getProgression()
                ];
            }, $objectifs);
            
            return $this->json($data);
            
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // API: Mettre à jour le statut
    #[Route('/api/{id}/statut', name: 'update_statut', methods: ['PUT'])]
    public function updateStatut(
        int $id, 
        Request $request, 
        EntityManagerInterface $em, 
        ObjectifRepository $repository,
        MailtrapApiService $mailtrapApiService
    ): JsonResponse {
        try {
            $objectif = $repository->find($id);
            if (!$objectif) {
                return $this->json(['error' => "Objectif ID $id non trouvé"], 404);
            }
            
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return $this->json(['error' => 'JSON invalide'], 400);
            }
            
            $nouveauStatut = $data['statut'] ?? null;
            
            if (!in_array($nouveauStatut, [Objectif::STATUT_ATTEINT, Objectif::STATUT_NON_ATTEINT])) {
                return $this->json(['error' => 'Statut invalide: ' . $nouveauStatut], 400);
            }
            
            $objectif->setStatut($nouveauStatut);
            $em->flush();
            
            $emailResult = $mailtrapApiService->envoyerNotification($objectif);
            
            return $this->json([
                'success' => true,
                'id' => $id,
                'statut' => $nouveauStatut,
                'email_envoye' => $emailResult['success'],
                'email_message' => $emailResult['message'],
                'html' => $emailResult['html'] ?? null
            ]);
            
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // API: Supprimer un objectif
    #[Route('/api/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em, ObjectifRepository $repository): JsonResponse
    {
        try {
            $objectif = $repository->find($id);
            if (!$objectif) {
                return $this->json(['error' => 'Objectif non trouvé'], 404);
            }
            
            $em->remove($objectif);
            $em->flush();
            
            return $this->json(['message' => 'Objectif supprimé avec succès']);
            
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // API: Statistiques des objectifs
    #[Route('/api/stats', name: 'stats', methods: ['GET'])]
    public function stats(ObjectifRepository $repository): JsonResponse
    {
        try {
            $stats = $repository->countByStatut();
            
            return $this->json([
                'total' => $stats['total'],
                'en_cours' => $stats['en_cours'],
                'atteint' => $stats['atteint'],
                'non_atteint' => $stats['non_atteint'],
                'taux_reussite' => $stats['total'] > 0 ? round(($stats['atteint'] / $stats['total']) * 100, 2) : 0
            ]);
            
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}