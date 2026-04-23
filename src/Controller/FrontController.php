<?php
namespace App\Controller;

use App\Repository\CourRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FrontController extends AbstractController
{
    #[Route('/index', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('public/base-front/index.html');
    }

    #[Route('/enfant', name: 'app_enfant_cours', methods: ['GET'])]
    public function listeCours(CourRepository $courRepository): Response
    {
        return $this->render('front/cours.html.twig', [
            'cours' => $courRepository->findAll(),
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
    #[Route('/index', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('front/index.html.twig');
    }
}