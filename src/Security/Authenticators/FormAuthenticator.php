<?php

namespace App\Security\Authenticators;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class FormAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public const LOGIN_ROUTE = 'app_user_login';

    private EntityManagerInterface $entityManager;
    private Session $session;
    private UrlGeneratorInterface $router;

    public function __construct(EntityManagerInterface $entityManager, SessionInterface $session, UrlGeneratorInterface $router)
    {
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->router = $router;
    }

    // required by AuthenticationEntryPointInterface
    public function start(Request $request, AuthenticationException $authException = null)
    {
        if(str_starts_with($request->attributes->get('_route'), 'api_')) {
            return new JsonResponse(["message" => "Authentication required."]);
        }

        // add a custom flash message and redirect to the login page
        $this->session->getFlashBag()->add('info', 'You have to login in order to access this page.');

        return new RedirectResponse($this->router->generate('app_user_login'));
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
            'remember_me' => $request->request->get('_remember_me'),
        ];

//        /** @var User $user */
//        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['username']]);
//
//        if (!$user) {
//            // fail authentication with a custom error
//            throw new UsernameNotFoundException();
//        }

//        if (!$user->isVerified()) {
//            // fail authentication with a custom error
//            throw new CustomUserMessageAuthenticationException('Account is not verified yet.');
//        }

        // Instead of loading and checking the user manually,
        // we use the Passport and Badges features to do this for us.

        $badges = [
            new CsrfTokenBadge('authenticate', $credentials['csrf_token']),
        ];

        if($credentials['remember_me']) {
            $badges[] = new RememberMeBadge();
        }

        return new Passport(
            new UserBadge($credentials['username'], function ($userIdentifier) {
                return $this->entityManager->getRepository(
                    User::class)->findOneBy(['email' => $userIdentifier, 'isVerified' => true]
                );
            }),
            new PasswordCredentials($credentials['password']),
            $badges
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->router->generate('start'));
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

        return new RedirectResponse($this->router->generate('app_user_login'));
    }
}
