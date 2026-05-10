<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UtilisateurRepository $repo
    ): Response {
        // Already logged in → redirect away
        if ($this->getUser()) {
            return $this->redirectToRoute('app_index');
        }

        $errors  = [];
        $old     = []; // repopulate form on error

        if ($request->isMethod('POST')) {
            $username        = trim($request->request->get('username', ''));
            $email           = trim($request->request->get('email', ''));
            $password        = $request->request->get('password', '');
            $confirmPassword = $request->request->get('confirm_password', '');
            $role            = $request->request->get('role', 'ROLE_PARENT');
            $isActive        = (bool) $request->request->get('is_active', false);

            $old = compact('username', 'email', 'role', 'isActive');

            // ── Validation ────────────────────────────────────────────
            if (empty($username)) {
                $errors['username'] = 'Le nom d\'utilisateur est obligatoire.';
            } elseif (strlen($username) < 3) {
                $errors['username'] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
            } elseif ($repo->findOneBy(['username' => $username])) {
                $errors['username'] = 'Ce nom d\'utilisateur est déjà utilisé.';
            }

            if (empty($email)) {
                $errors['email'] = 'L\'email est obligatoire.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'L\'adresse email n\'est pas valide.';
            } elseif ($repo->findOneBy(['email' => $email])) {
                $errors['email'] = 'Cet email est déjà utilisé.';
            }

            if (strlen($password) < 4) {
                $errors['password'] = 'Le mot de passe doit contenir au moins 4 caractères.';
            } elseif ($password !== $confirmPassword) {
                $errors['confirm_password'] = 'Les mots de passe ne correspondent pas.';
            }

            $allowedRoles = ['ROLE_ADMIN', 'ROLE_ENFANT', 'ROLE_PARENT'];
            if (!in_array($role, $allowedRoles)) {
                $errors['role'] = 'Rôle invalide.';
            }

            // ── Save if no errors ─────────────────────────────────────
            if (empty($errors)) {
                $user = new Utilisateur();
                $user->setUsername($username);
                $user->setEmail($email);
                $user->setPasswordHash(password_hash($password, PASSWORD_BCRYPT));
                $user->setRole($role);
                $user->setIsActive($isActive);

                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('register/index.html.twig', [
            'errors' => $errors,
            'old'    => $old,
        ]);
    }
}