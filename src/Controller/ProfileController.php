<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/profile')]
final class ProfileController extends AbstractController
{
    #[Route('', name: 'app_profile_index', methods: ['GET'])]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        /** @var Utilisateur $user */
        $user = $this->getUser();
        
        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }
    
    #[Route('/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher,
        SluggerInterface $slugger
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        /** @var Utilisateur $user */
        $user = $this->getUser();
        $errors = [];
        $success = null;
        
        if ($request->isMethod('POST')) {
            // Récupération des données
            $firstname = trim($request->request->get('firstname', ''));
            $lastname = trim($request->request->get('lastname', ''));
            $email = trim($request->request->get('email', ''));
            $phone = trim($request->request->get('phone', ''));
            $birthdate = $request->request->get('birthdate');
            $notificationsEmail = $request->request->get('notifications_email') ? true : false;
            
            $currentPassword = $request->request->get('current_password', '');
            $newPassword = $request->request->get('new_password', '');
            $confirmPassword = $request->request->get('confirm_password', '');
            
            // Mise à jour des informations
            $user->setFirstname($firstname);
            $user->setLastname($lastname);
            $user->setEmail($email);
            $user->setPhone($phone);
            if ($birthdate) {
                $user->setBirthdate(new \DateTime($birthdate));
            }
            $user->setNotificationsEmail($notificationsEmail);
            
            // Gestion de l'avatar
            /** @var UploadedFile $avatarFile */
            $avatarFile = $request->files->get('avatar');
            if ($avatarFile) {
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $avatarFile->guessExtension();
                
                try {
                    $avatarFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/avatars',
                        $newFilename
                    );
                    
                    // Supprimer l'ancien avatar si existe
                    if ($user->getAvatar() && file_exists($this->getParameter('kernel.project_dir') . '/public/uploads/avatars/' . $user->getAvatar())) {
                        unlink($this->getParameter('kernel.project_dir') . '/public/uploads/avatars/' . $user->getAvatar());
                    }
                    
                    $user->setAvatar($newFilename);
                } catch (\Exception $e) {
                    $errors[] = 'Erreur lors du téléchargement de l\'avatar.';
                }
            }
            
            // 🔑 CHANGEMENT DE MOT DE PASSE AVEC PASSWORD_HASHER
            if ($newPassword) {
                if (!$currentPassword) {
                    $errors[] = 'Veuillez entrer votre mot de passe actuel.';
                } else {
                    // Vérification avec le PasswordHasher
                    $isValid = $passwordHasher->isPasswordValid($user, $currentPassword);
                    
                    if (!$isValid) {
                        $errors[] = 'Mot de passe actuel incorrect.';
                    } elseif (strlen($newPassword) < 4) {
                        $errors[] = 'Le nouveau mot de passe doit contenir au moins 4 caractères.';
                    } elseif ($newPassword !== $confirmPassword) {
                        $errors[] = 'Les nouveaux mots de passe ne correspondent pas.';
                    } else {
                        // Hasher avec le PasswordHasher
                        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                        $user->setPasswordHash($hashedPassword);
                    }
                }
            }
            
            // Validation des emails et téléphone
            if (empty($errors)) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'L\'adresse email n\'est pas valide.';
                }
                
                if ($phone && !preg_match('/^[0-9+\-\s]{8,20}$/', $phone)) {
                    $errors[] = 'Le numéro de téléphone n\'est pas valide.';
                }
            }
            
            // Sauvegarde
            if (empty($errors)) {
                try {
                    $em->flush();
                    $success = 'Vos informations ont été mises à jour avec succès!';
                    
                    // Rafraîchir l'utilisateur
                    $em->refresh($user);
                } catch (\Exception $e) {
                    $errors[] = 'Une erreur est survenue lors de la sauvegarde.';
                }
            }
        }
        
        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'errors' => $errors,
            'success' => $success,
        ]);
    }
    
    #[Route('/delete-avatar', name: 'app_profile_delete_avatar', methods: ['POST'])]
    public function deleteAvatar(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        if ($this->isCsrfTokenValid('delete_avatar', $request->request->get('_token'))) {
            /** @var Utilisateur $user */
            $user = $this->getUser();
            
            if ($user->getAvatar()) {
                $avatarPath = $this->getParameter('kernel.project_dir') . '/public/uploads/avatars/' . $user->getAvatar();
                if (file_exists($avatarPath)) {
                    unlink($avatarPath);
                }
                $user->setAvatar(null);
                $em->flush();
            }
        }
        
        return $this->redirectToRoute('app_profile_edit');
    }
}