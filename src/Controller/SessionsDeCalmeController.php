<?php

namespace App\Controller;

use App\Entity\SessionsDeCalme;
use App\Form\SessionsDeCalmeType;
use App\Repository\SessionsDeCalmeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


#[Route('/sessions/de/calme')]
final class SessionsDeCalmeController extends AbstractController
{
    //////////////fonctions front office
    #[Route('/front', name: 'app_sessions_de_calme_front', methods: ['GET'])]
    public function frontIndex(): Response
    {
        return $this->render('sessions_de_calme/front_index.html.twig', [
            'carousel_slides' => [
                [
                    'image' => 'base-front/img/carousel-1.jpg',
                    'title' => 'Sessions de calme pour enfants',
                    'description' => 'Des activités relaxantes pour aider votre enfant à se concentrer et à gérer son stress',
                    'learn_more_link' => '#',
                    'classes_link' => '#'
                ]
            ]
        ]);
    }

    #[Route('/activite/{type}', name: 'app_session_calme_activite', methods: ['GET'])]
    public function activite(string $type): Response
    {
        $template = match($type) {
            'musique' => 'sessions_de_calme/activite_musique.html.twig',
            'respiration' => 'sessions_de_calme/activite_respiration.html.twig',
            'coloriage' => 'sessions_de_calme/activite_coloriage.html.twig',
            'histoire' => 'sessions_de_calme/activite_histoire.html.twig',
            default => throw $this->createNotFoundException('Activité non trouvée')
        };
        
        return $this->render($template, [
            'carousel_slides' => []
        ]);
    }

    #[Route('/save-session', name: 'app_session_calme_save', methods: ['POST'])]
    public function saveSession(Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = new SessionsDeCalme();
        
        // Récupérer l'utilisateur connecté (enfant)
        // À adapter selon votre système d'authentification
        // $user = $this->getUser();
        // $session->setUtilisateur($user);
        
        $session->setTypeActivite($request->request->get('type_activite'));
        $session->setDeclencheur('enfant');
        $session->setHorodatage(new \DateTime());
        
        if ($request->request->get('duree_prevue')) {
            $session->setDureePrevue((int)$request->request->get('duree_prevue'));
        }
        
        if ($request->request->get('duree_reelle')) {
            $session->setDureeReelle((int)$request->request->get('duree_reelle'));
        }
        
        if ($request->request->get('feedback_enfant')) {
            $session->setFeedbackEnfant((int)$request->request->get('feedback_enfant'));
        }
        
        if ($request->request->get('note_parent')) {
            $session->setNoteParent($request->request->get('note_parent'));
        }
        
        $entityManager->persist($session);
        $entityManager->flush();
        
        $this->addFlash('success', 'Merci pour ta session de calme ! Bravo pour ce moment de détente 🎉');
        
        return $this->redirectToRoute('app_sessions_de_calme_front');
    }

    //////fonctions back office

