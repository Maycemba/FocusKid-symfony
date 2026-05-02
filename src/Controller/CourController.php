<?php

namespace App\Controller;

use App\Entity\Cour;
use App\Form\CourType;
use App\Repository\CourRepository;
use App\Service\CourSearchService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

#[Route('/cour')]
final class CourController extends AbstractController
{
    #[Route(name: 'app_cour_index', methods: ['GET'])]
    public function index(
        Request $request,
        CourRepository $courRepository,
        PaginatorInterface $paginator,
        CourSearchService $searchService
    ): Response {
        $search = trim($request->query->get('search', ''));
        $niveau = trim($request->query->get('niveau', ''));
        $statut = trim($request->query->get('statut', ''));

        $isSearching = $search !== '' || $niveau !== '' || $statut !== '';
        $searchFallback = false;

        if ($isSearching) {
            try {
                $results    = $searchService->search($search, $niveau, $statut);
                $pagination = $paginator->paginate(
                    $results,
                    $request->query->getInt('page', 1),
                    6
                );
            } catch (Throwable) {
                $searchFallback = true;
                $query = $this->buildCourFallbackQuery($courRepository, $search, $niveau, $statut);
                $pagination = $paginator->paginate(
                    $query,
                    $request->query->getInt('page', 1),
                    6
                );
            }
        } else {
            $query = $courRepository->createQueryBuilder('c')
                ->orderBy('c.titre', 'ASC')
                ->getQuery();

            $pagination = $paginator->paginate(
                $query,
                $request->query->getInt('page', 1),
                6
            );
        }

        // Requête AJAX → retourne JSON
        if ($request->isXmlHttpRequest()) {
            $data = [];
            foreach ($pagination as $cour) {
                $data[] = [
                    'id'          => $cour->getIdCours(),
                    'titre'       => $cour->getTitre(),
                    'niveau'      => $cour->getNiveau(),
                    'description' => $cour->getDescription(),
                    'formateur'   => $cour->getFormateur(),
                    'statut'      => $cour->getStatut(),
                    'urlShow'     => $this->generateUrl('app_cour_show', ['id_cours' => $cour->getIdCours()]),
                    'urlEdit'     => $this->generateUrl('app_cour_edit', ['id_cours' => $cour->getIdCours()]),
                ];
            }

            return $this->json([
                'cours' => $data,
                'total' => $pagination->getTotalItemCount(),
                'fallback' => $searchFallback,
            ]);
        }

        // Requête normale → retourne la vue
        return $this->render('cour/index.html.twig', [
            'cours'  => $pagination,
            'search' => $search,
            'niveau' => $niveau,
            'statut' => $statut,
            'search_fallback' => $searchFallback,
        ]);
    }

    private function buildCourFallbackQuery(
        CourRepository $courRepository,
        string $search,
        string $niveau,
        string $statut
    ) {
        $qb = $courRepository->createQueryBuilder('c')
            ->orderBy('c.titre', 'ASC');

        if ($search !== '') {
            $qb->andWhere('LOWER(c.titre) LIKE :search OR LOWER(c.formateur) LIKE :search OR LOWER(c.description) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower($search) . '%');
        }

        if ($niveau !== '') {
            $qb->andWhere('c.niveau = :niveau')
                ->setParameter('niveau', $niveau);
        }

        if ($statut !== '') {
            $qb->andWhere('LOWER(c.statut) = :statut')
                ->setParameter('statut', mb_strtolower($statut));
        }

        return $qb->getQuery();
    }

    #[Route('/new', name: 'app_cour_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cour = new Cour();
        $form = $this->createForm(CourType::class, $cour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cour);
            $entityManager->flush();

            return $this->redirectToRoute('app_lecon_new', [
                'id_cours' => $cour->getId_cours(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cour/new.html.twig', [
            'cour' => $cour,
            'form' => $form,
        ]);
    }

    #[Route('/{id_cours}', name: 'app_cour_show', methods: ['GET'])]
    public function show(Cour $cour): Response
    {
        return $this->render('cour/show.html.twig', [
            'cour' => $cour,
        ]);
    }

    #[Route('/{id_cours}/edit', name: 'app_cour_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cour $cour, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CourType::class, $cour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_cour_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cour/edit.html.twig', [
            'cour' => $cour,
            'form' => $form,
        ]);
    }

    #[Route('/{id_cours}', name: 'app_cour_delete', methods: ['POST'])]
    public function delete(Request $request, Cour $cour, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $cour->getId_cours(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($cour);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cour_index', [], Response::HTTP_SEE_OTHER);
    }
}