<?php

namespace App\Controller;

use App\Repository\LoginAnomalyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/security', name: 'app_security_')]
class LoginAnomalyController extends AbstractController
{
    #[Route('', name: 'dashboard')]
    public function dashboard(LoginAnomalyRepository $repo): Response
    {
        return $this->render('security/dashboard.html.twig', [
            'anomalies' => $repo->findRecentAlerts(100),
            'stats'     => $repo->getStats(),
        ]);
    }
}