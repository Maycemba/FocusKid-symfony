<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IAPlannerController extends AbstractController
{
    #[Route('/ia/planner', name: 'app_ia_planner')]
    public function index(HttpClientInterface $httpClient): Response
    {
        // Vérifier si l'API IA est disponible
        $iaAvailable = false;
        try {
            $response = $httpClient->request('GET', 'http://localhost:5000/api/health', [
                'timeout' => 3
            ]);
            $iaAvailable = $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            $iaAvailable = false;
        }
        
        return $this->render('ia_planner/index.html.twig', [
            'ia_available' => $iaAvailable,
            'api_url' => 'http://localhost:5000/api'
        ]);
    }
}