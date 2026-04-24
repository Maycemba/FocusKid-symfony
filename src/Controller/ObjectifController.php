<?php

namespace App\Controller;

use App\Entity\Objectif;
use App\Repository\ObjectifRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/objectifs')]
class ObjectifController extends AbstractController
{
    // Afficher la page principale
    #[Route('/', name: 'objectif_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('carnet_educatif/indexObjectif.html.twig');
    }

    // API: Créer un objectif
    #[Route('/api', name: 'objectif_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $objectif = new Objectif();
        $objectif->setDescription($data['description'] ?? '');
        $objectif->setDateDebut(new \DateTime($data['date_debut'] ?? 'now'));
        $objectif->setDateFin(new \DateTime($data['date_fin'] ?? 'now'));
        
        $errors = $validator->validate($objectif);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }
        
        $em->persist($objectif);
        $em->flush();
        
        return $this->json([
            'message' => 'Objectif créé avec succès',
            'id' => $objectif->getId()
        ], 201);
    }

    // API: Lister tous les objectifs
    #[Route('/api', name: 'objectif_list', methods: ['GET'])]
    public function list(ObjectifRepository $repository): JsonResponse
    {
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
    }

    // API: Mettre à jour le statut d'un objectif
    #[Route('/api/{id}/statut', name: 'objectif_update_statut', methods: ['PUT'])]
    public function updateStatut(int $id, Request $request, EntityManagerInterface $em, ObjectifRepository $repository): JsonResponse
    {
        $objectif = $repository->find($id);
        if (!$objectif) {
            return $this->json(['error' => 'Objectif non trouvé'], 404);
        }
        
        $data = json_decode($request->getContent(), true);
        $nouveauStatut = $data['statut'] ?? null;
        
        if (!in_array($nouveauStatut, [Objectif::STATUT_ATTEINT, Objectif::STATUT_NON_ATTEINT])) {
            return $this->json(['error' => 'Statut invalide'], 400);
        }
        
        $objectif->setStatut($nouveauStatut);
        $em->flush();
        
        return $this->json([
            'message' => 'Statut mis à jour avec succès',
            'statut' => $objectif->getStatut(),
            'statut_label' => $objectif->getStatutLabel()
        ]);
    }

    // API: Supprimer un objectif
    #[Route('/api/{id}', name: 'objectif_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em, ObjectifRepository $repository): JsonResponse
    {
        $objectif = $repository->find($id);
        if (!$objectif) {
            return $this->json(['error' => 'Objectif non trouvé'], 404);
        }
        
        $em->remove($objectif);
        $em->flush();
        
        return $this->json(['message' => 'Objectif supprimé avec succès']);
    }

    // API: Statistiques des objectifs
    #[Route('/api/stats', name: 'objectif_stats', methods: ['GET'])]
    public function stats(ObjectifRepository $repository): JsonResponse
    {
        $stats = $repository->countByStatut();
        
        return $this->json([
            'total' => $stats['total'],
            'en_cours' => $stats['en_cours'],
            'atteint' => $stats['atteint'],
            'non_atteint' => $stats['non_atteint'],
            'taux_reussite' => $stats['total'] > 0 ? round(($stats['atteint'] / $stats['total']) * 100, 2) : 0
        ]);
    }
}