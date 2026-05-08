<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/utilisateur')]
final class UtilisateurController extends AbstractController
{
    // ── LIST ──────────────────────────────────────────────
    #[Route('', name: 'app_utilisateur_index', methods: ['GET'])]
    public function index(
        Request $request,
        UtilisateurRepository $repo
    ): Response {
        $search = $request->query->get('search', '');
        $role   = $request->query->get('role', '');
        $status = $request->query->get('status', '');

        $all   = $repo->findAll();
        $users = array_filter($all, function ($u) use ($search, $role, $status) {
            $matchSearch = !$search ||
                str_contains(strtolower($u->getUsername()), strtolower($search)) ||
                str_contains(strtolower($u->getEmail()), strtolower($search));
            $matchRole   = !$role   || $u->getRole() === $role;
            $matchStatus = $status === '' || (
                ($status === '1' && $u->isIsActive()) ||
                ($status === '0' && !$u->isIsActive())
            );
            return $matchSearch && $matchRole && $matchStatus;
        });

        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => array_values($users),
            'search'       => $search,
            'role'         => $role,
            'status'       => $status,
            'total'        => count($all),
        ]);
    }

    // ── ADD ───────────────────────────────────────────────
    #[Route('/new', name: 'app_utilisateur_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): Response {
        $errors = [];

        if ($request->isMethod('POST')) {
            $username = trim($request->request->get('username', ''));
            $email    = trim($request->request->get('email', ''));
            $password = $request->request->get('password', '');
            $role     = $request->request->get('role', 'enfant');
            $active   = $request->request->get('isActive') ? true : false;

            $user = new Utilisateur();
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setPasswordHash(password_hash($password, PASSWORD_BCRYPT));
            $user->setRole($role);
            $user->setIsActive($active);

            $violations = $validator->validate($user);
            if (count($violations) > 0) {
                foreach ($violations as $v) {
                    $errors[] = $v->getMessage();
                }
            } else {
                $em->persist($user);
                $em->flush();
                return $this->redirectToRoute('app_utilisateur_index');
            }
        }

        return $this->render('utilisateur/new.html.twig', [
            'errors' => $errors,
        ]);
    }

    // ── EDIT ──────────────────────────────────────────────
    #[Route('/{id}/edit', name: 'app_utilisateur_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Utilisateur $utilisateur,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): Response {
        $errors = [];

        if ($request->isMethod('POST')) {
            $username = trim($request->request->get('username', ''));
            $email    = trim($request->request->get('email', ''));
            $password = $request->request->get('password', '');
            $role     = $request->request->get('role', 'enfant');
            $active   = $request->request->get('isActive') ? true : false;

            $utilisateur->setUsername($username);
            $utilisateur->setEmail($email);
            $utilisateur->setRole($role);
            $utilisateur->setIsActive($active);
            if ($password) {
                $utilisateur->setPasswordHash(password_hash($password, PASSWORD_BCRYPT));
            }

            $violations = $validator->validate($utilisateur);
            if (count($violations) > 0) {
                foreach ($violations as $v) {
                    $errors[] = $v->getMessage();
                }
            } else {
                $em->flush();
                return $this->redirectToRoute('app_utilisateur_index');
            }
        }

        return $this->render('utilisateur/edit.html.twig', [
            'utilisateur' => $utilisateur,
            'errors'      => $errors,
        ]);
    }

    // ── DELETE ────────────────────────────────────────────
    #[Route('/{id}/delete', name: 'app_utilisateur_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Utilisateur $utilisateur,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $utilisateur->getId(),
            $request->request->get('_token'))) {
            $em->remove($utilisateur);
            $em->flush();
        }

        return $this->redirectToRoute('app_utilisateur_index');
    }

    // ── SHOW ──────────────────────────────────────────────
    #[Route('/{id}', name: 'app_utilisateur_show', methods: ['GET'])]
    public function show(Utilisateur $utilisateur): Response
    {
        return $this->render('utilisateur/show.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }
}