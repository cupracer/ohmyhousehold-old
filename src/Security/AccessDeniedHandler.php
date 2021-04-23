<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    private SessionInterface $session;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(SessionInterface $session, UrlGeneratorInterface $urlGenerator)
    {
        $this->session = $session;
        $this->urlGenerator = $urlGenerator;
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        if(str_starts_with($request->attributes->get('_route'), 'api_')) {
            return new JsonResponse([
                'success' => false,
                "message" => "Permission denied."
            ]);
        }

        // add a custom flash message and redirect to the login page
        $this->session->getFlashBag()->add('info', 'You don\'t have the permission to access this page.');


        return new RedirectResponse($this->urlGenerator->generate('homepage'));
    }
}