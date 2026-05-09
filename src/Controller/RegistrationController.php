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
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    #[IsGranted('PUBLIC_ACCESS')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_index');
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

                $role = $user->getRole();
                $message = $role == 0
                    ? 'Compte parent créé avec succès !'
                    : 'Compte enfant créé avec succès !';

                $this->addFlash('success', $message);

                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue : ' . $e->getMessage());
                error_log('Registration error: ' . $e->getMessage());
            }
        } elseif ($form->isSubmitted()) {
            foreach ($form->getErrors(true, true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
