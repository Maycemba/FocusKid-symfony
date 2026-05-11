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
use App\Service\PredictionService;
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
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager, 
        EmailService $emailService,
        PredictionService $predictionService  // 🔥 AJOUTER CETTE DEPENDANCE
    ): Response {
        
        if ($request->isMethod('POST')) {
            $postData = $request->request->all();
            $exerciceData = $postData['exercice'] ?? [];
            $contenuJson = $postData['contenuJson'] ?? null;
            
            if (!$contenuJson) {
                $contenuJson = $exerciceData['contenu'] ?? null;
            }
            
            // ========== 1. CRÉATION DE L'EXERCICE ==========
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
            $user = $this->getUser();
            $exercice->setCreePar($user instanceof Utilisateur ? $user->getId() : 1);
            
            if ($contenuJson && $contenuJson !== '{}' && $contenuJson !== 'null') {
                $exercice->setContenu($contenuJson);
            } else {
                $exercice->setContenu('{"dureeTotale":60,"defis":[{"question":"2 + 2 = ?","reponse":"4","points":10}]}');
            }
            
            $entityManager->persist($exercice);
            $entityManager->flush();
            
            // ========== 2. RÉCUPÉRATION DES JOURS ==========
            $joursString = $postData['jours'] ?? '';
            if (is_array($joursString)) {
                $joursString = implode(',', $joursString);
            }
            
            // ========== 3. RÉCUPÉRATION DES ENFANTS ASSIGNÉS ==========
            $enfantsAssignes = [];
            $tousLesStats = [];  // Pour stocker toutes les prédictions
            
            if ($exercice->isPourTousEnfants()) {
                $tousLesEnfants = $entityManager->getRepository(Utilisateur::class)->findBy(['role' => 2]);
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
            
            // ========== 4. 🔥 PRÉDICTION POUR CHAQUE ENFANT ==========
            $typeCode = $predictionService->getTypeCode($exercice->getType());
            $statsTousEnfants = $this->getStatsTousEnfants($entityManager, $enfantsIds);
            
            $predictions = [];
            foreach ($enfantsAssignes as $enfant) {
                $stats = $statsTousEnfants[$enfant->getId()] ?? [
                    'score_moyen' => 10,      // Score par défaut (moyenne)
                    'temps_moyen' => 60,      // Temps par défaut (60s)
                    'nb_exercices' => 0
                ];
                
                try {
                    $prediction = $predictionService->predire(
                        $exercice->getDifficulte(),
                        $typeCode,
                        $stats['score_moyen'],
                        $stats['temps_moyen']
                    );
                    
                    $predictions[$enfant->getId()] = $prediction;
                    
                    // Log pour debug
                    $this->addFlash('info', sprintf(
                        '🔮 Prédiction pour %s : %s (%.1f%%)',
                        $enfant->getUsername(),
                        $prediction['reussite'] ? '✅ Réussite' : '❌ Échec',
                        $prediction['probabilite']
                    ));
                    
                } catch (\Exception $e) {
                    // En cas d'erreur, prédiction par défaut
                    $predictions[$enfant->getId()] = [
                        'reussite' => $stats['score_moyen'] >= 10,
                        'probabilite' => $stats['score_moyen'] * 5,
                        'success' => false
                    ];
                }
            }
            
            // ========== 5. CRÉATION DES ASSIGNATIONS AVEC PRÉDICTIONS ==========
            if (!empty($enfantsIds)) {
                $emailCount = 0;
                $reussiteCount = 0;
                $echecCount = 0;
                
                foreach ($enfantsIds as $enfantId) {
                    $enfant = $entityManager->getRepository(Utilisateur::class)->find($enfantId);
                    if ($enfant) {
                        $exerciceEnfant = new ExerciceEnfant();
                        $exerciceEnfant->setExerciceId($exercice->getId());
                        $exerciceEnfant->setEnfantId($enfant->getId());
                        $exerciceEnfant->setDateAttribution(new \DateTime());
                        $exerciceEnfant->setJours($joursString);
                        
                        // 🔥 STOCKER LES PRÉDICTIONS DANS LA BASE
                        $prediction = $predictions[$enfantId] ?? null;
                        if ($prediction) {
                            $exerciceEnfant->setPredictionReussite($prediction['reussite']);
                            $exerciceEnfant->setPredictionProbabilite($prediction['probabilite']);
                            $exerciceEnfant->setPredictionDate(new \DateTime());
                            
                            if ($prediction['reussite']) {
                                $reussiteCount++;
                            } else {
                                $echecCount++;
                            }
                        }
                        
                        $entityManager->persist($exerciceEnfant);
                        
                        // Envoyer email
                        $emailSent = $emailService->sendNewExerciseNotification(
                            $enfant->getEmail(),
                            $enfant->getUsername(),
                            $exercice->getTitre(),
                            $exercice->getType(),
                            $exercice->getConsigne(),
                            (new \DateTime())->format('d/m/Y à H:i'),
                            $joursString ?: 'Non spécifiés'
                        );
                        if ($emailSent) {
                            $emailCount++;
                        }
                    }
                }
                $entityManager->flush();
                
                // ========== 6. AFFICHAGE RÉCAPITULATIF ==========
                $this->addFlash('success', sprintf(
                    '✅ Exercice "%s" créé ! %d enfant(s) assigné(s).',
                    $exercice->getTitre(),
                    count($enfantsIds)
                ));
                
                $this->addFlash('info', sprintf(
                    '🔮 Résumé des prédictions : %d réussite(s) attendue(s), %d échec(s) prévu(s)',
                    $reussiteCount,
                    $echecCount
                ));
                
                $this->addFlash('success', sprintf('📧 %d email(s) envoyé(s) aux enfants.', $emailCount));
                
            } else {
                $this->addFlash('success', '✅ Exercice créé avec succès ! (aucun enfant assigné)');
            }
            
            return $this->redirectToRoute('app_exercice_index');
        }
        
        $exercice = new Exercice();
        $form = $this->createForm(ExerciceType::class, $exercice);
        
        // 🔥 Récupérer tous les enfants pour le formulaire
        $enfants = $entityManager->getRepository(Utilisateur::class)->findBy(['role' => 2]);
        
        return $this->render('exercice/new.html.twig', [
            'exercice' => $exercice,
            'form' => $form->createView(),
            'enfants' => $enfants,  // Pour la sélection dans le formulaire
        ]);
    }
    /**
 * Récupérer les stats de tous les enfants en une seule requête
 */
private function getStatsTousEnfants(EntityManagerInterface $em, array $enfantsIds): array
{
    if (empty($enfantsIds)) return [];
    
    $conn = $em->getConnection();
    $placeholders = implode(',', array_fill(0, count($enfantsIds), '?'));
    
    $sql = "
        SELECT 
            enfant_id,
            COALESCE(AVG(score), 10) as score_moyen,
            COALESCE(AVG(temps_passe), 60) as temps_moyen,
            COUNT(*) as nb_exercices
        FROM reponse_exercice
        WHERE enfant_id IN ($placeholders)
        GROUP BY enfant_id
    ";
    
    $stmt = $conn->prepare($sql);
    $result = $stmt->executeQuery($enfantsIds);
    
    $stats = [];
    while ($row = $result->fetchAssociative()) {
        $stats[$row['enfant_id']] = [
            'score_moyen' => round($row['score_moyen'], 1),
            'temps_moyen' => round($row['temps_moyen']),
            'nb_exercices' => $row['nb_exercices']
        ];
    }
    
    return $stats;
}

    // Ajoute cette méthode pour assigner à tous les enfants
    private function assignToAllChildren(Exercice $exercice, EntityManagerInterface $em): void
    {
        $enfantRepo = $em->getRepository(Utilisateur::class);
        $enfants = $enfantRepo->findBy(['role' => 2]);
        
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
            $postData = $request->request->all();
            $joursSelectionnes = $postData['jours'] ?? [];
            if (!is_array($joursSelectionnes)) {
                $joursSelectionnes = [$joursSelectionnes];
            }
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
/**
 * Récupérer les statistiques d'un enfant (pour les prédictions)
 */
#[Route('/api/enfant-stats/{id}', name: 'api_enfant_stats', methods: ['GET'])]
public function getEnfantStats(int $id, EntityManagerInterface $em): JsonResponse
{
    $conn = $em->getConnection();
    $sql = "
        SELECT 
            COALESCE(AVG(score), 10) as score_moyen,
            COALESCE(AVG(temps_passe), 60) as temps_moyen,
            COUNT(*) as nb_exercices
        FROM reponse_exercice
        WHERE enfant_id = :enfant_id
    ";
    
    $result = $conn->executeQuery($sql, ['enfant_id' => $id])->fetchAssociative();
    
    return $this->json([
        'score_moyen' => round($result['score_moyen'], 1),
        'temps_moyen' => round($result['temps_moyen']),
        'nb_exercices' => $result['nb_exercices']
    ]);
}
    // ========== FRONT OFFICE (ENFANT) ==========

    // Dans ExerciceController.php, modifiez la méthode frontIndex :

#[Route('/front/exercices', name: 'app_front_exercice_index', methods: ['GET'])]
public function frontIndex(Request $request, ExerciceRepository $exerciceRepository, ExerciceEnfantRepository $exerciceEnfantRepository): Response
{
    $user = $this->getUser();
    $enfantId = $user instanceof Utilisateur ? $user->getId() : 1;
    
    $type = $request->query->get('type', 'non_complete');
    $showPlanning = $request->query->get('showPlanning', 0);
    
    // Récupérer les exercices assignés à l'enfant
    $exercicesEnfant = $exerciceEnfantRepository->findBy(['enfant_id' => $enfantId]);
    $exercicesIds = [];
    $completionStatus = [];
    $joursParExercice = [];
    
    foreach ($exercicesEnfant as $ee) {
        $exercicesIds[] = $ee->getExerciceId();
        $completionStatus[$ee->getExerciceId()] = $ee->isComplete();
        $joursParExercice[$ee->getExerciceId()] = $ee->getJoursArray();
    }
    
    // 🔥 IMPORTANT: Récupérer TOUS les exercices assignés (même complétés)
    $allExercices = [];
    if (count($exercicesIds) > 0) {
        $allExercices = $exerciceRepository->findBy([
            'id' => $exercicesIds, 
            'actif' => true,
            'archive' => false
        ]);
    }
    
    // Filtrer selon le type (non_complete ou complete)
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
    foreach ($allExercices as $exo) {
        $isComplete = $completionStatus[$exo->getId()] ?? false;
        if ($isComplete) {
            $completeCount++;
        } else {
            $nonCompleteCount++;
        }
    }
    
    // 🔥 DEBUG: Ajoutez ces logs pour vérifier
    $this->addFlash('debug', 'Exercices trouvés: ' . count($allExercices));
    $this->addFlash('debug', 'Type actuel: ' . $type);
    $this->addFlash('debug', 'À faire: ' . $nonCompleteCount . ', Faits: ' . $completeCount);
    
    return $this->render('front/exercice/index.html.twig', [
        'exercices' => $exercices,
        'currentType' => $type,
        'nonCompleteCount' => $nonCompleteCount,
        'completeCount' => $completeCount,
        'allExercices' => $allExercices,
        'joursParExercice' => $joursParExercice,
        'showPlanning' => (int)$showPlanning,
    ]);
}
    #[Route('/front/jeu/memoire/{id}', name: 'app_front_jeu_memoire', methods: ['GET'])]
    public function jeuMemoire(int $id, ExerciceRepository $exerciceRepository): Response
    {
        $user = $this->getUser();
        $enfantId = $user instanceof Utilisateur ? $user->getId() : 1;
        
        $exercice = $exerciceRepository->find($id);
        
        if (!$exercice) {
            throw $this->createNotFoundException('Exercice non trouvé');
        }
        
        $contenu = $exercice->getContenu();
        $questions = [];
        
        if ($contenu) {
            $data = json_decode($contenu, true);
            
            // Format 1: tableau simple d'images
            if ($data && isset($data['images']) && is_array($data['images'])) {
                $questions = $data['images'];
            } 
            // Format 2: tableau avec 'valeur'
            elseif ($data && isset($data['questions']) && is_array($data['questions'])) {
                foreach ($data['questions'] as $q) {
                    $questions[] = $q['valeur'] ?? $q['image'] ?? '?';
                }
            }
            // Format 3: avec thème
            elseif ($data && isset($data['theme'])) {
                $themes = [
                    'animaux' => ['🐶','🐱','🐭','🐹','🐰','🦊','🐻','🐼'],
                    'fruits' => ['🍎','🍐','🍊','🍋','🍌','🍉','🍇','🍓'],
                    'chiffres' => ['1️⃣','2️⃣','3️⃣','4️⃣','5️⃣','6️⃣','7️⃣','8️⃣']
                ];
                $theme = $data['theme'] ?? 'animaux';
                $questions = $themes[$theme] ?? $themes['animaux'];
            }
        }
        
        // Si toujours vide, utiliser valeurs par défaut
        if (empty($questions)) {
            $questions = ['🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼'];
        }
        
        return $this->render('front/exercice/jeu_memoire.html.twig', [
            'exercice' => $exercice,
            'questions' => $questions,
            'enfantId' => $enfantId,
        ]);
    }

    #[Route('/front/jeu/attention/{id}', name: 'app_front_jeu_attention', methods: ['GET'])]
    public function jeuAttention(int $id, ExerciceRepository $exerciceRepository): Response
    {
        $user = $this->getUser();
        $enfantId = $user instanceof Utilisateur ? $user->getId() : 1;
        
        $exercice = $exerciceRepository->find($id);
        
        if (!$exercice) {
            throw $this->createNotFoundException('Exercice non trouvé');
        }
        
        $contenu = $exercice->getContenu();
        $questions = [];
        
        if ($contenu) {
            $data = json_decode($contenu, true);
            
            // Format pour le jeu d'intrus (ton contenu actuel)
            if ($data && isset($data['questions']) && is_array($data['questions'])) {
                $questions = $data['questions'];
            }
            // Format simple
            elseif ($data && isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $img) {
                    $questions[] = ['image' => $img, 'valeur' => $img];
                }
            }
            // Format avec thème
            elseif ($data && isset($data['theme'])) {
                $themes = [
                    'emojis' => ['🐶','🐱','🐭','🐹','🐰','🦊','🐻','🐼'],
                    'animaux' => ['🦁','🐧','🐦','🐟','🐠','🐙','🦋','🐝'],
                    'objets' => ['📚','✏️','🔍','💡','🔑','⌚','📱','💻']
                ];
                $theme = $data['theme'] ?? 'emojis';
                foreach ($themes[$theme] as $img) {
                    $questions[] = ['image' => $img, 'valeur' => $img];
                }
            }
        }
        
        // Si toujours vide, afficher une erreur
        if (empty($questions)) {
            $this->addFlash('error', 'Cet exercice ne contient pas de données valides.');
            return $this->redirectToRoute('app_front_exercice_index');
        }
        
        return $this->render('front/exercice/jeu_attention.html.twig', [
            'exercice' => $exercice,
            'questions' => $questions,
            'enfantId' => $enfantId,
        ]);
    }

    #[Route('/front/jeu/logique/{id}', name: 'app_front_jeu_logique', methods: ['GET'])]
    public function jeuLogique(int $id, ExerciceRepository $exerciceRepository): Response
    {
        $user = $this->getUser();
        $enfantId = $user instanceof Utilisateur ? $user->getId() : 1;
        
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
        $user = $this->getUser();
        $enfantId = $user instanceof Utilisateur ? $user->getId() : 1;
        
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
    if (!$user) {
        return $this->json([]);
    }
    
    $enfantId = $user instanceof Utilisateur ? $user->getId() : 1;
    
    $assignations = $repo->findBy(['enfant_id' => $enfantId, 'complete' => 0]);
    $events = [];
    
    foreach ($assignations as $assignation) {
        $exercice = $exerciceRepo->find($assignation->getExerciceId());
        if (!$exercice || !$exercice->isActif() || $exercice->isArchive()) {
            continue;
        }
        
        $jours = $assignation->getJoursArray();
        
        foreach ($jours as $jour) {
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
}    private function getNextDateForDay(string $day): \DateTime
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
