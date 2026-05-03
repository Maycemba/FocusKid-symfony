<?php

namespace App\Controller;

use App\Repository\CarnetEducatifRepository;
use App\Repository\CourRepository;
use App\Repository\ExerciceRepository;
use App\Repository\JeuRepository;
use App\Repository\LeconRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

final class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'app_admin_dashboard', methods: ['GET'])]
    public function index(
        CourRepository $courRepository,
        LeconRepository $leconRepository,
        ExerciceRepository $exerciceRepository,
        JeuRepository $jeuRepository,
        CarnetEducatifRepository $carnetEducatifRepository,
        UtilisateurRepository $utilisateurRepository,
        ChartBuilderInterface $chartBuilder
    ): Response {
        $cours = $courRepository->findAll();

        $niveaux = [];
        foreach ($cours as $cour) {
            $niveau = $cour->getNiveau() ?: 'Non defini';
            $niveaux[$niveau] = ($niveaux[$niveau] ?? 0) + 1;
        }

        ksort($niveaux);

        $coursesByLevelChart = $chartBuilder->createChart(Chart::TYPE_BAR);

        $coursesByLevelChart->setData([
            'labels' => array_keys($niveaux),
            'datasets' => [[
                'label' => 'Cours par niveau',
                'data' => array_values($niveaux),
                'backgroundColor' => [
                    '#2196F3',
                    '#4CAF50',
                    '#FF9800',
                    '#9C27B0',
                    '#F44336',
                    '#00BCD4',
                    '#3F51B5',
                    '#8BC34A',
                    '#FFC107',
                    '#795548',
                    '#E91E63',
                    '#009688',
                    '#673AB7',
                    '#CDDC39',
                ],
                'borderRadius' => 8,
            ]],
        ]);

        $coursesByLevelChart->setOptions([
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ]);

        return $this->render('dashboard/index.html.twig', [
            'stats' => [
                'cours' => $courRepository->count([]),
                'lecons' => $leconRepository->count([]),
                'exercices' => $exerciceRepository->count([]),
                'jeux' => $jeuRepository->count([]),
                'carnets' => $carnetEducatifRepository->count([]),
                'utilisateurs' => $utilisateurRepository->count([]),
            ],
            'recentCours' => $courRepository->findBy([], ['id_cours' => 'DESC'], 5),
            'recentLecons' => $leconRepository->findBy([], ['id_lecon' => 'DESC'], 5),
            'recentUtilisateurs' => $utilisateurRepository->findBy([], ['id' => 'DESC'], 5),
            'coursesByLevelChart' => $coursesByLevelChart,
        ]);
    }
}
