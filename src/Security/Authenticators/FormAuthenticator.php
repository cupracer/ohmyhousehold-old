<?php

namespace App\Security\Authenticators;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class FormAuthenticator extends AbstractAuthenticator
{
    public const LOGIN_ROUTE = 'app_login';

    private EntityManagerInterface $entityManager;
    private CsrfTokenManagerInterface $csrfTokenManager;
    private Session $session;

    public function __construct(EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager, SessionInterface $session)
    {
        $this->entityManager = $entityManager;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->session = $session;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function authenticate(Request $request): PassportInterface
    {
        $credentials = [
            'username' => strtolower($request->request->get('username')),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];

        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['username']]);

        if (!$user) {
            // fail authentication with a custom error
            throw new UsernameNotFoundException();
        }

        if (!$user->isVerified()) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Account is not verified yet.');
        }

        return new SelfValidatingPassport($user);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse('/');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $error = strtr($exception->getMessageKey(), $exception->getMessageData());
        $this->session->getFlashBag()->add('error', $error);

//        $data = [
//            // you may want to customize or obfuscate the message first
//            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
//
//            // or to translate this message
//            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
//        ];

        return new RedirectResponse('/login');
    }
}
