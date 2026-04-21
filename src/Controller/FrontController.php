<?php
namespace App\Controller;

use App\Repository\CourRepository;
use App\Service\TranslationService;
use Knp\Component\Pager\PaginatorInterface;
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
    public function listeCours(
        Request $request,
        CourRepository $courRepository,
        PaginatorInterface $paginator
    ): Response {
        $query = $courRepository->createQueryBuilder('c')
            ->orderBy('c.titre', 'ASC')
            ->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('front/cours.html.twig', [
            'cours' => $pagination,
        ]);
    }

    #[Route('/enfant/cours/{id_cours}', name: 'app_enfant_lecons', methods: ['GET'])]
    public function listeLecons(
        int $id_cours,
        Request $request,
        CourRepository $courRepository,
        TranslationService $translationService
    ): Response {
        $cour = $courRepository->find($id_cours);

        if (!$cour) {
            throw $this->createNotFoundException('Cours introuvable');
        }

        $locale = $request->query->get('lang', 'fr');
        $langues = ['fr', 'en', 'ar'];
        if (!in_array($locale, $langues)) {
            $locale = 'fr';
        }

        $lecons = $cour->getLecons();
        $leconsTraduites = [];

        foreach ($lecons as $lecon) {
            $leconsTraduites[] = [
                'original'    => $lecon,
                'titre_lecon' => $locale !== 'fr'
                    ? $translationService->translate($lecon->getTitreLecon(), $locale)
                    : $lecon->getTitreLecon(),
                'contenu'     => $locale !== 'fr'
                    ? $translationService->translate($lecon->getContenu() ?? '', $locale)
                    : $lecon->getContenu(),
            ];
        }

        return $this->render('front/lecons.html.twig', [
            'cour'            => $cour,
            'lecons'          => $lecons,
            'leconsTraduites' => $leconsTraduites,
            'locale'          => $locale,
        ]);
    }
}