<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class ReferrerService
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getReferrerUrl(Request $request): ?string
    {
        // Following lines help us to find and return to the referrer URL.
        // Source: https://www.strangebuzz.com/en/snippets/get-the-routing-information-of-the-referer

        $referrer = $request->headers->get('referer');

        if (!$referrer || !\is_string($referrer)) {
            // Referer is invalid or empty.
            return null;
        }

        $referrerPathInfo = Request::create($referrer)->getPathInfo();

        // Remove the scriptname if using a dev controller like app_dev.php (Symfony 3.x only)
        $referrerPathInfo = str_replace($request->getScriptName(), '', $referrerPathInfo);

        // try to match the path with the application routing
        $routeInfos = $this->router->match($referrerPathInfo);

        // get the Symfony route name
        $referrerRoute = $routeInfos['_route'] ?? '';
        
        if (!$referrerRoute) {
            // No route found (external URL for example)
            return null;
        }
        return $referrerRoute;
//        // get the parameters, remove useless ones
//        unset($routeInfos['_route'], $routeInfos['_controller']);
//        // and add a parameter to test:
//        //$routeInfos['foo'] = 'bar';
//
//        // Ok, now we can generate a new URL for this referer with new parameters
//        return $this->router->generate($referrerRoute, $routeInfos);
    }
}