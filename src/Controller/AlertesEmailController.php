<?php

namespace App\Controller;

use App\Entity\AlertesEmail;
use App\Form\AlertesEmailType;
use App\Repository\AlertesEmailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/alertes/email')]
final class AlertesEmailController extends AbstractController
{
    #[Route(name: 'app_alertes_email_index', methods: ['GET'])]
    public function index(AlertesEmailRepository $alertesEmailRepository): Response
    {
        return $this->render('alertes_email/index.html.twig', [
            'alertes_emails' => $alertesEmailRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_alertes_email_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $alertesEmail = new AlertesEmail();
        $form = $this->createForm(AlertesEmailType::class, $alertesEmail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($alertesEmail);
            $entityManager->flush();

            return $this->redirectToRoute('app_alertes_email_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alertes_email/new.html.twig', [
            'alertes_email' => $alertesEmail,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_alertes_email_show', methods: ['GET'])]
    public function show(AlertesEmail $alertesEmail): Response
    {
        return $this->render('alertes_email/show.html.twig', [
            'alertes_email' => $alertesEmail,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_alertes_email_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AlertesEmail $alertesEmail, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AlertesEmailType::class, $alertesEmail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_alertes_email_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('alertes_email/edit.html.twig', [
            'alertes_email' => $alertesEmail,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_alertes_email_delete', methods: ['POST'])]
    public function delete(Request $request, AlertesEmail $alertesEmail, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$alertesEmail->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($alertesEmail);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_alertes_email_index', [], Response::HTTP_SEE_OTHER);
    }
}
