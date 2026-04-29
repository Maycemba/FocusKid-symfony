<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VoiceCommandController extends AbstractController
{
    #[Route('/voice-commander', name: 'voice_commander')]
    public function index(): Response
    {
        return $this->render('voice/index.html.twig');
    }

    #[Route('/api/process-voice-command', name: 'process_voice_command', methods: ['POST'])]
    public function processVoiceCommand(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data || !isset($data['commande'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Commande invalide'
                ]);
            }
            
            $commande = strtolower(trim($data['commande']));
            
            // Ignorer la commande ping
            if ($commande === 'ping') {
                return $this->json([
                    'success' => true,
                    'message' => 'pong'
                ]);
            }
            
            // Tableau des commandes disponibles
            $commandes = [
                'coloriage' => [
                    'keywords' => ['coloriage', 'colorier', 'dessin', 'colorie', 'coloring'],
                    'route' => 'app_activite_coloriage',
                    'message' => 'Ouverture de la session coloriage. Bon amusement !'
                ],
                'histoire' => [
                    'keywords' => ['histoire', 'raconter', 'conte', 'story', 'lecture'],
                    'route' => 'app_activite_histoire',
                    'message' => 'Ouverture de la session histoire. Préparez-vous à écouter !'
                ],
                'respiration' => [
                    'keywords' => ['respiration', 'respirer', 'calme', 'relaxation', 'meditation', 'méditation', 'souffle'],
                    'route' => 'app_activite_respiration',
                    'message' => 'Ouverture de la session respiration. Inspirez, expirez...'
                ],
                'musique' => [
                    'keywords' => ['musique', 'musical', 'chanson', 'son', 'melodie', 'mélodie', 'music'],
                    'route' => 'app_activite_musique',
                    'message' => 'Ouverture de la session musique. Laissez-vous emporter par les sons !'
                ]
            ];
            
            // Vérifier quelle commande correspond
            foreach ($commandes as $action => $config) {
                foreach ($config['keywords'] as $keyword) {
                    if (str_contains($commande, $keyword)) {
                        return $this->json([
                            'success' => true,
                            'action' => $action,
                            'redirect' => $this->generateUrl($config['route']),
                            'message' => $config['message']
                        ]);
                    }
                }
            }
            
            // Commande non reconnue
            return $this->json([
                'success' => true,
                'action' => 'unknown',
                'message' => "Je n'ai pas compris. Dites : 'Ouvrir session coloriage', 'Ouvrir session histoire', 'Ouvrir session respiration' ou 'Ouvrir session musique'"
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur serveur'
            ]);
        }
    }
}