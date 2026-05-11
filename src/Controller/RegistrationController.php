<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['POST'])] // ← seulement POST pour l’AJAX
    #[IsGranted('PUBLIC_ACCESS')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        if ($this->getUser()) {
            return $this->json(['success' => false, 'error' => 'Déjà connecté']);
        }

        $user = new Utilisateur();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $plainPassword = $form->get('plainPassword')->getData();
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPasswordHash($hashedPassword);
                $user->setCreatedAt(new \DateTime());
                $user->setIsActive(true);

                $entityManager->persist($user);
                $entityManager->flush();

                return $this->json(['success' => true, 'redirect' => $this->generateUrl('app_login')]);
            } catch (\Exception $e) {
                return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
            }
        }

        // Collecte des erreurs du formulaire
        $errors = [];
        foreach ($form->getErrors(true, true) as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        // Erreurs sur les champs enfants
        foreach ($form->all() as $child) {
            foreach ($child->getErrors(true, true) as $error) {
                $errors[] = $error->getMessage();
            }
        }

        return $this->json(['success' => false, 'error' => implode(' ', $errors)], 400);
    }
}