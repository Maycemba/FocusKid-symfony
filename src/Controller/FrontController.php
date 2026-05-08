<?php

namespace App\Controller;

use App\Repository\CourRepository;
use App\Service\TranslationService;
use Dompdf\Dompdf;
use Dompdf\Options;
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

    // Version de test sans pagination complexe
    #[Route('/enfant', name: 'app_enfant_cours', methods: ['GET'])]
    public function listeCours(
        Request $request,
        CourRepository $courRepository
    ): Response {
        try {
            $search = trim($request->query->get('search', ''));
            $niveau = trim($request->query->get('niveau', ''));
            $sort   = trim($request->query->get('sort', 'az'));

            $qb = $courRepository->createQueryBuilder('c');

            if ($search !== '') {
                $qb->andWhere('LOWER(c.titre) LIKE :search')
                    ->setParameter('search', '%' . mb_strtolower($search) . '%');
            }

            if ($niveau !== '') {
                $qb->andWhere('c.niveau = :niveau')
                    ->setParameter('niveau', (int)$niveau);
            }

            if ($sort === 'za') {
                $qb->orderBy('c.titre', 'DESC');
            } else {
                $qb->orderBy('c.titre', 'ASC');
            }

            $cours = $qb->getQuery()->getResult();

            if ($request->isXmlHttpRequest()) {
                $items = [];
                foreach ($cours as $cour) {
                    $items[] = [
                        'id'          => $cour->getIdCours(),
                        'titre'       => $cour->getTitre(),
                        'niveau'      => $cour->getNiveau(),
                        'description' => $cour->getDescription(),
                        'formateur'   => $cour->getFormateur(),
                        'lecons'      => $cour->getLecons()->count(),
                        'urlLecons'   => $this->generateUrl('app_enfant_lecons', [
                            'id_cours' => $cour->getIdCours(),
                            '_locale' => $request->getLocale(),
                        ]),
                    ];
                }

                return $this->json([
                    'cours' => $items,
                    'total' => count($items),
                    'page' => 1,
                    'pages' => 1
                ]);
            }

            return $this->render('front/cours.html.twig', [
                'cours' => $cours,
                'search' => $search,
                'niveau' => $niveau,
                'sort' => $sort,
            ]);
        } catch (\Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['error' => $e->getMessage()], 500);
            }
            throw $e;
        }
    }

    #[Route(
        '/enfant/cours/{id_cours}/{_locale}',
        name: 'app_enfant_lecons',
        methods: ['GET'],
        defaults: ['_locale' => 'fr'],
        requirements: ['_locale' => 'fr|en|ar', 'id_cours' => '\d+']
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

    #[Route(
        '/enfant/cours/{id_cours}/pdf/{_locale}',
        name: 'app_enfant_cours_pdf',
        methods: ['GET'],
        defaults: ['_locale' => 'fr'],
        requirements: ['_locale' => 'fr|en|ar', 'id_cours' => '\d+']
    )]
    public function telechargerCoursPdf(
        int $id_cours,
        string $_locale,
        CourRepository $courRepository,
        TranslationService $translationService
    ): Response {
        $cour = $courRepository->find($id_cours);

        if (!$cour) {
            throw $this->createNotFoundException('Cours introuvable');
        }

        $locale = $_locale;
        $lecons = $cour->getLecons();
        $leconsTraduites = [];

        foreach ($lecons as $lecon) {
            $leconsTraduites[] = [
                'titre_lecon' => $locale !== 'fr'
                    ? $translationService->translate($lecon->getTitreLecon(), $locale)
                    : $lecon->getTitreLecon(),
                'contenu'     => $locale !== 'fr'
                    ? $translationService->translate($lecon->getContenu() ?? '', $locale)
                    : $lecon->getContenu(),
            ];
        }

        // Générer le HTML depuis un template Twig dédié
        $html = $this->renderView('front/cours_pdf.html.twig', [
            'cour'            => $cour,
            'leconsTraduites' => $leconsTraduites,
            'locale'          => $locale,
        ]);

        // Configurer DomPDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Arial');
        $options->set('chroot', $this->getParameter('kernel.project_dir') . '/public');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nomFichier = 'cours-' . preg_replace('/[^a-z0-9]/i', '-', $cour->getTitre()) . '.pdf';

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $nomFichier . '"'
            ]
        );
    }

    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_utilisateur_index');
        }

        return $this->redirectToRoute('app_index');
    }
}