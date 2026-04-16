<?php
// src/Controller/CarnetEducatifController.php

namespace App\Controller;

use App\Entity\CarnetEducatif;
use App\Form\CarnetEducatifType;
use App\Repository\CarnetEducatifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Dompdf\Dompdf;          // ← Ajout
use Dompdf\Options;         // ← Ajout
use Twig\Environment;       // ← Ajout (pour le rendu du template PDF)

#[Route('/carnet_educatif')]
final class CarnetEducatifController extends AbstractController
{
    #[Route('/', name: 'app_carnet_educatif_index', methods: ['GET'])]
public function index(Request $request, CarnetEducatifRepository $carnetEducatifRepository): Response
{
    // Récupération des paramètres de filtre
    $search = $request->query->get('search', '');
    $matiere = $request->query->get('matiere', '');
    $travailTermine = $request->query->get('travail_termine', '');

    // Construction de la requête
    $qb = $carnetEducatifRepository->createQueryBuilder('c');

    if ($search) {
        $qb->andWhere('c.matiere LIKE :search OR c.lieu LIKE :search OR c.type_activite LIKE :search')
           ->setParameter('search', '%'.$search.'%');
    }
    if ($matiere) {
        $qb->andWhere('c.matiere = :matiere')
           ->setParameter('matiere', $matiere);
    }
    if ($travailTermine !== '') {
        $qb->andWhere('c.travail_termine = :tt')
           ->setParameter('tt', $travailTermine === '1');
    }

    $qb->orderBy('c.date_etude', 'DESC');

    $carnets = $qb->getQuery()->getResult();

    // Liste des matières distinctes pour le filtre déroulant
    $matieres = $carnetEducatifRepository->createQueryBuilder('c')
        ->select('DISTINCT c.matiere')
        ->getQuery()
        ->getScalarResult();
    $matieres = array_column($matieres, 'matiere');

    return $this->render('carnet_educatif/index.html.twig', [
        'carnet_educatifs' => $carnets,
        'matieres' => $matieres,
        'search' => $search,
        'selected_matiere' => $matiere,
        'selected_travail_termine' => $travailTermine,
    ]);
}

    #[Route('/new', name: 'app_carnet_educatif_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $carnetEducatif = new CarnetEducatif();
        $form = $this->createForm(CarnetEducatifType::class, $carnetEducatif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $carnetEducatif->calculateDureeTotale();
            $entityManager->persist($carnetEducatif);
            $entityManager->flush();

            $this->addFlash('success', 'Carnet créé avec succès.');
            return $this->redirectToRoute('app_carnet_educatif_index');
        }

        return $this->render('carnet_educatif/new.html.twig', [
            'carnet_educatif' => $carnetEducatif,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_carnet_educatif_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CarnetEducatif $carnetEducatif, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CarnetEducatifType::class, $carnetEducatif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $carnetEducatif->calculateDureeTotale();
            $entityManager->flush();

            $this->addFlash('success', 'Carnet modifié.');
            return $this->redirectToRoute('app_carnet_educatif_index');
        }

        return $this->render('carnet_educatif/edit.html.twig', [
            'carnet_educatif' => $carnetEducatif,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_carnet_educatif_show', methods: ['GET'])]
    public function show(CarnetEducatif $carnetEducatif): Response
    {
        return $this->render('carnet_educatif/show.html.twig', [
            'carnet_educatif' => $carnetEducatif,
        ]);
    }

    #[Route('/{id}', name: 'app_carnet_educatif_delete', methods: ['POST'])]
    public function delete(Request $request, CarnetEducatif $carnetEducatif, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$carnetEducatif->getId(), $request->request->get('_token'))) {
            $entityManager->remove($carnetEducatif);
            $entityManager->flush();
            $this->addFlash('success', 'Carnet supprimé.');
        }
        return $this->redirectToRoute('app_carnet_educatif_index');
    }

    #[Route('/{id}/pdf', name: 'app_carnet_educatif_pdf', methods: ['GET'])]
    public function downloadPdf(CarnetEducatif $carnetEducatif, Environment $twig): Response
    {
        // Configuration de Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isHtml5ParserEnabled', true);
        $pdfOptions->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($pdfOptions);

        // Récupération du HTML à partir du template dédié au PDF
        $html = $twig->render('carnet_educatif/pdf.html.twig', [
            'carnet_educatif' => $carnetEducatif,
        ]);

        // Chargement du HTML dans Dompdf
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Génération du nom du fichier
        $filename = sprintf('carnet_%d_%s.pdf', $carnetEducatif->getId(), date('Y-m-d'));

        // Retourne le PDF en réponse
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ]);
    }
}