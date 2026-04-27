<?php

namespace App\Controller;

use App\Entity\Exercice;
use App\Entity\ExerciceEnfant;
use App\Entity\Utilisateur;
use App\Form\ExerciceType;
use App\Repository\ExerciceRepository;
use App\Repository\UtilisateurRepository;  
use App\Repository\ExerciceEnfantRepository;
use App\Service\QuestionGeneratorIA;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/exercice')]
final class ExerciceController extends AbstractController
{
    // ========== BACK OFFICE (ADMIN) ==========
    
    #[Route('/', name: 'app_exercice_index', methods: ['GET'])]
    public function index(ExerciceRepository $exerciceRepository): Response
    {
        return $this->render('exercice/index.html.twig', [
            'exercices' => $exerciceRepository->findAll(),
        ]);
    }

  #[Route('/new', name: 'app_exercice_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, EmailService $emailService): Response
{if ($request->isMethod('POST')) {
    $postData = $request->request->all();
    $exerciceData = $postData['exercice'] ?? [];
    $contenuJson = $postData['contenuJson'] ?? null;
    
    if (!$contenuJson) {
        $contenuJson = $exerciceData['contenu'] ?? null;
    }
    
    // Création de l'exercice
    $exercice = new Exercice();
    $exercice->setTitre($exerciceData['titre'] ?? 'Sans titre');
    $exercice->setType($exerciceData['type'] ?? 'CHRONO');
    $exercice->setConsigne($exerciceData['consigne'] ?? '');
    $exercice->setDifficulte((int)($exerciceData['difficulte'] ?? 1));
    $exercice->setDuree((int)($exerciceData['duree'] ?? 60));
    $exercice->setActif(isset($exerciceData['actif']));
    $exercice->setArchive(isset($exerciceData['archive']));
    $exercice->setPourTousEnfants(isset($exerciceData['pour_tous_enfants']));
    $exercice->setDateCreation(new \DateTime());
    $exercice->setCreePar($this->getUser() ? $this->getUser()->getId() : 1);
    
    if ($contenuJson && $contenuJson !== '{}' && $contenuJson !== 'null') {
        $exercice->setContenu($contenuJson);
    } else {
        $exercice->setContenu('{"dureeTotale":60,"defis":[{"question":"2 + 2 = ?","reponse":"4","points":10}]}');
    }
    
    $entityManager->persist($exercice);
    $entityManager->flush();
    
    // Récupération des jours
    $joursString = $postData['jours'] ?? '';
    if (is_array($joursString)) {
        $joursString = implode(',', $joursString);
    }
    
    // Récupérer les enfants assignés (objets pour emails)
    $enfantsAssignes = [];
    
    // Gestion des enfants
    if ($exercice->isPourTousEnfants()) {
        $tousLesEnfants = $entityManager->getRepository(Utilisateur::class)->findBy(['role' => 'enfant']);
        $enfantsIds = [];
        foreach ($tousLesEnfants as $enfant) {
            $enfantsIds[] = $enfant->getId();
            $enfantsAssignes[] = $enfant;
        }
    } else {
        $enfantsIds = $exerciceData['enfantsSelectionnes'] ?? [];
        $enfantsIds = array_filter($enfantsIds);
        
        foreach ($enfantsIds as $enfantId) {
            $enfant = $entityManager->getRepository(Utilisateur::class)->find($enfantId);
            if ($enfant) {
                $enfantsAssignes[] = $enfant;
            }
        }
    }
    
    // Création des assignations
    if (!empty($enfantsIds)) {
        foreach ($enfantsIds as $enfantId) {
            $enfant = $entityManager->getRepository(Utilisateur::class)->find($enfantId);
            if ($enfant) {
                $exerciceEnfant = new ExerciceEnfant();
                $exerciceEnfant->setExerciceId($exercice->getId());
                $exerciceEnfant->setEnfantId($enfant->getId());
                $exerciceEnfant->setDateAttribution(new \DateTime());
                $exerciceEnfant->setJours($joursString);
                $entityManager->persist($exerciceEnfant);
            }
        }
        $entityManager->flush();
        
        // 🔥 ENVOI DES EMAILS AUX ENFANTS ASSIGNÉS
        $dateAttribution = (new \DateTime())->format('d/m/Y à H:i');
        $joursAffichage = $joursString ?: 'Non spécifiés';
        
        $emailCount = 0;
        foreach ($enfantsAssignes as $enfant) {
            $emailSent = $emailService->sendNewExerciseNotification(
                $enfant->getEmail(),
                $enfant->getUsername(),
                $exercice->getTitre(),
                $exercice->getType(),
                $exercice->getConsigne(),
                $dateAttribution,
                $joursAffichage
            );
            if ($emailSent) {
                $emailCount++;
                $this->addFlash('info', 'Email envoyé à ' . $enfant->getUsername());
            }
        }
        
        $this->addFlash('success', sprintf('✅ Exercice créé ! %d email(s) envoyé(s).', $emailCount));
    } else {
        $this->addFlash('success', '✅ Exercice créé avec succès !');
    }
    
    return $this->redirectToRoute('app_exercice_index');
}
    
    $exercice = new Exercice();
    $form = $this->createForm(ExerciceType::class, $exercice);
    
    return $this->render('exercice/new.html.twig', [
        'exercice' => $exercice,
        'form' => $form->createView(),
    ]);
}

