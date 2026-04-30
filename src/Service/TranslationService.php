<?php
<<<<<<< HEAD
namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationService
{
    public function __construct(
        private readonly TranslatorInterface  $translator,
        private readonly HttpClientInterface  $httpClient,
        private readonly LoggerInterface      $logger,
    ) {}

    /**
     * Traduit du contenu dynamique (BDD) via MyMemory API (gratuit, sans clé).
     */
    public function translate(string $text, string $targetLang): string
    {
        if (empty(trim($text)) || $targetLang === 'fr') {
=======

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
>>>>>>> jeu
            return $text;
        }

        try {
            $response = $this->httpClient->request('GET', 'https://api.mymemory.translated.net/get', [
                'query' => [
<<<<<<< HEAD
                    'q'        => $text,
                    'langpair' => 'fr|' . $targetLang,
                ],
                'timeout' => 5,
=======
                    'q' => $text,
                    'langpair' => $from . '|' . $to,
                ],
>>>>>>> jeu
            ]);

            $data = $response->toArray();

<<<<<<< HEAD
            if (isset($data['responseData']['translatedText'])
                && $data['responseStatus'] === 200
            ) {
                return $data['responseData']['translatedText'];
            }

            return $text;

        } catch (\Exception $e) {
            $this->logger->error('Translation failed.', [
                'target_lang'  => $targetLang,
                'text_preview' => mb_substr($text, 0, 120),
                'exception'    => $e,
            ]);

            return $text;
        }
    }

    /**
     * Traduit une clé statique de l'UI via symfony/translation.
     */
    public function trans(string $id, array $parameters = [], ?string $locale = null): string
    {
        return $this->translator->trans($id, $parameters, 'messages', $locale);
    }
}
=======
            if (isset($data['responseData']['translatedText'])) {
                return $data['responseData']['translatedText'];
            }
        } catch (\Exception $e) {
            // Log error if needed
        }

        return $text; // Retourne le texte original en cas d'erreur
    }
}
>>>>>>> jeu
