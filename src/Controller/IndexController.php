<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    #[IsGranted('PUBLIC_ACCESS')]
    public function index(): Response
    {
        // Si l'utilisateur est connecté, rediriger vers son tableau de bord
        if ($this->getUser()) {
            $user = $this->getUser();
            $roles = $user->getRoles();
            
            if (in_array('ROLE_ADMIN', $roles)) {
                return $this->redirectToRoute('admin_dashboard'); // Adaptez selon votre route admin
            } elseif (in_array('ROLE_PARENT', $roles)) {
                return $this->redirectToRoute('parent_dashboard'); // Adaptez selon votre route parent
            } elseif (in_array('ROLE_ENFANT', $roles)) {
                return $this->redirectToRoute('enfant_dashboard'); // Adaptez selon votre route enfant
            }
        }
        
        // Page d'accueil publique
        return $this->render('index.html.twig');
    }
}