    // Ajoute cette méthode pour assigner à tous les enfants
    private function assignToAllChildren(Exercice $exercice, EntityManagerInterface $em): void
    {
        $enfantRepo = $em->getRepository(Utilisateur::class);
        $enfants = $enfantRepo->findBy(['role' => 'enfant']);
        
        foreach ($enfants as $enfant) {
            $exists = $em->getRepository(ExerciceEnfant::class)->findOneBy([
                'exercice_id' => $exercice->getId(),
                'enfant_id' => $enfant->getId()
            ]);
            
            if (!$exists) {
                $exerciceEnfant = new ExerciceEnfant();
                $exerciceEnfant->setExerciceId($exercice->getId());
                $exerciceEnfant->setEnfantId($enfant->getId());
                $exerciceEnfant->setDateAttribution(new \DateTime());
                $em->persist($exerciceEnfant);
            }
        }
        $em->flush();
    }

    // Ajoute cette méthode pour générer un contenu par défaut
    private function getDefaultContenuByType(string $type): string
    {
        switch ($type) {
            case 'MEMOIRE':
                return json_encode([
                    'theme' => 'animaux',
                    'taille' => '4x4',
                    'tempsAffichage' => 3,
                    'pointsParPaire' => 10
                ]);
            case 'ATTENTION':
                return json_encode([
                    'sousType' => 'intrus',
                    'questions' => [
                        ['images' => '🐶,🐱,🐭,🐹', 'intrus' => '🦊', 'reponse' => '🦊'],
                        ['images' => '🍎,🍎,🍎,🍏', 'intrus' => '🍏', 'reponse' => '🍏']
                    ],
                    'tempsParQuestion' => 10
                ]);
            case 'LOGIQUE':
                return json_encode([
                    'sousType' => 'numerique',
                    'sequences' => [
                        ['serie' => '2, 4, 6, 8, ?', 'reponse' => '10', 'regle' => '+2'],
                        ['serie' => '5, 10, 15, 20, ?', 'reponse' => '25', 'regle' => '+5']
                    ]
                ]);
            case 'CHRONO':
                return json_encode([
                    'dureeTotale' => 60,
                    'defis' => [
                        ['question' => '2 + 2 = ?', 'reponse' => '4', 'points' => 10],
                        ['question' => '5 x 3 = ?', 'reponse' => '15', 'points' => 10]
                    ]
                ]);
            default:
                return '{}';
        }
    }

    #[Route('/{id}/edit', name: 'app_exercice_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Exercice $exercice, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(ExerciceType::class, $exercice);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $contenuJson = $request->request->get('contenuJson');
        
        if (!$contenuJson) {
            $postData = $request->request->all();
            $contenuJson = $postData['contenuJson'] ?? null;
        }
        
        if ($contenuJson && $contenuJson !== '{}' && $contenuJson !== 'null') {
            $exercice->setContenu($contenuJson);
        }
        
        // 🔥 Mettre à jour les jours pour les assignations existantes
        $joursSelectionnes = $request->request->get('jours') ?? [];
        $joursString = implode(',', $joursSelectionnes);
        
