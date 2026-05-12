<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminSecurityController extends AbstractController
{
  
    #[Route("/admin/login-form", name:"app_admin_login_form_ajax", methods:["GET"])]
    public function loginFormAjax(): Response
    {
        if ($this->getUser()) {
            $roles = $this->getUser()->getRoles();
            if (in_array('ROLE_ADMIN', $roles) || in_array('ROLE_TEACHER', $roles)) {
                return $this->json(['redirect' => $this->generateUrl('app_admin_dashboard')]);
            }
            return $this->json(['redirect' => $this->generateUrl('app_dashboard')]);
        }

        return $this->render('security/_login_admin_form.html.twig', [
            'error' => null,
            'savedUsername' => '',
            'rememberChecked' => false,
            'captchaUrl' => $this->generateUrl('app_captcha_image'),
        ]);
    }

 
    #[Route("/admin/login", name:"app_admin_login", methods:["POST"])]
    public function login(Request $request, UtilisateurRepository $userRepo, SessionInterface $session): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->json(['success' => false, 'error' => 'Requête AJAX requise'], 400);
        }

        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $captcha = $request->request->get('captcha');

        // Vérification CAPTCHA
        $expectedCaptcha = $session->get('captcha_code');
        if (empty($expectedCaptcha) || strtoupper($captcha) !== $expectedCaptcha) {
            return $this->json(['success' => false, 'error' => 'Code de sécurité incorrect']);
        }

        $user = $userRepo->findOneBy(['username' => $username]);
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Identifiant ou mot de passe invalide']);
        }

        // Vérification du mot de passe
        $passwordValid = $this->container->get('security.password_hasher')->isPasswordValid($user, $password);
        if (!$passwordValid) {
            return $this->json(['success' => false, 'error' => 'Identifiant ou mot de passe invalide']);
        }

        // Vérification du rôle (admin ou enseignant)
        $roles = $user->getRoles();
        if (!in_array('ROLE_ADMIN', $roles) && !in_array('ROLE_TEACHER', $roles)) {
            return $this->json(['success' => false, 'error' => 'Accès non autorisé : vous n\'êtes pas enseignant.']);
        }

        // Authentification manuelle
        $token = new UsernamePasswordToken($user, 'main', $roles);
        $this->container->get('security.token_storage')->setToken($token);
        $session->set('_security_main', serialize($token));

        // Redirection vers le dashboard admin
        return $this->json(['success' => true, 'redirect' => $this->generateUrl('app_lecon_index')]);
    }

  
    #[Route("/admin/login-page", name:"app_admin_login_page", methods:["GET"])]
    public function loginPage(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/admin_login.html.twig', [
            'error' => $error,
            'savedUsername' => $lastUsername,
        ]);
    }
}