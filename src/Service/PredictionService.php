<?php
// src/Service/PredictionService.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class PredictionService
{
    private $httpClient;
    private $logger;
    private $apiUrl;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->apiUrl = 'http://localhost:5000/predict';
    }

    public function predire(int $difficulte, int $typeCode, float $scoreMoyen, int $tempsMoyen): array
    {
        try {
            $response = $this->httpClient->request('POST', $this->apiUrl, [
                'json' => [
                    'difficulte' => $difficulte,
                    'type_exercice' => $typeCode,
                    'score_moyen' => $scoreMoyen,
                    'temps_moyen' => $tempsMoyen
                ],
                'timeout' => 5
            ]);

            $data = $response->toArray();
            
            return [
                'reussite' => $data['reussite_predite'] === 1,
                'probabilite' => round($data['probabilite_reussite'], 1),
                'success' => $data['success'] ?? true
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Erreur prédiction: ' . $e->getMessage());
            
            // Fallback: prédiction basée sur le score moyen (si >= 10/20)
            $reussite = $scoreMoyen >= 10;
            
            return [
                'reussite' => $reussite,
                'probabilite' => $reussite ? 70 : 30,
                'success' => false
            ];
        }
    }

    public function getTypeCode(string $type): int
    {
        return match($type) {
            'MEMOIRE' => 1,
            'ATTENTION' => 2,
            'LOGIQUE' => 3,
            'CHRONO' => 4,
            default => 1
        };
    }
}