        $exercicesEnfant = $entityManager->getRepository(ExerciceEnfant::class)->findBy(['exercice_id' => $exercice->getId()]);
        foreach ($exercicesEnfant as $ee) {
            $ee->setJours($joursString);
        }
        
        $entityManager->flush();
        $this->addFlash('success', 'Exercice modifié avec succès !');
        return $this->redirectToRoute('app_exercice_index');
    }

    return $this->render('exercice/edit.html.twig', [
        'exercice' => $exercice,
        'form' => $form,
    ]);
}

    #[Route('/{id}', name: 'app_exercice_show', methods: ['GET'])]
    public function show(Exercice $exercice, UtilisateurRepository $utilisateurRepository): Response
    {
        $createur = null;
        if ($exercice->getCreePar()) {
            try {
                $createur = $utilisateurRepository->find($exercice->getCreePar());
            } catch (\Exception $e) {
                $createur = null;
            }
        }
        
        return $this->render('exercice/show.html.twig', [
            'exercice' => $exercice,
            'createur' => $createur,
        ]);
    }

    #[Route('/{id}', name: 'app_exercice_delete', methods: ['POST'])]
    public function delete(Request $request, Exercice $exercice, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$exercice->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($exercice);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_exercice_index', [], Response::HTTP_SEE_OTHER);
    }
    
    #[Route('/archive/{id}', name: 'app_exercice_archive', methods: ['POST'])]
    public function archive(Request $request, Exercice $exercice, EntityManagerInterface $entityManager): Response
    {
        $token = $request->request->get('_token');
        
        if ($this->isCsrfTokenValid('archive' . $exercice->getId(), $token)) {
            $exercice->setArchive(true);
            $exercice->setActif(false);
            $entityManager->flush();
            $this->addFlash('success', 'Exercice "' . $exercice->getTitre() . '" archivé avec succès!');
        } else {
            $this->addFlash('error', 'Token invalide');
        }
        
        return $this->redirectToRoute('app_exercice_index');
    }

    #[Route('/restore/{id}', name: 'app_exercice_restore', methods: ['POST'])]
    public function restore(Request $request, Exercice $exercice, EntityManagerInterface $entityManager): Response
    {
        $token = $request->request->get('_token');
        
        if ($this->isCsrfTokenValid('restore' . $exercice->getId(), $token)) {
            $exercice->setArchive(false);
            $entityManager->flush();
            $this->addFlash('success', 'Exercice "' . $exercice->getTitre() . '" restauré avec succès!');
        } else {
            $this->addFlash('error', 'Token invalide');
        }
        
        return $this->redirectToRoute('app_exercice_index');
    }
    
    #[Route('/api/generer-exercice-ia', name: 'api_generer_exercice_ia', methods: ['POST'])]
    public function genererExerciceIA(Request $request, QuestionGeneratorIA $generatorIA): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $titre = $data['titre'] ?? '';
        $type = $data['type'] ?? 'MÉMOIRE';
        $nbQuestions = $data['nbQuestions'] ?? 5;
        
        $contenu = $generatorIA->genererExercice($titre, $type, $nbQuestions);
        
        return $this->json(['success' => true, 'contenu' => $contenu]);
    }

    // ========== FRONT OFFICE (ENFANT) - NOUVELLES ROUTES ==========
    
    // ========== FRONT OFFICE (ENFANT) ==========

