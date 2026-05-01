<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Controller\CaptchaController;

class LoginController extends AbstractController
{
    // ── LOGIN ─────────────────────────────────────────────
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request, AuthenticationUtils $authUtils): Response
    {
        // Redirect already-authenticated users
        if ($this->getUser()) {
            return $this->redirectBasedOnRole();
        }

        $error          = $authUtils->getLastAuthenticationError();
        $lastUsername   = $authUtils->getLastUsername();
        $savedUsername  = $request->cookies->get('remember_username', $lastUsername);

        // Lockout countdown for the template
        $session        = $request->getSession();
        $blockedAt      = $session->get('login_blocked_at', null);
        $lockoutSeconds = 0;
        if ($blockedAt !== null) {
            $elapsed = time() - $blockedAt;
            $lockoutSeconds = $elapsed < 60 ? (60 - $elapsed) : 0;
        }

        return $this->render('login/index.html.twig', [
            'error'          => $error,
            'savedUsername'  => $savedUsername,
            'rememberChecked'=> $savedUsername !== '',
            'lockoutSeconds' => $lockoutSeconds,
        ]);
    }

    // ── LOGOUT — handled by Symfony Security (see security.yaml) ──
    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        throw new \LogicException('This should be intercepted by the firewall.');
    }

    // ── FORGOT PASSWORD — Step 1: Enter email ─────────────
    #[Route('/forgot-password', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(
        Request $request,
        UtilisateurRepository $repo,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        $error   = null;
        $success = null;

        if ($request->isMethod('POST')) {
            $email = trim($request->request->get('email', ''));
            $user  = $repo->findOneBy(['email' => $email]);

            if (!$user) {
                $error = 'Aucun compte trouvé avec cet email.';
            } else {
                $code   = strval(random_int(100000, 999999));
                $expiry = new \DateTime('+10 minutes');

                $user->setResetToken($code);
                $user->setResetTokenExpiry($expiry);
                $em->flush();

                $emailMessage = (new Email())
                    ->from(new Address('soussiaaziz10@gmail.com', 'FocusKid'))
                    ->to($email)
                    ->subject('FocusKid — Code de réinitialisation : ' . $code)
                    ->html("
                        <div style='font-family:Arial,sans-serif;max-width:500px;margin:0 auto;padding:20px'>
                            <div style='background:linear-gradient(135deg,#667eea,#764ba2);
                                        border-radius:16px;padding:32px;text-align:center;margin-bottom:24px'>
                                <h1 style='color:white;margin:0;font-size:28px'>🔐 FocusKid</h1>
                                <p style='color:rgba(255,255,255,0.85);margin:8px 0 0'>
                                    Réinitialisation de mot de passe
                                </p>
                            </div>
                            <p style='color:#4a5568;font-size:16px'>
                                Bonjour <strong>" . htmlspecialchars($user->getUsername()) . "</strong>,
                            </p>
                            <div style='background:#f7fafc;border:2px dashed #667eea;border-radius:12px;
                                        padding:24px;text-align:center;margin:24px 0'>
                                <span style='font-size:42px;font-weight:700;color:#667eea;letter-spacing:12px'>
                                    " . $code . "
                                </span>
                            </div>
                            <p style='color:#718096;font-size:13px'>
                                ⏱ Ce code expire dans <strong>10 minutes</strong>.
                            </p>
                        </div>
                    ")
                    ->replyTo(new Address('soussiaaziz10@gmail.com', 'FocusKid'))
                    ->priority(Email::PRIORITY_HIGH);

                try {
                    $mailer->send($emailMessage);
                } catch (\Exception $e) {
                    $error = 'Erreur lors de l\'envoi de l\'email : ' . $e->getMessage();
                    return $this->render('login/forgot_password.html.twig', [
                        'error' => $error, 'success' => null,
                    ]);
                }

                $request->getSession()->set('reset_email', $email);
                return $this->redirectToRoute('app_verify_code');
            }
        }

        return $this->render('login/forgot_password.html.twig', [
            'error' => $error, 'success' => $success,
        ]);
    }

    // ── FORGOT PASSWORD — Step 2: Verify code ─────────────
    #[Route('/verify-code', name: 'app_verify_code', methods: ['GET', 'POST'])]
    public function verifyCode(
        Request $request,
        UtilisateurRepository $repo,
        EntityManagerInterface $em
    ): Response {
        $email = $request->getSession()->get('reset_email');
        if (!$email) return $this->redirectToRoute('app_forgot_password');

        $error = null;

        if ($request->isMethod('POST')) {
            $code = trim($request->request->get('code', ''));
            $user = $repo->findOneBy(['email' => $email]);

            if (!$user) return $this->redirectToRoute('app_forgot_password');

            if ($user->getResetToken() !== $code) {
                $error = 'Code incorrect. Veuillez réessayer.';
            } elseif ($user->getResetTokenExpiry() < new \DateTime()) {
                $error = 'Ce code a expiré. Veuillez recommencer.';
            } else {
                $request->getSession()->set('reset_verified', true);
                return $this->redirectToRoute('app_reset_password');
            }
        }

        return $this->render('login/verify_code.html.twig', [
            'error' => $error, 'email' => $email,
        ]);
    }

    // ── FORGOT PASSWORD — Step 3: New password ────────────
    #[Route('/reset-password', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(
        Request $request,
        UtilisateurRepository $repo,
        EntityManagerInterface $em
    ): Response {
        $email    = $request->getSession()->get('reset_email');
        $verified = $request->getSession()->get('reset_verified');
        if (!$email || !$verified) return $this->redirectToRoute('app_forgot_password');

        $error = null;

        if ($request->isMethod('POST')) {
            $password = $request->request->get('password', '');
            $confirm  = $request->request->get('confirm', '');

            if (strlen($password) < 4) {
                $error = 'Le mot de passe doit contenir au moins 4 caractères.';
            } elseif ($password !== $confirm) {
                $error = 'Les mots de passe ne correspondent pas.';
            } else {
                $user = $repo->findOneBy(['email' => $email]);
                $user->setPasswordHash(password_hash($password, PASSWORD_BCRYPT));
                $user->setResetToken(null);
                $user->setResetTokenExpiry(null);
                $em->flush();
                $request->getSession()->remove('reset_email');
                $request->getSession()->remove('reset_verified');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('login/reset_password.html.twig', ['error' => $error]);
    }

    // ── CLEAR COOKIES ─────────────────────────────────────
    #[Route('/clear-cookies', name: 'app_clear_cookies')]
    public function clearCookies(): Response
    {
        $response = $this->redirectToRoute('app_login');
        $response->headers->clearCookie('remember_username', '/');
        $response->headers->clearCookie('remember_password', '/');
        return $response;
    }

    // ── HELPERS ───────────────────────────────────────────
    private function redirectBasedOnRole(): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_utilisateur_index');
        }
        return $this->redirectToRoute('app_index');
    }
}