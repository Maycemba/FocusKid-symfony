<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class EmotionApiService
{
    private const API_BASE = 'http://localhost:5000';

    public function __construct(private HttpClientInterface $client) {}

    public function generateQuiz(): array
    {
        try {
            $response = $this->client->request('GET', self::API_BASE . '/quiz/generate');
            $data = $response->toArray();
            
            if (isset($data['success']) && $data['success'] === true) {
                return $data['questions'];
            }
            
            return [];
        } catch (\Exception $e) {
            throw new \Exception('API Python inaccessible: ' . $e->getMessage());
        }
    }

    public function analyzeAnswers(string $childId, array $answers): array
    {
        try {
            $response = $this->client->request('POST', self::API_BASE . '/analyze', [
                'json' => [
                    'child_id' => $childId,
                    'answers' => $answers
                ]
            ]);
            
            return $response->toArray();
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de l\'analyse: ' . $e->getMessage());
        }
    }

    public function checkHealth(): bool
    {
        try {
            $response = $this->client->request('GET', self::API_BASE . '/health');
            $data = $response->toArray();
            return isset($data['status']) && $data['status'] === 'ok';
        } catch (\Exception $e) {
            return false;
        }
    }
}