    #[Route(name: 'app_sessions_de_calme_index', methods: ['GET'])]
    public function index(
        Request $request, 
        SessionsDeCalmeRepository $sessionsDeCalmeRepository,
        PaginatorInterface $paginator
    ): Response
    {
        // Récupérer les termes de recherche
        $searchTerm = $request->query->get('search', '');
        $typeFilter = $request->query->get('type', '');
        $declencheurFilter = $request->query->get('declencheur', '');
        $dateFrom = $request->query->get('date_from', '');
        $dateTo = $request->query->get('date_to', '');
        
        // Récupérer et valider le nombre d'éléments par page
        $perPage = $request->query->getInt('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;
        
        // Construire la requête avec filtres
        $queryBuilder = $sessionsDeCalmeRepository->createQueryBuilder('s');
        
        // Filtre de recherche générale
        if (!empty($searchTerm)) {
            $queryBuilder->andWhere('s.type_activite LIKE :search OR s.declencheur LIKE :search OR s.note_parent LIKE :search')
                         ->setParameter('search', '%' . $searchTerm . '%');
        }
        
        // Filtre par type d'activité
        if (!empty($typeFilter)) {
            $queryBuilder->andWhere('s.type_activite = :type')
                         ->setParameter('type', $typeFilter);
        }
        
        // Filtre par déclencheur
        if (!empty($declencheurFilter)) {
            $queryBuilder->andWhere('s.declencheur = :declencheur')
                         ->setParameter('declencheur', $declencheurFilter);
        }
        
        // Filtre par date
        if (!empty($dateFrom)) {
            $queryBuilder->andWhere('s.horodatage >= :dateFrom')
                         ->setParameter('dateFrom', new \DateTime($dateFrom . ' 00:00:00'));
        }
        
        if (!empty($dateTo)) {
            $queryBuilder->andWhere('s.horodatage <= :dateTo')
                         ->setParameter('dateTo', new \DateTime($dateTo . ' 23:59:59'));
        }
        
        $queryBuilder->orderBy('s.horodatage', 'DESC');
        
        // Calcul des statistiques globales (indépendantes de la pagination)
        // Utilisation des bons noms de champs : duree_reelle, note_parent, etc.
        $totalSessions = $sessionsDeCalmeRepository->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
            
        // Correction : utiliser duree_reelle (avec underscore) au lieu de dureeReelle
        $avgDuration = $sessionsDeCalmeRepository->createQueryBuilder('s')
            ->select('AVG(s.duree_reelle)')
            ->where('s.duree_reelle IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
            
        // Correction : utiliser note_parent (avec underscore) au lieu de noteParent
        $avgNote = $sessionsDeCalmeRepository->createQueryBuilder('s')
            ->select('AVG(s.note_parent)')
            ->where('s.note_parent IS NOT NULL')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
            
        $todayCount = $sessionsDeCalmeRepository->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.horodatage >= :todayStart')
            ->andWhere('s.horodatage <= :todayEnd')
            ->setParameter('todayStart', new \DateTime('today 00:00:00'))
            ->setParameter('todayEnd', new \DateTime('today 23:59:59'))
            ->getQuery()
            ->getSingleScalarResult();
        
        // Pagination
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            $perPage
        );
        
        return $this->render('sessions_de_calme/index.html.twig', [
            'pagination' => $pagination,
            'searchTerm' => $searchTerm,
            'typeFilter' => $typeFilter,
            'declencheurFilter' => $declencheurFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'perPage' => $perPage,
            'totalSessions' => $totalSessions,
            'avgDuration' => round($avgDuration, 1),
            'avgNote' => round($avgNote, 1),
            'todayCount' => $todayCount,
            'carousel_slides' => [
                [
                    'image' => 'base-front/img/carousel-1.jpg',
                    'title' => 'The Best Kindergarten School For Your Child',
                    'description' => 'Vero elitr justo clita lorem. Ipsum dolor at sed stet sit diam no. Kasd rebum ipsum et diam justo clita et kasd rebum sea elitr.',
                    'learn_more_link' => '#',
                    'classes_link' => '#'
                ],
                [
                    'image' => 'base-front/img/carousel-2.jpg',
                    'title' => 'Make A Brighter Future For Your Child',
                    'description' => 'Vero elitr justo clita lorem. Ipsum dolor at sed stet sit diam no. Kasd rebum ipsum et diam justo clita et kasd rebum sea elitr.',
                    'learn_more_link' => '#',
                    'classes_link' => '#'
                ]
            ]
        ]);
    }
    
    #[Route('/new', name: 'app_sessions_de_calme_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sessionsDeCalme = new SessionsDeCalme();
        $form = $this->createForm(SessionsDeCalmeType::class, $sessionsDeCalme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sessionsDeCalme);
            $entityManager->flush();

            return $this->redirectToRoute('app_sessions_de_calme_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sessions_de_calme/new.html.twig', [
            'sessions_de_calme' => $sessionsDeCalme,
            'form' => $form,
            'carousel_slides' => [
                [
                    'image' => 'base-front/img/carousel-1.jpg',
                    'title' => 'The Best Kindergarten School For Your Child',
                    'description' => 'Vero elitr justo clita lorem.',
                    'learn_more_link' => '#',
                    'classes_link' => '#'
                ]
            ]
        ]);
    }