#[Route('/front/exercices', name: 'app_front_exercice_index', methods: ['GET'])]
public function frontIndex(Request $request, ExerciceRepository $exerciceRepository, ExerciceEnfantRepository $exerciceEnfantRepository): Response
{
    $user = $this->getUser();
    $enfantId = $user ? $user->getId() : 1;
    
    $type = $request->query->get('type', 'non_complete');
    $showPlanning = $request->query->get('showPlanning', 0);
    $view = $request->query->get('view', 'day');
    $date = $request->query->get('date', date('Y-m-d'));
    $currentDate = new \DateTime($date);
    
    // Récupérer les exercices assignés à l'enfant
    $exercicesEnfant = $exerciceEnfantRepository->findBy(['enfant_id' => $enfantId]);
    $exercicesIds = [];
    $completionStatus = [];
    $joursParExercice = []; // Stocker les jours par exercice
    
    foreach ($exercicesEnfant as $ee) {
        $exercicesIds[] = $ee->getExerciceId();
        $completionStatus[$ee->getExerciceId()] = $ee->isComplete();
        $joursParExercice[$ee->getExerciceId()] = $ee->getJoursArray();
    }
    
    $allExercices = [];
    if (count($exercicesIds) > 0) {
        $allExercices = $exerciceRepository->findBy(['id' => $exercicesIds, 'archive' => false]);
    }
    
    // Filtrer selon le type
    $exercices = [];
    foreach ($allExercices as $exo) {
        $isComplete = $completionStatus[$exo->getId()] ?? false;
        if ($type === 'complete' && $isComplete) {
            $exercices[] = $exo;
        } elseif ($type === 'non_complete' && !$isComplete) {
            $exercices[] = $exo;
        }
    }
    
    // Compter pour les badges
    $nonCompleteCount = 0;
    $completeCount = 0;
    foreach ($completionStatus as $status) {
        if ($status) {
            $completeCount++;
        } else {
            $nonCompleteCount++;
        }
    }
    
    return $this->render('front/exercice/index.html.twig', [
        'exercices' => $exercices,
        'currentType' => $type,
        'nonCompleteCount' => $nonCompleteCount,
        'completeCount' => $completeCount,
        'allExercices' => $allExercices,
        'joursParExercice' => $joursParExercice, // ← Passer les jours séparément
        'showPlanning' => $showPlanning,
        'currentView' => $view,
        'currentDate' => $currentDate,
        'enfantId' => $enfantId,
    ]);
}
    
    #[Route('/front/jeu/memoire/{id}', name: 'app_front_jeu_memoire', methods: ['GET'])]
    public function jeuMemoire(int $id, ExerciceRepository $exerciceRepository): Response
    {
        // 🔥 Récupérer l'ID de l'enfant connecté
        $user = $this->getUser();
        $enfantId = $user ? $user->getId() : 1;
        
        $exercice = $exerciceRepository->find($id);
        
        if (!$exercice) {
            throw $this->createNotFoundException('Exercice non trouvé');
        }
        
        $contenu = $exercice->getContenu();
        $questions = [];
        
        if ($contenu) {
            $data = json_decode($contenu, true);
            if ($data) {
                if (isset($data['images']) && is_array($data['images'])) {
                    $questions = $data['images'];
                } elseif (isset($data['theme'])) {
                    $themes = [
                        'animaux' => ['🐶','🐱','🐭','🐹','🐰','🦊','🐻','🐼'],
                        'fruits' => ['🍎','🍐','🍊','🍋','🍌','🍉','🍇','🍓'],
                        'chiffres' => ['1️⃣','2️⃣','3️⃣','4️⃣','5️⃣','6️⃣','7️⃣','8️⃣']
                    ];
                    $theme = $data['theme'] ?? 'animaux';
                    $taille = $data['taille'] ?? '4x4';
                    $nbCartes = $taille === '4x4' ? 8 : 18;
                    $imagesList = $themes[$theme] ?? $themes['animaux'];
                    for ($i = 0; $i < $nbCartes; $i++) {
                        $questions[] = ['valeur' => $imagesList[$i % count($imagesList)]];
                    }
                }
            }
        }
        
        return $this->render('front/exercice/jeu_memoire.html.twig', [
            'exercice' => $exercice,
            'questions' => $questions,
            'contenu_json' => $contenu,
            'enfantId' => $enfantId,
        ]);
    }

    #[Route('/front/jeu/attention/{id}', name: 'app_front_jeu_attention', methods: ['GET'])]
    public function jeuAttention(int $id, ExerciceRepository $exerciceRepository): Response
    {
        // 🔥 Récupérer l'ID de l'enfant connecté
        $user = $this->getUser();
        $enfantId = $user ? $user->getId() : 1;
        
        $exercice = $exerciceRepository->find($id);
        
        if (!$exercice) {
            throw $this->createNotFoundException('Exercice non trouvé');
        }
        
        $contenu = $exercice->getContenu();
        $questions = [];
        
        if ($contenu) {
            $data = json_decode($contenu, true);
            if ($data && isset($data['questions']) && is_array($data['questions'])) {
                $questions = $data['questions'];
            } elseif ($data && isset($data['theme'])) {
                $themes = [
                    'emojis' => ['🐶','🐱','🐭','🐹','🐰','🦊','🐻','🐼','🐨','🐸'],
                    'animaux' => ['🦁','🐧','🐦','🐟','🐠','🐙','🦋','🐝','🐞','🐳'],
                    'objets' => ['📚','✏️','🔍','💡','🔑','⌚','📱','💻','🖱️','📷']
                ];
                $theme = $data['theme'] ?? 'emojis';
                $imagesList = $themes[$theme] ?? $themes['emojis'];
                foreach ($imagesList as $img) {
                    $questions[] = ['image' => $img, 'valeur' => $img];
                }
            }
        }
        
        if (empty($questions)) {
            $defaultImages = ['🐶','🐱','🐭','🐹','🐰','🦊','🐻','🐼','🐨','🐸','🐧','🐦'];
            foreach ($defaultImages as $img) {
                $questions[] = ['image' => $img, 'valeur' => $img];
            }
        }
        
        return $this->render('front/exercice/jeu_attention.html.twig', [
            'exercice' => $exercice,
            'questions' => $questions,
            'contenu_json' => $contenu,
            'enfantId' => $enfantId,
        ]);
    }

    #[Route('/front/jeu/logique/{id}', name: 'app_front_jeu_logique', methods: ['GET'])]
    public function jeuLogique(int $id, ExerciceRepository $exerciceRepository): Response
    {
        // 🔥 Récupérer l'ID de l'enfant connecté
        $user = $this->getUser();
        $enfantId = $user ? $user->getId() : 1;
        
        $exercice = $exerciceRepository->find($id);
        
        if (!$exercice) {
            throw $this->createNotFoundException('Exercice non trouvé');
        }
        
        $contenu = $exercice->getContenu();
        $questions = [];
        
        if ($contenu) {
            $data = json_decode($contenu, true);
            if ($data && isset($data['sequences']) && is_array($data['sequences'])) {
                $questions = $data['sequences'];
            } elseif ($data && isset($data['questions']) && is_array($data['questions'])) {
                $questions = $data['questions'];
            }
        }
        
        if (empty($questions)) {
            $questions = [
                ['serie' => '2, 4, 6, 8, ?', 'reponse' => '10', 'regle' => 'Ajouter 2'],
                ['serie' => '5, 10, 15, 20, ?', 'reponse' => '25', 'regle' => 'Ajouter 5'],
                ['serie' => '3, 6, 12, 24, ?', 'reponse' => '48', 'regle' => 'Multiplier par 2']
            ];
        }
        
        return $this->render('front/exercice/jeu_logique.html.twig', [
            'exercice' => $exercice,
            'questions' => $questions,
            'contenu_json' => $contenu,
            'enfantId' => $enfantId,
        ]);
    }
    
    #[Route('/front/jeu/chrono/{id}', name: 'app_front_jeu_chrono', methods: ['GET'])]
    public function jeuChrono(int $id, ExerciceRepository $exerciceRepository): Response
    {
        // 🔥 Récupérer l'ID de l'enfant connecté
        $user = $this->getUser();
        $enfantId = $user ? $user->getId() : 1;
        
        $exercice = $exerciceRepository->find($id);
        
        if (!$exercice) {
            throw $this->createNotFoundException('Exercice non trouvé');
        }
        
        $contenu = $exercice->getContenu();
        $questions = [];
        
        if ($contenu) {
            $data = json_decode($contenu, true);
            if ($data && isset($data['defis'])) {
                $questions = $data['defis'];
            }
        }
        
        return $this->render('front/exercice/jeu_chrono.html.twig', [
            'exercice' => $exercice,
            'questions' => $questions,
            'contenu_json' => $contenu,
            'enfantId' => $enfantId,
        ]);
    }
    
    #[Route('/api/save-reponse', name: 'api_save_reponse', methods: ['POST'])]
    public function saveReponse(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['exercice_id']) || !isset($data['enfant_id'])) {
            return $this->json(['success' => false, 'message' => 'Données manquantes'], 400);
        }
        
        $conn = $em->getConnection();
        $conn->executeStatement(
            "INSERT INTO reponse_exercice (exercice_id, enfant_id, score, temps_passe, reponses, reussite, date_passage) 
             VALUES (:exercice_id, :enfant_id, :score, :temps_passe, :reponses, :reussite, NOW())",
            [
                'exercice_id' => $data['exercice_id'],
                'enfant_id' => $data['enfant_id'],
                'score' => $data['score'] ?? 0,
                'temps_passe' => $data['temps_passe'] ?? 0,
                'reponses' => json_encode($data['reponses'] ?? []),
                'reussite' => $data['reussite'] ?? false
            ]
        );
        
        return $this->json(['success' => true]);
    }

    #[Route('/api/exercice-complete', name: 'api_exercice_complete', methods: ['POST'])]
    public function exerciceComplete(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->json(['success' => false, 'message' => 'Aucune donnée reçue'], 400);
        }
        
        if (!isset($data['exercice_id']) || !isset($data['enfant_id'])) {
            return $this->json(['success' => false, 'message' => 'Données manquantes'], 400);
        }
        
        try {
            $conn = $em->getConnection();
            
            $conn->executeStatement(
                "INSERT INTO reponse_exercice (exercice_id, enfant_id, score, temps_passe, reponses, reussite, date_passage) 
                 VALUES (:exercice_id, :enfant_id, :score, :temps_passe, :reponses, :reussite, NOW())",
                [
                    'exercice_id' => $data['exercice_id'],
                    'enfant_id' => $data['enfant_id'],
                    'score' => $data['score'] ?? 0,
                    'temps_passe' => $data['temps_passe'] ?? 0,
                    'reponses' => json_encode($data['reponses'] ?? []),
                    'reussite' => $data['reussite'] ?? 0
                ]
            );
            
            $conn->executeStatement(
                "UPDATE exercice_enfant 
                 SET complete = 1, 
                     score_final = :score, 
                     date_completion = NOW() 
                 WHERE exercice_id = :exercice_id AND enfant_id = :enfant_id",
                [
                    'exercice_id' => $data['exercice_id'],
                    'enfant_id' => $data['enfant_id'],
                    'score' => $data['score'] ?? 0
                ]
            );
            
            return $this->json(['success' => true, 'message' => 'Exercice complété et enregistré !']);
            
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }
  #[Route('/api/planning/events', name: 'api_planning_events', methods: ['GET'])]
