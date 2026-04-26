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
//use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;
use Knp\Snappy\Pdf;
#[Route('carnet_educatif')]
final class CarnetEducatifController extends AbstractController
{
    #[Route('/', name: 'app_carnet_educatif_index', methods: ['GET'])]
public function index(Request $request, CarnetEducatifRepository $carnetEducatifRepository): Response
{
    $search = $request->query->get('search', '');
    $matiere = $request->query->get('matiere', '');
    $travailTermine = $request->query->get('travail_termine', '');

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

    // Correction ici : utilisez 'date_etude' au lieu de 'dateEtude'
    $qb->orderBy('c.date_etude', 'DESC');
    $carnets = $qb->getQuery()->getResult();

    $matieres = $carnetEducatifRepository->createQueryBuilder('c')
        ->select('DISTINCT c.matiere')
        ->getQuery()
        ->getScalarResult();
    $matieres = array_column($matieres, 'matiere');

        $qb->orderBy('c.date_etude', 'DESC');
        $carnets = $qb->getQuery()->getResult();

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
            $duree = $request->request->all()['carnet_educatif']['duree_totale'] ?? null;
            if ($duree) {
                $carnetEducatif->setDureeTotale((int)$duree);
            } else {
                $carnetEducatif->calculateDureeTotale();
            }
            
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

    #[Route('/{id}/pdf', name: 'app_carnet_educatif_pdf', methods: ['GET'])]
public function downloadPdf(CarnetEducatif $carnetEducatif): Response
{
    try {
        // Chemin CORRECT vers wkhtmltopdf
        $wkhtmlPath = 'C:\wkhtmltopdf\bin\wkhtmltopdf.exe';
        
        // Vérifiez que le fichier existe
        if (!file_exists($wkhtmlPath)) {
            throw new \Exception("wkhtmltopdf non trouvé à : " . $wkhtmlPath);
        }
        
        $snappy = new Pdf($wkhtmlPath);
        
        $html = $this->renderView('carnet_educatif/pdf.html.twig', [
            'carnet_educatif' => $carnetEducatif,
        ]);

        $options = [
            'page-size' => 'A4',
            'orientation' => 'portrait',
            'margin-top' => '15mm',
            'margin-bottom' => '20mm',
            'margin-left' => '12mm',
            'margin-right' => '12mm',
            'encoding' => 'UTF-8',
            'enable-local-file-access' => true,
            'images' => true,
        ];

        $pdfContent = $snappy->getOutputFromHtml($html, $options);

        $filename = sprintf('carnet_educatif_%d_%s.pdf', 
            $carnetEducatif->getId(), 
            $carnetEducatif->getDateEtude()->format('Y-m-d')
        );

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ]);
        
    } catch (\Exception $e) {
        return new Response('Erreur : ' . $e->getMessage(), 500);
    }
}
    // Optionnel : Méthode pour afficher le PDF dans le navigateur (au lieu de télécharger)
    #[Route('/{id}/pdf-view', name: 'app_carnet_educatif_pdf_view', methods: ['GET'])]
    public function viewPdf(CarnetEducatif $carnetEducatif, Pdf $knpSnappyPdf): Response
    {
        try {
            $html = $this->renderView('carnet_educatif/pdf.html.twig', [
                'carnet_educatif' => $carnetEducatif,
            ]);

            $pdfContent = $knpSnappyPdf->getOutputFromHtml($html);

            return new Response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="carnet_' . $carnetEducatif->getId() . '.pdf"',
            ]);
            
        } catch (\Exception $e) {
            return new Response('Erreur : ' . $e->getMessage(), 500);
        }
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
}