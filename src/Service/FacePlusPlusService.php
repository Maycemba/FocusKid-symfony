<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class FacePlusPlusService
{
    private const API_URL = 'https://api-us.faceplusplus.com/facepp/v3/detect';

    // Mapping Face++ → vos noms d'émotions
    private const EMOTION_MAP = [
        'happiness' => 'joie',
        'sadness'   => 'tristesse',
        'anger'     => 'colere',
        'fear'      => 'peur',
        'disgust'   => 'degout',
        'surprise'  => 'surprise',
        'neutral'   => 'neutre',
    ];

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey,
        private string $apiSecret,
    ) {}

    /**
     * Analyse une image base64 et retourne les émotions détectées.
     *
     * @param string $imageBase64  Image en base64 (sans le préfixe data:image/...)
     * @return array{
     *   emotionDominante: string|null,
     *   emotionDominanteNormalisee: string|null,
     *   scores: array,
     *   confiance: float
     * }
     */
    public function analyserImage(string $imageBase64): array
    {
        // Supprimer le préfixe data:image si présent
        if (str_contains($imageBase64, ',')) {
            $imageBase64 = explode(',', $imageBase64)[1];
        }

        $response = $this->httpClient->request('POST', self::API_URL, [
            'body' => [
                'api_key'            => $this->apiKey,
                'api_secret'         => $this->apiSecret,
                'image_base64'       => $imageBase64,
                'return_attributes'  => 'emotion',
            ],
        ]);

        $data = $response->toArray();

        if (empty($data['faces'])) {
            return [
                'emotionDominante'           => null,
                'emotionDominanteNormalisee' => null,
                'scores'                     => [],
                'confiance'                  => 0.0,
            ];
        }

        $emotions = $data['faces'][0]['attributes']['emotion'];

        // Trouver l'émotion dominante
        $emotionDominante = array_key_first(
            array_filter($emotions, fn($v) => $v === max($emotions))
        );

        return [
            'emotionDominante'           => $emotionDominante,
            'emotionDominanteNormalisee' => self::EMOTION_MAP[$emotionDominante] ?? $emotionDominante,
            'scores'                     => $emotions,
            'confiance'                  => round($emotions[$emotionDominante], 2),
        ];
    }

    /**
     * Compare l'émotion choisie avec l'émotion détectée.
     */
    public function verifierCorrespondance(string $emotionChoisie, ?string $emotionDetecteeNormalisee): bool
    {
        if ($emotionDetecteeNormalisee === null) {
            return false;
        }

        return strtolower(trim($emotionChoisie)) === strtolower(trim($emotionDetecteeNormalisee));
    }
}