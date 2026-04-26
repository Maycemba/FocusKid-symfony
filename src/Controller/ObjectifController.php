<?php

namespace App\Controller;

use App\Entity\Objectif;
use App\Repository\ObjectifRepository;
use App\Service\WhatsAppService;
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
    #[Route('/', name: 'app_objectif_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('carnet_educatif/indexObjectif.html.twig');
    }

    #[Route('/api', name: 'app_objectif_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);
            
            $description = trim($data['description'] ?? '');
            $dateDebut = $data['date_debut'] ?? null;
            $dateFin = $data['date_fin'] ?? null;

            if (!$description || !$dateDebut || !$dateFin) {
                return $this->json(['error' => 'Tous les champs sont obligatoires'], 400);
            }

            $objectif = new Objectif();
            $objectif->setDescription($description);
            $objectif->setDateDebut(new \DateTime($dateDebut));
            $objectif->setDateFin(new \DateTime($dateFin));
            $objectif->setStatut(Objectif::STATUT_EN_COURS);

            $em->persist($objectif);
            $em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Objectif créé avec succès',
                'id' => $objectif->getId()
            ], 201);

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api', name: 'app_objectif_list', methods: ['GET'])]
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
                    'progression' => $o->getProgression(),
                    'jours_restants' => $o->getJoursRestants()
                ];
            }, $objectifs);
            
            return $this->json($data);
            
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/{id}/statut', name: 'app_objectif_update_statut', methods: ['PUT'])]
    public function updateStatut(
        int $id, 
        Request $request, 
        EntityManagerInterface $em, 
        ObjectifRepository $repository,
        WhatsAppService $whatsAppService
    ): JsonResponse {
        try {
            $objectif = $repository->find($id);
            if (!$objectif) {
                return $this->json(['error' => 'Objectif non trouvé'], 404);
            }
            
            $data = json_decode($request->getContent(), true);
            $nouveauStatut = $data['statut'] ?? null;
            $numeroWhatsApp = $data['numero_whatsapp'] ?? '+21658397936';
            
            if (!in_array($nouveauStatut, ['ATTEINT', 'NON_ATTEINT'])) {
                return $this->json(['error' => 'Statut invalide'], 400);
            }
            
            $objectif->setStatut($nouveauStatut);
            $em->flush();
            
            // Générer le message WhatsApp stylisé
            $estAtteint = $nouveauStatut === 'ATTEINT';
            
            if ($estAtteint) {
                $messageTexte = "🎉 FÉLICITATIONS ! 🎉\n\n";
                $messageTexte .= "Objectif ATTEINT avec succès !\n\n";
                $messageTexte .= "📝 " . $objectif->getDescription() . "\n";
                $messageTexte .= "📅 Du " . $objectif->getDateDebut()->format('d/m/Y') . " au " . $objectif->getDateFin()->format('d/m/Y') . "\n";
                $messageTexte .= "📊 Progression: " . $objectif->getProgression() . "%\n\n";
                $messageTexte .= "⭐ Bravo ! Continuez sur cette lancée ! 💪";
            } else {
                $messageTexte = "⚠️ OBJECTIF NON ATTEINT ⚠️\n\n";
                $messageTexte .= "📝 " . $objectif->getDescription() . "\n";
                $messageTexte .= "📅 Du " . $objectif->getDateDebut()->format('d/m/Y') . " au " . $objectif->getDateFin()->format('d/m/Y') . "\n";
                $messageTexte .= "📊 Progression: " . $objectif->getProgression() . "%\n\n";
                $messageTexte .= "💪 Ne vous découragez pas !\n";
                $messageTexte .= "Chaque effort compte. Réessayez ! 🌟";
            }
            
            // ENVOI RÉEL VIA WHATSAPP (décommentez pour activer)
            /*
            try {
                $whatsappResult = $whatsAppService->sendMessage($numeroWhatsApp, $messageTexte);
                $whatsappSent = true;
            } catch (\Exception $e) {
                $whatsappSent = false;
                $whatsappError = $e->getMessage();
            }
            */
            
            // Sauvegarde du message dans un fichier (mode test)
            $logDir = __DIR__ . '/../../var/logs/whatsapp/';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }
            $filename = $logDir . 'whatsapp_messages_' . date('Y-m-d') . '.txt';
            $logEntry = "[" . date('Y-m-d H:i:s') . "] Numéro: {$numeroWhatsApp}\n";
            $logEntry .= "Message:\n{$messageTexte}\n";
            $logEntry .= str_repeat("-", 50) . "\n\n";
            file_put_contents($filename, $logEntry, FILE_APPEND);
            
            // Générer le HTML du message WhatsApp
            $htmlMessage = $this->renderView('carnet_educatif/whatsapp_notification.html.twig', [
                'message' => nl2br(htmlspecialchars($messageTexte))
            ]);
            
            return $this->json([
                'success' => true,
                'statut' => $nouveauStatut,
                'statut_label' => $objectif->getStatutLabel(),
                'whatsapp' => [
                    'message' => $messageTexte,
                    'html' => $htmlMessage,
                    'numero' => $numeroWhatsApp,
                    'sauvegarde_dans' => $filename
                    // 'envoye' => $whatsappSent ?? false,
                    // 'erreur' => $whatsappError ?? null
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/api/{id}', name: 'app_objectif_delete', methods: ['DELETE'])]
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

    #[Route('/api/stats', name: 'app_objectif_stats', methods: ['GET'])]
    public function stats(ObjectifRepository $repository): JsonResponse
    {
        try {
            $stats = $repository->countByStatut();
            
            return $this->json([
                'total' => $stats['total'],
                'en_cours' => $stats['en_cours'],
                'atteint' => $stats['atteint'],
                'non_atteint' => $stats['non_atteint']
            ]);
            
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}