public function planningEvents(ExerciceEnfantRepository $repo, ExerciceRepository $exerciceRepo): JsonResponse
{
    $user = $this->getUser();
    $enfantId = $user ? $user->getId() : 1;
    
    $assignations = $repo->findBy(['enfant_id' => $enfantId, 'complete' => 0]);
    $events = [];
    
    foreach ($assignations as $assignation) {
        $exercice = $exerciceRepo->find($assignation->getExerciceId());
        if (!$exercice) continue;
        
        $jours = $assignation->getJours() ? explode(',', $assignation->getJours()) : [];
        
        foreach ($jours as $jour) {
            // Calculer la prochaine date pour ce jour
            $date = $this->getNextDateForDay($jour);
            
            $events[] = [
                'title' => $exercice->getTitre(),
                'start' => $date->format('Y-m-d'),
                'url' => $this->generateUrl('app_front_jeu_' . strtolower($exercice->getType()), ['id' => $exercice->getId()]),
                'backgroundColor' => '#FF9800',
                'borderColor' => '#FF9800',
                'textColor' => '#ffffff',
            ];
        }
    }
    
    return $this->json($events);
}

private function getNextDateForDay(string $day): \DateTime
{
    $days = [
        'Lundi' => 1, 'Mardi' => 2, 'Mercredi' => 3,
        'Jeudi' => 4, 'Vendredi' => 5, 'Samedi' => 6, 'Dimanche' => 7
    ];
    
    $today = new \DateTime();
    $currentDay = (int)$today->format('N');
    $targetDay = $days[$day] ?? 1;
    
    if ($targetDay >= $currentDay) {
        $diff = $targetDay - $currentDay;
    } else {
        $diff = 7 - ($currentDay - $targetDay);
    }
    
    if ($diff == 0) {
        // Si c'est aujourd'hui, on garde aujourd'hui
        return $today;
    }
    
    return (clone $today)->modify("+$diff days");
}
private function getFrenchDayName(int $dayNumber): string
{
    $days = [
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
        7 => 'Dimanche'
    ];
    return $days[$dayNumber];
}

}