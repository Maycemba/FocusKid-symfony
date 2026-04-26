<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class AnthropicProxyController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $anthropicApiKey,
        private LoggerInterface $logger
    ) {}

    #[Route('/api/anthropic/proxy', name: 'anthropic_proxy', methods: ['POST', 'OPTIONS'])]
    public function proxy(Request $request): Response
    {
        // Gestion CORS pour OPTIONS
        if ($request->getMethod() === 'OPTIONS') {
            return new Response('', 200, [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'POST, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
                'Access-Control-Max-Age' => '86400',
            ]);
        }

        try {
            // Log la requête
            $this->logger->info('Anthropic proxy request received', [
                'content_type' => $request->getContentType(),
                'content_length' => strlen($request->getContent()) // ✅ CORRECTION
            ]);

            // Vérifier si la clé API est définie
            if (empty($this->anthropicApiKey)) {
                $this->logger->error('ANTHROPIC_API_KEY is not set');
                return $this->json(['error' => 'API key not configured'], 500, [
                    'Access-Control-Allow-Origin' => '*'
                ]);
            }

            // Récupérer les données de la requête
            $content = $request->getContent();
            $this->logger->debug('Request content: ' . $content);
            
            $data = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->error('Invalid JSON: ' . json_last_error_msg());
                return $this->json(['error' => 'Invalid JSON: ' . json_last_error_msg()], 400, [
                    'Access-Control-Allow-Origin' => '*'
                ]);
            }

            // ✅ Vérifier que le modèle est présent
            if (!isset($data['model'])) {
                $this->logger->error('Missing model in request');
                return $this->json(['error' => 'Missing "model" field in request. Use "claude-3-sonnet-20241022"'], 400, [
                    'Access-Control-Allow-Origin' => '*'
                ]);
            }

            $this->logger->info('Calling Anthropic API', ['model' => $data['model']]);

            // Appel à l'API Anthropic
            $response = $this->httpClient->request('POST', 'https://api.anthropic.com/v1/messages', [
                'headers' => [
                    'x-api-key' => $this->anthropicApiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ],
                'json' => $data,
                'timeout' => 30,
            ]);

            $statusCode = $response->getStatusCode();
            $responseContent = $response->getContent(false);
            
            $this->logger->info('Anthropic API response', ['status_code' => $statusCode]);

            return new Response($responseContent, $statusCode, [
                'Content-Type' => 'application/json',
                'Access-Control-Allow-Origin' => '*',
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Anthropic proxy error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->json([
                'error' => $e->getMessage(),
                'type' => get_class($e)
            ], 500, [
                'Access-Control-Allow-Origin' => '*'
            ]);
        }
    }

    #[Route('/api/anthropic/test', name: 'anthropic_test', methods: ['GET'])]
    public function test(): Response
    {
        return $this->json([
            'status' => 'Controller works',
            'api_key_configured' => !empty($this->anthropicApiKey),
            'api_key_length' => strlen($this->anthropicApiKey ?? ''),
        ], 200, [
            'Access-Control-Allow-Origin' => '*'
        ]);
    }
}