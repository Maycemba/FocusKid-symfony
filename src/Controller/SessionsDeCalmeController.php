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

#[Route('/sessions/de/calme')]
final class SessionsDeCalmeController extends AbstractController  // Changé: extends AbstractController au lieu de BaseController
{
    //////////////fonctions front office
// Ajoutez ces routes au début du fichier, après le #[Route('/sessions/de/calme')]

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
    $session->setDeclencheur('enfant'); // L'enfant a lancé la session
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

////fonction back office

   /* #[Route(name: 'app_sessions_de_calme_index', methods: ['GET'])]
    public function index(SessionsDeCalmeRepository $sessionsDeCalmeRepository): Response
    {
        return $this->render('sessions_de_calme/index.html.twig', [
            'sessions_de_calmes' => $sessionsDeCalmeRepository->findAll(),
            // AJOUT OBLIGATOIRE - Données du carousel
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
    } */
   // Dans SessionsDeCalmeController.php, modifiez la méthode index 

#[Route(name: 'app_sessions_de_calme_index', methods: ['GET'])]
public function index(Request $request, SessionsDeCalmeRepository $sessionsDeCalmeRepository): Response
{
    // Récupérer les termes de recherche
    $searchTerm = $request->query->get('search', '');
    $typeFilter = $request->query->get('type', '');
    $declencheurFilter = $request->query->get('declencheur', '');
    $dateFrom = $request->query->get('date_from', '');
    $dateTo = $request->query->get('date_to', '');
    
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
    
    $sessions = $queryBuilder->getQuery()->getResult();
    
    return $this->render('sessions_de_calme/index.html.twig', [
        'sessions_de_calmes' => $sessions,
        'searchTerm' => $searchTerm,
        'typeFilter' => $typeFilter,
        'declencheurFilter' => $declencheurFilter,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
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
            // AJOUTEZ AUSSI ICI
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
            // AJOUTEZ AUSSI ICI
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
            // AJOUTEZ AUSSI ICI
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
        // Vérifier le token CSRF
        if ($this->isCsrfTokenValid('delete' . $sessionsDeCalme->getId(), $request->request->get('_token'))) {
            
            try {
                // Supprimer l'entité
                $entityManager->remove($sessionsDeCalme);
                $entityManager->flush();
                
                // Ajouter un message de succès
                $this->addFlash('success', 'La session a été supprimée avec succès !');
                
            } catch (\Exception $e) {
                // En cas d'erreur
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression : ' . $e->getMessage());
            }
        } else {
            // Token CSRF invalide
            $this->addFlash('error', 'Token CSRF invalide. Veuillez réessayer.');
        }
        
        // Rediriger vers la liste
        return $this->redirectToRoute('app_sessions_de_calme_index', [], Response::HTTP_SEE_OTHER);
    }

}