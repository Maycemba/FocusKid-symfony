<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TranslationService
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Traduit un texte en utilisant l'API MyMemory (Gratuit)
     */
    public function translate(string $text, string $from = 'fr', string $to = 'en'): ?string
    {
        if (empty($text)) {
            return $text;
        }

        try {
            $response = $this->httpClient->request('GET', 'https://api.mymemory.translated.net/get', [
                'query' => [
                    'q' => $text,
                    'langpair' => $from . '|' . $to,
                ],
            ]);

            $data = $response->toArray();

            if (isset($data['responseData']['translatedText'])) {
                return $data['responseData']['translatedText'];
            }
        } catch (\Exception $e) {
            // Log error if needed
        }

        return $text; // Retourne le texte original en cas d'erreur
    }
}
