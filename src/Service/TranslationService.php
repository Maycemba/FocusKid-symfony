<?php
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
            return $text;
        }

        try {
            $response = $this->httpClient->request('GET', 'https://api.mymemory.translated.net/get', [
                'query' => [
                    'q'        => $text,
                    'langpair' => 'fr|' . $targetLang,
                ],
                'timeout' => 5,
            ]);

            $data = $response->toArray();

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
