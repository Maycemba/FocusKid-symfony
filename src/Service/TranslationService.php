<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslationService
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public function translate(string $text, string $targetLang): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        try {
            $tr = new GoogleTranslate();
            $tr->setSource('fr');
            $tr->setTarget($targetLang);
            $tr->setOptions([
                'verify' => false,
            ]);
            return $tr->translate($text);
        } catch (\Exception $e) {
            $this->logger->error('Translation failed.', [
                'target_lang' => $targetLang,
                'text_preview' => mb_substr($text, 0, 120),
                'exception' => $e,
            ]);

            return $text;
        }
    }
}
