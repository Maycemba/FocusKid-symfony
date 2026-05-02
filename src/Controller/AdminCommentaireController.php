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
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/commentaire', name: 'admin_commentaire_')]

class AdminCommentaireController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, CommentaireRepository $repository): Response
    {
        $search   = $request->query->get('search');
        $carnetId = $request->query->get('carnet_id');
        $type     = $request->query->get('type');

        $qb = $repository->createQueryBuilder('c')
            ->leftJoin('c.carnetEducatif', 'car')  // nom correct selon l'entité
            ->addSelect('car')
            ->orderBy('c.date_commentaire', 'DESC');

        if ($search) {
            $qb->andWhere('c.texte_commentaire LIKE :search')
               ->setParameter('search', '%'.$search.'%');
        }
        if ($carnetId) {
            $qb->andWhere('car.id = :carnetId')
               ->setParameter('carnetId', $carnetId);
        }
        if ($type) {
            $qb->andWhere('c.type_commentaire = :type')
               ->setParameter('type', $type);
        }

        $commentaires = $qb->getQuery()->getResult();

        return $this->render('AdminCarnet/indexCommentaire.html.twig', [
            'commentaires' => $commentaires,
            'search'       => $search,
            'carnet_id'    => $carnetId,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Commentaire $commentaire): Response
    {
        return $this->render('AdminCarnet/showCommentaire.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commentaire $commentaire, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Commentaire modifié avec succès.');
            return $this->redirectToRoute('admin_commentaire_show', ['id' => $commentaire->getId()]);
        }

        return $this->render('AdminCarnet/editCommentaire.html.twig', [
            'commentaire' => $commentaire,
            'form'        => $form->createView(),
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