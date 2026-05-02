<?php

namespace App\Controller;

use App\Repository\CourRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

final class FrontController extends AbstractController
{
    #[Route('/', name: 'app_home')]
#[Route('/index', name: 'app_index')]
public function index(): Response
{
    return $this->render('/index.html.twig');
}
    #[Route('/enfant', name: 'app_enfant_cours', methods: ['GET'])]
    public function listeCours(Request $request, CourRepository $courRepository, PaginatorInterface $paginator): Response
    {
        // Récupérer tous les cours
        $query = $courRepository->createQueryBuilder('c')->getQuery();
        
        // Paginer les résultats
        $cours = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),  // Numéro de page
            6  // Nombre d'éléments par page
        );
        
        return $this->render('front/cours.html.twig', [
            'cours' => $cours,  // Maintenant $cours est un objet de pagination
        ]);
    }

    #[Route('/enfant/cours/{id_cours}', name: 'app_enfant_lecons', methods: ['GET'])]
    public function listeLecons(int $id_cours, CourRepository $courRepository): Response
    {
        $cour = $courRepository->find($id_cours);

        if (!$cour) {
            throw $this->createNotFoundException('Cours introuvable');
        }

        return $this->render('front/lecons.html.twig', [
            'cour' => $cour,
            'lecons' => $cour->getLecons(),
        ]);
    }
    
}