<?php
namespace App\Controller;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository; // ← point-virgule ajouté
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\ByteString;


class SecurityController extends AbstractController
{
    // ========================
    // LOGIN (standard + AJAX)
    // ========================

    /**
     * @Route("/login", name="app_login", methods={"GET", "POST"})
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils, UtilisateurRepository $UtilisateurRepository, SessionInterface $session): Response
    {
        // Si déjà connecté, rediriger
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home'); // à adapter
        }

        // Gestion AJAX (POST via fetch)
        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $username = $request->request->get('username');
            $password = $request->request->get('password');
            $captcha  = $request->request->get('captcha');

            // Vérification CAPTCHA
            $expectedCaptcha = $session->get('captcha_code');
            if (empty($expectedCaptcha) || strtoupper($captcha) !== $expectedCaptcha) {
                return $this->json(['success' => false, 'error' => 'Code de sécurité incorrect']);
            }

            // Recherche utilisateur
            $user = $UtilisateurRepository->findOneBy(['username' => $username]);
            if (!$user) {
                return $this->json(['success' => false, 'error' => 'Identifiant ou mot de passe invalide']);
            }

            // Vérification mot de passe (selon votre encodeur)
            $passwordValid = $this->container->get('security.password_hasher')->isPasswordValid($user, $password);
            if (!$passwordValid) {
                return $this->json(['success' => false, 'error' => 'Identifiant ou mot de passe invalide']);
            }

            // Authentification manuelle
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $this->container->get('security.token_storage')->setToken($token);
            $session->set('_security_main', serialize($token));

            return $this->json(['success' => true, 'redirect' => $this->generateUrl('app_dashboard')]);
        }

        // Mode GET (affichage normal, non AJAX)
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // Si c'est une requête AJAX pour le formulaire vide, on renvoie le template partiel
        if ($request->isXmlHttpRequest() && $request->isMethod('GET')) {
            return $this->render('security/_login_form.html.twig', [
                'error' => $error,
                'savedUsername' => $lastUsername,
                'rememberChecked' => false,
                'lockoutSeconds' => 0, // optionnel
            ]);
        }

        // Sinon page de login classique (pour les navigateurs sans JS)
        return $this->render('security/login.html.twig', [
            'error' => $error,
            'savedUsername' => $lastUsername,
        ]);
    }

    /**
     * @Route("/login-form-ajax", name="app_login_form_ajax", methods={"GET"})
     */
    public function loginFormAjax(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->json(['redirect' => $this->generateUrl('app_dashboard')]);
        }
        return $this->render('security/_login_form.html.twig', [
            'error' => null,
            'savedUsername' => '',
            'rememberChecked' => false,
            'lockoutSeconds' => 0,
        ]);
    }

    /**
     * @Route("/logout", name="app_logout", methods={"GET"})
     */
    public function logout(): void
    {
        // Cette méthode est interceptée par Symfony
        throw new \LogicException('This method should be intercepted by the logout key.');
    }

    // ========================
    // MOT DE PASSE OUBLIÉ (ENVOI CODE)
    // ========================

    /**
     * @Route("/mot-de-passe-oublie", name="app_forgot_password", methods={"GET", "POST"})
     */
    public function forgotPassword(Request $request, UtilisateurRepository $UtilisateurRepository, MailerInterface $mailer, SessionInterface $session): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $UtilisateurRepository->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('error', 'Aucun compte trouvé avec cet email.');
                return $this->redirectToRoute('app_forgot_password');
            }

            // Génération d'un code à 6 chiffres
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $session->set('reset_code', $code);
            $session->set('reset_email', $email);
            $session->set('reset_code_expires', time() + 300); // 5 minutes

            // Envoi de l'email
            $emailMessage = (new Email())
                ->from('no-reply@focuskid.com')
                ->to($email)
                ->subject('FocusKid - Code de réinitialisation')
                ->html("<p>Bonjour,</p><p>Voici votre code magique pour réinitialiser votre mot de passe : <strong>$code</strong></p><p>Ce code expire dans 5 minutes.</p>");

            try {
                $mailer->send($emailMessage);
                $this->addFlash('success', 'Un code de réinitialisation a été envoyé à votre email.');
                return $this->redirectToRoute('app_verify_code', ['email' => $email]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'envoi de l\'email. Réessayez plus tard.');
                return $this->redirectToRoute('app_forgot_password');
            }
        }

        // Affichage du formulaire (GET)
        if ($request->isXmlHttpRequest()) {
            return $this->render('security/_forgot_password_form.html.twig');
        }
        return $this->render('security/forgot_password.html.twig');
    }
 

    /**
     * @Route("/verify-code", name="app_verify_code", methods={"GET", "POST"})
     */
    public function verifyCode(Request $request, SessionInterface $session): Response
    {
        $email = $request->query->get('email') ?: $request->request->get('email');
        $expectedCode = $session->get('reset_code');
        $expires = $session->get('reset_code_expires', 0);

        if ($request->isMethod('POST')) {
            $enteredCode = $request->request->get('code');
            if ($enteredCode == $expectedCode && time() < $expires) {
                // Code valide
                $session->set('code_validated', true);
                return $this->redirectToRoute('app_reset_password', ['email' => $email, 'code' => $enteredCode]);
            } else {
                $this->addFlash('error', 'Code invalide ou expiré.');
            }
        }

        $remainingTime = max(0, $expires - time());
        if ($request->isXmlHttpRequest()) {
            return $this->render('security/_verify_code_form.html.twig', [
                'email' => $email,
                'remainingTime' => $remainingTime,
            ]);
        }
        return $this->render('security/verify_code.html.twig', [
            'email' => $email,
            'remainingTime' => $remainingTime,
        ]);
    }

    /**
     * @Route("/resend-code", name="app_resend_code", methods={"POST"})
     */
    public function resendCode(Request $request, UtilisateurRepository $UtilisateurRepository, MailerInterface $mailer, SessionInterface $session): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (!$email) {
            return $this->json(['success' => false, 'error' => 'Email requis'], 400);
        }

        $user = $UtilisateurRepository->findOneBy(['email' => $email]);
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Utilisateur introuvable'], 404);
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $session->set('reset_code', $code);
        $session->set('reset_email', $email);
        $session->set('reset_code_expires', time() + 300);

        $emailMessage = (new Email())
            ->from('no-reply@focuskid.com')
            ->to($email)
            ->subject('FocusKid - Nouveau code')
            ->html("<p>Votre nouveau code magique : <strong>$code</strong></p><p>Valable 5 minutes.</p>");

        try {
            $mailer->send($emailMessage);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => 'Erreur d\'envoi'], 500);
        }
    }

    /**
     * @Route("/reset-password", name="app_reset_password", methods={"GET", "POST"})
     */
    public function resetPassword(Request $request, UtilisateurRepository $UtilisateurRepository, UserPasswordHasherInterface $passwordHasher, SessionInterface $session): Response
    {
        if (!$session->get('code_validated')) {
            $this->addFlash('error', 'Accès non autorisé. Veuillez refaire la procédure.');
            return $this->redirectToRoute('app_forgot_password');
        }

        $email = $request->query->get('email') ?: $request->request->get('email');
        $code = $request->query->get('code') ?: $request->request->get('code');

        $user = $UtilisateurRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_forgot_password');
        }

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password');
            $confirm = $request->request->get('confirm');

            if ($password !== $confirm) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->redirectToRoute('app_reset_password', ['email' => $email, 'code' => $code]);
            }

            if (strlen($password) < 4) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 4 caractères.');
                return $this->redirectToRoute('app_reset_password', ['email' => $email, 'code' => $code]);
            }

            $hashedPassword = $passwordHasher->hashPassword($user, $password);
            $user->setPasswordHash($hashedPassword);
            $UtilisateurRepository->save($user, true);

            $session->remove('code_validated');
            $session->remove('reset_code');
            $session->remove('reset_email');

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès !');
            return $this->redirectToRoute('app_login');
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('security/_reset_password_form.html.twig', [
                'email' => $email,
                'code' => $code,
            ]);
        }
        return $this->render('security/reset_password.html.twig', [
            'email' => $email,
            'code' => $code,
        ]);
    }

    // ========================
    // CAPTCHA
    // ========================

    /**
     * @Route("/captcha-image", name="app_captcha_image", methods={"GET"})
     */
    public function captchaImage(SessionInterface $session): Response
    {
        $code = strtoupper(substr(md5(uniqid()), 0, 5));
        $session->set('captcha_code', $code);

        $image = imagecreate(150, 45);
        $bg = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 255, 107, 53);
        $lineColor = imagecolorallocate($image, 200, 200, 200);

        for ($i = 0; $i < 5; $i++) {
            imageline($image, rand(0, 150), rand(0, 45), rand(0, 150), rand(0, 45), $lineColor);
        }

        imagettftext($image, 20, rand(-5, 5), 15, 32, $textColor, __DIR__.'/../../public/fonts/arial.ttf', $code);
        // Si la police n'existe pas, utilisez imagestring :
        // imagestring($image, 5, 20, 12, $code, $textColor);

        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        return new Response($imageData, 200, ['Content-Type' => 'image/png']);
    }
  
}