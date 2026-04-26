<?php

namespace App\Controller;

use App\Entity\CarnetEducatif;
use App\Form\CarnetEducatifType;
use App\Repository\CarnetEducatifRepository;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/carnet', name: 'admin_carnet_')]
class AdminCarnetController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        CarnetEducatifRepository $repository,
        CommentaireRepository $commentaireRepository
    ): Response {
        $search = $request->query->get('search');
        $matiere = $request->query->get('matiere');
        $travailTermine = $request->query->get('travail_termine');

        $qb = $repository->createQueryBuilder('c');

        if ($search) {
            $qb->andWhere('c.matiere LIKE :search OR c.type_activite LIKE :search OR c.lieu LIKE :search')
               ->setParameter('search', '%'.$search.'%');
        }
        if ($matiere) {
            $qb->andWhere('c.matiere = :matiere')
               ->setParameter('matiere', $matiere);
        }
        if ($travailTermine !== null && $travailTermine !== '') {
            $qb->andWhere('c.travail_termine = :tt')
               ->setParameter('tt', (bool) $travailTermine);
        }

        $carnets = $qb->getQuery()->getResult();

        $matieres = $repository->createQueryBuilder('c')
            ->select('DISTINCT c.matiere')
            ->getQuery()
            ->getSingleColumnResult();

        $totalCarnets = $repository->count([]);
        $totalCommentaires = $commentaireRepository->count([]);

        return $this->render('AdminCarnet/indexAdmin.html.twig', [
            'carnets' => $carnets,
            'search' => $search,
            'matiere' => $matiere,
            'matieres' => $matieres,
            'travail_termine' => $travailTermine,
            'total_carnets' => $totalCarnets,
            'total_commentaires' => $totalCommentaires,
        ]);
    }

    #[Route('/statistiques', name: 'stats', methods: ['GET'])]
    public function stats(
        CarnetEducatifRepository $carnetRepo,
        CommentaireRepository $commentaireRepo
    ): Response {
        // Statistiques de base
        $totalCarnets = $carnetRepo->count([]);
        $totalCommentaires = $commentaireRepo->count([]);
        
        // Distribution par matière
        $statsParMatiere = $carnetRepo->createQueryBuilder('c')
            ->select('c.matiere, COUNT(c.id) as count')
            ->groupBy('c.matiere')
            ->getQuery()
            ->getResult();
        
        $statsParMatiereArray = [];
        foreach ($statsParMatiere as $stat) {
            $statsParMatiereArray[$stat['matiere']] = $stat['count'];
        }
        
        // Distribution des niveaux de concentration
        $statsConcentration = [];
        for ($i = 1; $i <= 5; $i++) {
            $count = $carnetRepo->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->where('c.niveau_concentration = :niveau')
                ->setParameter('niveau', $i)
                ->getQuery()
                ->getSingleScalarResult();
            $statsConcentration[$i] = $count;
        }
        
        // Distribution des niveaux d'agitation
        $statsAgitation = [];
        for ($i = 1; $i <= 5; $i++) {
            $count = $carnetRepo->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->where('c.niveau_agitation = :niveau')
                ->setParameter('niveau', $i)
                ->getQuery()
                ->getSingleScalarResult();
            $statsAgitation[$i] = $count;
        }
        
        // Distribution des niveaux d'autonomie
        $statsAutonomie = [];
        for ($i = 1; $i <= 5; $i++) {
            $count = $carnetRepo->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->where('c.niveau_autonomie = :niveau')
                ->setParameter('niveau', $i)
                ->getQuery()
                ->getSingleScalarResult();
            $statsAutonomie[$i] = $count;
        }
        
        // Statistiques par mois
        $statsParMois = $carnetRepo->createQueryBuilder('c')
            ->select('SUBSTRING(c.date_etude, 1, 7) as mois, COUNT(c.id) as count')
            ->groupBy('mois')
            ->orderBy('mois', 'DESC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();
        
        $statsParMoisArray = [];
        foreach ($statsParMois as $stat) {
            $statsParMoisArray[$stat['mois']] = $stat['count'];
        }
        
        // Niveaux de difficulté
        $statsDifficulte = $carnetRepo->createQueryBuilder('c')
            ->select('c.niveau_difficulte, COUNT(c.id) as count')
            ->groupBy('c.niveau_difficulte')
            ->getQuery()
            ->getResult();
        
        $statsDifficulteArray = [];
        foreach ($statsDifficulte as $stat) {
            $statsDifficulteArray[$stat['niveau_difficulte']] = $stat['count'];
        }
        
        // Travail terminé vs non terminé
        $totalTermine = $carnetRepo->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.travail_termine = :termine')
            ->setParameter('termine', true)
            ->getQuery()
            ->getSingleScalarResult();
        
        $tauxTermine = $totalCarnets > 0 ? round(($totalTermine / $totalCarnets) * 100, 2) : 0;
        
        // Moyennes
        $moyenneConcentration = $carnetRepo->createQueryBuilder('c')
            ->select('AVG(c.niveau_concentration)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
        
        $moyenneAgitation = $carnetRepo->createQueryBuilder('c')
            ->select('AVG(c.niveau_agitation)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
        
        $moyenneAutonomie = $carnetRepo->createQueryBuilder('c')
            ->select('AVG(c.niveau_autonomie)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
        
        // Interruptions moyennes
        $moyenneInterruptions = $carnetRepo->createQueryBuilder('c')
            ->select('AVG(c.nombre_interruptions)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
        
        // Top matières avec meilleure concentration
        $topMatieres = $carnetRepo->createQueryBuilder('c')
            ->select('c.matiere, AVG(c.niveau_concentration) as avg_concentration')
            ->groupBy('c.matiere')
            ->orderBy('avg_concentration', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
        
        $topMatieresArray = [];
        foreach ($topMatieres as $stat) {
            $topMatieresArray[$stat['matiere']] = round($stat['avg_concentration'], 2);
        }
        
        return $this->render('AdminCarnet/stats.html.twig', [
            // Statistiques de base
            'total_carnets' => $totalCarnets,
            'total_commentaires' => $totalCommentaires,
            'taux_termine' => $tauxTermine,
            'total_termine' => $totalTermine,
            'total_non_termine' => $totalCarnets - $totalTermine,
            
            // Distributions
            'stats_par_matiere' => $statsParMatiereArray,
            'stats_concentration' => $statsConcentration,
            'stats_agitation' => $statsAgitation,
            'stats_autonomie' => $statsAutonomie,
            'stats_par_mois' => $statsParMoisArray,
            'stats_difficulte' => $statsDifficulteArray,
            
            // Moyennes
            'moyenne_concentration' => round($moyenneConcentration, 2),
            'moyenne_agitation' => round($moyenneAgitation, 2),
            'moyenne_autonomie' => round($moyenneAutonomie, 2),
            'moyenne_interruptions' => round($moyenneInterruptions, 2),
            
            // Top matières
            'top_matieres' => $topMatieresArray,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(CarnetEducatif $carnet): Response
    {
        return $this->render('AdminCarnet/showCarnet.html.twig', [
            'carnet' => $carnet,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CarnetEducatif $carnet, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CarnetEducatifType::class, $carnet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (method_exists($carnet, 'calculateDureeTotale')) {
                $carnet->calculateDureeTotale();
            }
            $em->flush();
            $this->addFlash('success', 'Carnet modifié.');
            return $this->redirectToRoute('admin_carnet_index');
        }

        return $this->render('AdminCarnet/editCarnet.html.twig', [
            'carnet' => $carnet,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, CarnetEducatif $carnet, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$carnet->getId(), $request->request->get('_token'))) {
            $em->remove($carnet);
            $em->flush();
            $this->addFlash('success', 'Carnet supprimé.');
        }
        return $this->redirectToRoute('admin_carnet_index');
    }

    #[Route('/{id}/confirm-delete', name: 'delete_confirm', methods: ['GET'])]
    public function deleteConfirm(CarnetEducatif $carnet): Response
    {
        return $this->render('AdminCarnet/deleteCarnet.html.twig', [
            'carnet' => $carnet,
        ]);
    }
}