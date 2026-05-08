<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    #[IsGranted('PUBLIC_ACCESS')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        // Si déjà connecté, rediriger vers l'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('app_index');
        }

        $user = new Utilisateur();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hasher le mot de passe
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPasswordHash($hashedPassword);
            
            // Définir le rôle par défaut (0 = parent, 1 = admin, 2 = enfant)
            $user->setRole(0); // Rôle parent par défaut
            
            // Définir la date de création
            $user->setCreatedAt(new \DateTime());
            
            // Activer le compte par défaut
            $user->setIsActive(true);
            
            // Sauvegarder l'utilisateur
            $entityManager->persist($user);
            $entityManager->flush();

            // Ajouter un message flash
            $this->addFlash('success', 'Compte créé avec succès ! Tu peux maintenant te connecter.');

            // Rediriger vers la page de connexion
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}