    #[Route('/{id}', name: 'app_sessions_de_calme_show', methods: ['GET'])]
    public function show(SessionsDeCalme $sessionsDeCalme): Response
    {
        return $this->render('sessions_de_calme/show.html.twig', [
            'sessions_de_calme' => $sessionsDeCalme,
            'carousel_slides' => [
                [
                    'image' => 'base-front/img/carousel-1.jpg',
                    'title' => 'The Best Kindergarten School For Your Child',
                    'description' => 'Vero elitr justo clita lorem.',
                    'learn_more_link' => '#',
                    'classes_link' => '#'
                ]
            ]
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sessions_de_calme_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SessionsDeCalme $sessionsDeCalme, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SessionsDeCalmeType::class, $sessionsDeCalme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_sessions_de_calme_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sessions_de_calme/edit.html.twig', [
            'sessions_de_calme' => $sessionsDeCalme,
            'form' => $form,
            'carousel_slides' => [
                [
                    'image' => 'base-front/img/carousel-1.jpg',
                    'title' => 'The Best Kindergarten School For Your Child',
                    'description' => 'Vero elitr justo clita lorem.',
                    'learn_more_link' => '#',
                    'classes_link' => '#'
                ]
            ]
        ]);
    }
    
    #[Route('/{id}/delete', name: 'app_sessions_de_calme_delete', methods: ['POST'])]
    public function delete(Request $request, SessionsDeCalme $sessionsDeCalme, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $sessionsDeCalme->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($sessionsDeCalme);
                $entityManager->flush();
                $this->addFlash('success', 'La session a été supprimée avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide. Veuillez réessayer.');
        }
        
        return $this->redirectToRoute('app_sessions_de_calme_index', [], Response::HTTP_SEE_OTHER);
    }
#[Route('/api/process-voice-command', name: 'process_voice_command', methods: ['POST'])]
public function processVoiceCommand(Request $request): JsonResponse
{
    try {
        $content = $request->getContent();
        error_log('=== DEBUG Voice Command ===');
        error_log('Content reçu: ' . $content);
        
        $data = json_decode($content, true);
        
        if (!$data) {
            error_log('Erreur: JSON invalide');
            return $this->json([
                'success' => false,
                'message' => 'JSON invalide'
            ]);
        }
        
        // Accepter les deux formats (commande ou command)
        $commande = $data['commande'] ?? $data['command'] ?? null;
        
        error_log('Commande extraite: ' . ($commande ?? 'null'));
        
        if (!$commande) {
            error_log('Erreur: Aucune commande trouvée');
            return $this->json([
                'success' => false,
                'message' => 'Commande manquante'
            ]);
        }
        
        $commande = strtolower(trim($commande));
        
        // Ping pour tester
        if ($commande === 'ping') {
            return $this->json([
                'success' => true,
                'message' => 'pong'
            ]);
        }
        
        // Vérification manuelle des commandes
        if (str_contains($commande, 'coloriage') || str_contains($commande, 'colorier') || str_contains($commande, 'dessin')) {
            return $this->json([
                'success' => true,
                'action' => 'coloriage',
                'redirect' => '/sessions/de/calme/activite/coloriage',
                'message' => 'Ouverture de la session coloriage. Bon amusement !'
            ]);
        }
        
        if (str_contains($commande, 'histoire') || str_contains($commande, 'raconter') || str_contains($commande, 'conte')) {
            return $this->json([
                'success' => true,
                'action' => 'histoire',
                'redirect' => '/sessions/de/calme/activite/histoire',
                'message' => 'Ouverture de la session histoire. Préparez-vous à écouter !'
            ]);
        }
        
        if (str_contains($commande, 'respiration') || str_contains($commande, 'respirer') || str_contains($commande, 'calme')) {
            return $this->json([
                'success' => true,
                'action' => 'respiration',
                'redirect' => '/sessions/de/calme/activite/respiration',
                'message' => 'Ouverture de la session respiration. Inspirez, expirez...'
            ]);
        }
        
        if (str_contains($commande, 'musique') || str_contains($commande, 'chanson') || str_contains($commande, 'son')) {
            return $this->json([
                'success' => true,
                'action' => 'musique',
                'redirect' => '/sessions/de/calme/activite/musique',
                'message' => 'Ouverture de la session musique. Laissez-vous emporter par les sons !'
            ]);
        }
        
        // Commande non reconnue
        return $this->json([
            'success' => true,
            'action' => 'unknown',
            'message' => "Je n'ai pas compris '{$commande}'. Dites : coloriage, histoire, respiration ou musique"
        ]);
        
    } catch (\Exception $e) {
        error_log('Exception: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        
        return $this->json([
            'success' => false,
            'message' => 'Erreur serveur: ' . $e->getMessage()
        ]);
    }
}
}
