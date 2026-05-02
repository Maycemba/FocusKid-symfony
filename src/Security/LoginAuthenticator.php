<?php

namespace App\Security;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use App\Service\AnomalyDetector;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class LoginAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private RouterInterface $router,
        private UtilisateurRepository $userRepo,
        private AnomalyDetector $anomalyDetector
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'app_login'
            && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $username = trim($request->request->get('username', ''));
        $password = $request->request->get('password', '');
        $session  = $request->getSession();
        $ip       = $request->getClientIp() ?? '0.0.0.0';

        // ── CAPTCHA ────────────────────────────────────────────────
        $expected = strtoupper(trim((string) $session->get('captcha_code', '')));
        $given    = strtoupper(trim($request->request->get('captcha', '')));
        $session->remove('captcha_code');

        if ($expected === '' || $expected !== $given) {
            throw new CustomUserMessageAuthenticationException(
                'Code CAPTCHA incorrect. Veuillez réessayer.'
            );
        }

        // ── Rate limiting ───────────────────────────────────────────
        $blockedAt = $session->get('login_blocked_at', null);
        if ($blockedAt !== null) {
            $elapsed = time() - $blockedAt;
            if ($elapsed < 60) {
                throw new CustomUserMessageAuthenticationException(
                    sprintf('Trop de tentatives. Réessayez dans %d seconde(s).', 60 - $elapsed)
                );
            }
            $session->set('login_attempts', 0);
            $session->set('login_blocked_at', null);
        }

        $session->set(SecurityRequestAttributes::LAST_USERNAME, $username);

        // ── Load & verify user manually ─────────────────────────────
        $user = $this->userRepo->findOneBy(['username' => $username]);

        if (!$user) {
            // ── Anomaly detection on failure ────────────────────────
            $this->anomalyDetector->analyze($username, $ip);
            throw new CustomUserMessageAuthenticationException(
                'Nom d\'utilisateur introuvable.'
            );
        }

        if (!$user->isIsActive()) {
            throw new CustomUserMessageAuthenticationException(
                'Ce compte est désactivé.'
            );
        }

        $hash  = $user->getPasswordHash();
        $valid = false;

        if (str_starts_with($hash, '$2y$') || str_starts_with($hash, '$2a$')) {
            $valid = password_verify($password, $hash);
        } else {
            $valid = ($password === $hash);
        }

        if (!$valid) {
            // ── Anomaly detection on failure ────────────────────────
            $this->anomalyDetector->analyze($username, $ip);
            throw new CustomUserMessageAuthenticationException(
                'Mot de passe incorrect.'
            );
        }

        return new SelfValidatingPassport(
            new UserBadge($username, fn() => $user)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $session = $request->getSession();
        $session->set('login_attempts', 0);
        $session->set('login_blocked_at', null);

        /** @var Utilisateur $user */
        $user = $token->getUser();

        $url = in_array('ROLE_ADMIN', $user->getRoles())
            ? $this->router->generate('app_utilisateur_index')
            : $this->router->generate('app_index');

        $response = new RedirectResponse($url);

        // ── Remember Me cookie ──────────────────────────────────────
        $rememberMe = $request->request->get('remember_me');
        if ($rememberMe) {
            $cookie = Cookie::create('remember_username')
                ->withValue($user->getUsername())
                ->withExpires(new \DateTime('+30 days'))
                ->withPath('/')
                ->withHttpOnly(true)
                ->withSameSite('lax');
            $response->headers->setCookie($cookie);
        } else {
            $response->headers->clearCookie('remember_username', '/');
        }

        return $response;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $session = $request->getSession();
        $msg     = $exception->getMessage();

        $isCaptchaOrLockout = str_contains($msg, 'CAPTCHA') || str_contains($msg, 'tentatives');

        if (!$isCaptchaOrLockout) {
            $attempts = $session->get('login_attempts', 0) + 1;
            $session->set('login_attempts', $attempts);

            if ($attempts >= 3) {
                $session->set('login_blocked_at', time());
                $msg = 'Trop de tentatives. Réessayez dans 60 seconde(s).';
            } else {
                $remaining = 3 - $attempts;
                $msg = sprintf('%s (%d tentative(s) restante(s))', $msg, $remaining);
            }
        }

        $session->set(
            SecurityRequestAttributes::AUTHENTICATION_ERROR,
            new CustomUserMessageAuthenticationException($msg)
        );

        return new RedirectResponse($this->router->generate('app_login'));
    }
}