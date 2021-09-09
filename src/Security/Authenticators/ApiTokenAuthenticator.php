<?php

namespace App\Security\Authenticators;

use App\Entity\ApiToken;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Contracts\Translation\TranslatorInterface;


class ApiTokenAuthenticator extends AbstractAuthenticator
{
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        return $request->headers->has('X-AUTH-TOKEN');
    }

    private function getHashedTokenVariants(string $token)
    {
        $hashedTokens = [];

        foreach(['sha1',] as $hashAlgorithm) {
            $hashedTokens[$hashAlgorithm] = match ($hashAlgorithm) {
                'sha1' => sha1($token),
            };
        }

        return $hashedTokens;
    }

    private function findUserByToken(string $token)
    {
        foreach (array_values($this->getHashedTokenVariants($token)) as $hashedToken) {
            /** @var User $user */
            $user = $this->entityManager->getRepository(User::class)->findOneByApiToken($hashedToken);

            if($user) {
                return $user;
            }
        }

        return null;
    }

    private function findApiTokenByToken(string $token)
    {
        foreach (array_values($this->getHashedTokenVariants($token)) as $hashedToken) {
            /** @var ApiToken $apiToken */
            $apiToken = $this->entityManager->getRepository(
                ApiToken::class)->findOneBy(['token' => $hashedToken]);

            if($apiToken) {
                return $apiToken;
            }
        }

        return null;
    }

    public function authenticate(Request $request): PassportInterface
    {
        $token = $request->headers->get('X-AUTH-TOKEN');
        if (null === $token) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        $user = $this->findUserByToken($token);

        if (!$user || !$user->isVerified()) {
            // fail authentication with a custom error
            throw new AuthenticationCredentialsNotFoundException();
        }

        return new SelfValidatingPassport(new UserBadge($user->getEmail()));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $requestToken = $request->headers->get('X-AUTH_TOKEN');

        if($requestToken) {
            $apiToken = $this->findApiTokenByToken($requestToken);
            $apiToken->setLastUsedAt(new DateTime());
            $this->entityManager->persist($apiToken);
            $this->entityManager->flush();
        }

        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([
            'success' => false,
            'message' => $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ], Response::HTTP_UNAUTHORIZED);
    }
}
