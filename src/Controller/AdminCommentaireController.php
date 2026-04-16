<?php


namespace App\Controller;

use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/commentaire', name: 'admin_commentaire_')]
class AdminCommentaireController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(CommentaireRepository $repository): Response
    {
        $commentaires = $repository->findAll();
        return $this->render('admin/commentaire/index.html.twig', [
            'commentaires' => $commentaires,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Commentaire $commentaire): Response
    {
        return $this->render('admin/commentaire/show.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commentaire $commentaire, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de la date/heure séparées (inchangée)
            $date = $form->get('date_seule')->getData();
            $heure = $form->get('heure_seule')->getData();
            if ($date && $heure) {
                $datetime = \DateTime::createFromInterface($date);
                $datetime->setTime((int)$heure->format('H'), (int)$heure->format('i'));
                $commentaire->setDateCommentaire($datetime);
            }
            $em->flush();
            $this->addFlash('success', 'Commentaire modifié.');
            return $this->redirectToRoute('admin_commentaire_index');
        }

        // Pré-remplir date_seule et heure_seule
        if ($commentaire->getDateCommentaire()) {
            $form->get('date_seule')->setData($commentaire->getDateCommentaire());
            $form->get('heure_seule')->setData($commentaire->getDateCommentaire());
        }

        return $this->render('admin/commentaire/edit.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commentaire->getId(), $request->request->get('_token'))) {
            $em->remove($commentaire);
            $em->flush();
            $this->addFlash('success', 'Commentaire supprimé.');
        }
        return $this->redirectToRoute('admin_commentaire_index');
    }
}