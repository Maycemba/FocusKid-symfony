<?php
// src/Controller/Admin/AdminCarnetController.php

namespace App\Controller;

use App\Entity\CarnetEducatif;
use App\Form\CarnetEducatifType;
use App\Repository\CarnetEducatifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/carnet_educatif', name: 'admin_carnet_')]
class AdminCarnetController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(CarnetEducatifRepository $repository): Response
    {
        // Admin voit tous les carnets
        $carnets = $repository->findAll();
        return $this->render('front/indexAdmin.html.twig', [
            'carnet_educatifs' => $carnets,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(CarnetEducatif $carnet): Response
    {
        return $this->render('car/show.html.twig', [
            'carnet_educatif' => $carnet,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, CarnetEducatif $carnet, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CarnetEducatifType::class, $carnet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $carnet->calculateDureeTotale(); // recalcul de la durée
            $em->flush();
            $this->addFlash('success', 'Carnet modifié avec succès.');
            return $this->redirectToRoute('admin_carnet_index');
        }

        return $this->render('carnet_educatif/edit.html.twig', [
            'carnet_educatif' => $carnet,
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
}