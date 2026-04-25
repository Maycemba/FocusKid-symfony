<?php
namespace App\Controller;

use App\Repository\CourRepository;
use App\Service\TranslationService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $search = trim($request->query->get('search', ''));
        $niveau = trim($request->query->get('niveau', ''));
        $sort   = trim($request->query->get('sort', ''));

        $qb = $courRepository->createQueryBuilder('c');

        if ($search !== '') {
            $qb->andWhere('LOWER(c.titre) LIKE :search OR LOWER(COALESCE(c.formateur, \'\')) LIKE :search OR LOWER(COALESCE(c.description, \'\')) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower($search) . '%');
        }

        if ($niveau !== '') {
            $qb->andWhere('c.niveau = :niveau')
                ->setParameter('niveau', $niveau);
        }

        if ($sort === 'za') {
            $qb->orderBy('c.titre', 'DESC');
        } else {
            $qb->orderBy('c.titre', 'ASC');
        }

        $query = $qb->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );

        if ($request->isXmlHttpRequest()) {
            $items = [];

            foreach ($pagination as $cour) {
                $items[] = [
                    'id'          => $cour->getIdCours(),
                    'titre'       => $cour->getTitre(),
                    'niveau'      => $cour->getNiveau(),
                    'description' => $cour->getDescription(),
                    'formateur'   => $cour->getFormateur(),
                    'lecons'      => $cour->getLecons()->count(),
                    'urlLecons'   => $this->generateUrl('app_enfant_lecons', [
                        'id_cours' => $cour->getIdCours(),
                    ]),
                ];
            }

            return new JsonResponse([
                'cours'    => $items,
                'total'    => $pagination->getTotalItemCount(),
                'page'     => $pagination->getCurrentPageNumber(),
                'perPage'  => $pagination->getItemNumberPerPage(),
                'pages'    => (int) ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage()),
                'search'   => $search,
                'niveau'   => $niveau,
                'sort'     => $sort,
            ]);
        }

        return $this->render('front/cours.html.twig', [
            'cours' => $pagination,
            'search' => $search,
            'niveau' => $niveau,
            'sort' => $sort,
        ]);
    }

    #[Route(
        '/enfant/cours/{id_cours}/{_locale}',
        name: 'app_enfant_lecons',
        methods: ['GET'],
        defaults: ['_locale' => 'fr'],
        requirements: ['_locale' => 'fr|en|ar']
    )]
    public function listeLecons(
        int $id_cours,
        string $_locale,
        Request $request,
        CourRepository $courRepository,
        TranslationService $translationService
    ): Response {
        $cour = $courRepository->find($id_cours);

        if (!$cour) {
            throw $this->createNotFoundException('Cours introuvable');
        }

        $locale = $_locale;
        $request->setLocale($locale);

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
