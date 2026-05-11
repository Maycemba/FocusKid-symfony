<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\CourRepository;
use App\Service\TranslationService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\EmotionRepository;
use App\Repository\ScenarioRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\RegistrationFormType;

final class FrontController extends AbstractController
{
    #[Route('/index', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('/sessions_de_calme/front_index.html.twig');
    }

    #[Route('/', name: 'app_home')]
public function home(): Response
{
    // Affiche directement la nouvelle page d'accueil publique
    return $this->render('/sessions_de_calme/front_index.html.twig');
}
    // Dans votre contrôleur (par exemple FrontController.php)

 
    #[Route('/accueil', name: 'app_nouvel_accueil')]
public function nouvelAccueil(): Response
{
    return $this->render('/index.html.twig');
}
 

    // Version complète avec pagination, recherche et filtres
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

    // Version complète avec traduction
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

    // Version PDF avec traduction
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

    // Gestion des émotions
    #[Route('/enfant/emotions', name: 'app_enfant_emotions', methods: ['GET'])]
    public function emotions(EmotionRepository $emotionRepository): Response
    {
        return $this->render('front/emotions.html.twig', [
            'emotions' => $emotionRepository->findAll(),
        ]);
    }

    // Scénarios par émotion
    #[Route('/enfant/emotions/{emotionId}/scenarios', name: 'app_enfant_scenarios_emotion', methods: ['GET'])]
    public function scenariosParEmotion(int $emotionId, ScenarioRepository $scenarioRepository): JsonResponse
    {
        $scenarios = $scenarioRepository->findBy(['emotion' => $emotionId]);

        $data = array_map(fn($s) => [
            'id'          => $s->getId(),
            'description' => $s->getDescription(),
            'animation'   => $s->getAnimation(),
        ], $scenarios);

        return new JsonResponse(['scenarios' => $data]);
    }
#[Route('/register-form-ajax', name: 'app_register_form_ajax')]
    public function registerFormAjax(Request $request): Response
    {
        $user = new Utilisateur();
        $form = $this->createForm(RegistrationFormType::class, $user, [
            'action' => $this->generateUrl('app_register'), // ← Action vers la route POST
        ]);
        return $this->render('registration/_register_form.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/login-form-ajax', name: 'app_login_form_ajax')]
    public function loginFormAjax(Request $request): Response
    {
        // Générer un CAPTCHA frais pour le formulaire
        $captchaUrl = $this->generateUrl('app_captcha_image');
        return $this->render('security/_login_form.html.twig', [
            'error' => null,
            'savedUsername' => $request->cookies->get('remember_username', ''),
            'rememberChecked' => $request->cookies->get('remember_username') !== null,
            'captchaUrl' => $captchaUrl,
            'lockoutSeconds' => 0, // On gère le lockout dans le template via session si besoin
        ]);
    }
}